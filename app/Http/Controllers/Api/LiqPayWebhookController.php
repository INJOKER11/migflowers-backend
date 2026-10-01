<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\LiqPayService;
use App\Services\OrderNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class LiqPayWebhookController extends Controller
{
    public function __construct(
        private LiqPayService $liqpay,
        private OrderNotificationService $notification,
    ) {}

    public function __invoke(Request $request)
    {
        $payload = $this->liqpay->decodeCallback($request->input('data'), $request->input('signature'));

        if ($payload === null) {
            Log::warning('LiqPay webhook: invalid signature');

            return response()->noContent(Response::HTTP_BAD_REQUEST);
        }

        $order = Order::where('payment_invoice_id', $payload['order_id'] ?? null)->first();

        if (! $order) {
            Log::warning('LiqPay webhook: unknown order_id', ['order_id' => $payload['order_id'] ?? null]);

            return response()->noContent(Response::HTTP_OK);
        }

        $newStatus = $this->liqpay->mapStatus($payload['status'] ?? null);

        // LiqPay retries callbacks, so a repeat must not re-notify.
        if ($newStatus === null || $order->status === $newStatus) {
            return response()->noContent(Response::HTTP_OK);
        }

        if ($newStatus === 'paid' && ! $this->matchesOrder($payload, $order)) {
            Log::warning('LiqPay webhook: amount or currency mismatch', [
                'order_id' => $order->order_number,
                'amount' => $payload['amount'] ?? null,
                'currency' => $payload['currency'] ?? null,
            ]);

            return response()->noContent(Response::HTTP_OK);
        }

        $order->update([
            'status' => $newStatus,
            'payment_reference' => $newStatus === 'paid'
                ? (string) ($payload['payment_id'] ?? $order->order_number)
                : $order->payment_reference,
        ]);
        $this->notification->notifyUpdatedOrder($order->fresh(['items.product', 'items.size', 'items.color', 'district']));

        return response()->noContent(Response::HTTP_OK);
    }

    private function matchesOrder(array $payload, Order $order): bool
    {
        return ($payload['currency'] ?? null) === 'UAH'
            && abs((float) ($payload['amount'] ?? 0) - (float) $order->total_amount) < 0.01;
    }
}
