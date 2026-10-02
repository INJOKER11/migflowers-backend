<?php

namespace App\Services;

use App\Models\Order;

/**
 * LiqPay (PrivatBank) checkout. Unlike monobank there is no "create invoice"
 * call: the payment page is a signed URL we build ourselves, and the result
 * comes back to the webhook as the same data+signature pair.
 */
class LiqPayService
{
    private const CHECKOUT_URL = 'https://www.liqpay.ua/api/3/checkout';

    /** How long the payment page stays usable, matching the old invoice validity. */
    private const VALIDITY_MINUTES = 60;

    private string $publicKey;

    private string $privateKey;

    private bool $sandbox;

    public function __construct()
    {
        $this->publicKey = (string) config('services.liqpay.public_key');
        $this->privateKey = (string) config('services.liqpay.private_key');
        $this->sandbox = (bool) config('services.liqpay.sandbox');
    }

    public function createFor(Order $order): string
    {
        if ($this->publicKey === '' || $this->privateKey === '') {
            throw new \RuntimeException('LiqPay keys are not configured (LIQPAY_PUBLIC_KEY / LIQPAY_PRIVATE_KEY).');
        }

        $params = [
            'version' => 3,
            'public_key' => $this->publicKey,
            'action' => 'pay',
            'amount' => round((float) $order->total_amount, 2),
            'currency' => 'UAH',
            'description' => "Замовлення #{$order->order_number}",
            // order_number is a UUID and already unique, so it doubles as
            // LiqPay's order_id and the webhook can find the order by it.
            'order_id' => $order->order_number,
            'language' => 'uk',
            'result_url' => config('app.frontend_url') . "/checkout/{$order->order_number}",
            'server_url' => config('app.url') . '/api/liqpay/webhook',
            // LiqPay reads this as UTC.
            'expired_date' => now('UTC')->addMinutes(self::VALIDITY_MINUTES)->format('Y-m-d H:i:s'),
        ];

        if ($this->sandbox) {
            $params['sandbox'] = 1;
        }

        $data = $this->encode($params);

        $order->update(['payment_invoice_id' => $order->order_number]);

        return self::CHECKOUT_URL . '?' . http_build_query([
            'data' => $data,
            'signature' => $this->sign($data),
        ]);
    }

    public function encode(array $params): string
    {
        return base64_encode(json_encode($params, JSON_UNESCAPED_UNICODE));
    }

    public function sign(string $data): string
    {
        return base64_encode(sha1($this->privateKey . $data . $this->privateKey, true));
    }

    /**
     * Decodes a callback, or returns null if the signature does not match —
     * without this check anyone could POST "success" and mark an order paid.
     */
    public function decodeCallback(?string $data, ?string $signature): ?array
    {
        if (! is_string($data) || ! is_string($signature) || $this->privateKey === '') {
            return null;
        }

        if (! hash_equals($this->sign($data), $signature)) {
            return null;
        }

        $payload = json_decode(base64_decode($data, true) ?: '', true);

        return is_array($payload) ? $payload : null;
    }

    /** Maps a LiqPay status onto ours; null for intermediate ones (3DS, processing, …). */
    public function mapStatus(?string $status): ?string
    {
        return match ($status) {
            'success' => 'paid',
            // Test payments only count when this install is itself in sandbox mode.
            'sandbox' => $this->sandbox ? 'paid' : null,
            'failure', 'error', 'reversed' => 'payment_failed',
            default => null,
        };
    }
}
