<?php

namespace App\Services;

use App\Models\Order;
use http\Exception\RuntimeException;
use Illuminate\Support\Facades\Http;

class MonobankInvoiceService
{
    private const CREATE_URL = 'https://api.monobank.ua/api/merchant/invoice/create';

    public function __construct(
        private string $token = '',
    ) {
        $this->token = $this->token ?: config('services.monobank.token');
    }

    public function createFor(Order $order): string
    {
        $response = Http::withHeaders(['X-Token' => $this->token])
            ->post(self::CREATE_URL, [
               'amount' => (int) round ($order->total_amount * 100),
               'ccy' => 980,
               'merchantPaymInfo' => [
                   'reference' => (string) $order->id,
                   'destination' => "Замовлення #{$order->id}",
               ],
                'redirectUrl' => config('app.frontend_url' . "/order/{$order->id}/success"),
//                'webHookUrl' => route('orders.payment.webhook'),
                'webHookUrl' => "https://zap-motion-bluish.ngrok-free.dev/api/monobank/webhook",
                'validity' => 3600,
            ]);

        if($response->failed()) {
            throw new RuntimeException(
                "Monobank invoice creation failed for order {$order->id}: {$response->body()}"
            );
        }

        $data = $response->json();

        $order->update(['payment_invoice_id' => $data['invoiceId']]);

        return $data['pageUrl'];

    }
}
