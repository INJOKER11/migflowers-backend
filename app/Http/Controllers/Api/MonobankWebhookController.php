<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MonobankWebhookController extends Controller
{
    public function __construct(
        private OrderNotificationService $notification,
    ) {}

    public function __invoke(Request $request)
    {

        $payload = $request->all();

        $order = Order::where('payment_invoice_id', $payload['invoiceId'] ?? null)->first();

        if (! $order) {
            Log::warning('Monobank webhook: unknown invoiceId', $payload);

            return response()->noContent(Response::HTTP_OK);
        }

        $newStatus = match ($payload['status'] ?? null) {
            'success' => 'paid',
            'failure', 'expired', 'reversed' => 'payment_failed',
            default => null,
        };

        if($newStatus === null) {
            return \response()->noContent(Response::HTTP_OK);
        }

        $order->update([
            'status' => $newStatus,
            'payment_reference' => $newStatus === 'paid' ? $payload['invoiceId'] : $order->payment_reference,
        ]);
        $this->notification->notifyUpdatedOrder($order->fresh());

        return \response()->noContent(Response::HTTP_OK);
    }
}
