<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MonobankWebhookController extends Controller
{

    public function __invoke(Request $request)
    {

        $payload = $request->all();

        $order = Order::where('payment_invoice_id', $payload['invoiceId'] ?? null)->first();

        if (! $order) {
            Log::warning('Monobank webhook: unknown invoiceId', $payload);

            return response()->noContent(Response::HTTP_OK);
        }
        var_dump($payload);
        if(($payload['status'] ?? null) === "success") {
            $order->update([
                'status' => 'paid',
                'payment_reference' => $payload['invoiceId'],
            ]);
        } elseif (in_array($payload['status'] ?? null, ['failure', 'expired', 'reversed'], true)) {
            $order->update(['status' => 'payment_failed']);
        }

        return \response()->noContent(Response::HTTP_OK);
    }
}
