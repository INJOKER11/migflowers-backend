<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\LiqPayService;
use App\Services\OrderNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiqPayWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.liqpay.public_key' => 'sandbox_public',
            'services.liqpay.private_key' => 'sandbox_private',
            'services.liqpay.sandbox' => false,
            'app.url' => 'https://api.example.test',
            'app.frontend_url' => 'https://www.example.test',
        ]);

        $this->mock(OrderNotificationService::class)->shouldReceive('notifyUpdatedOrder');
    }

    private function order(): Order
    {
        $order = Order::create([
            'customer_name' => 'Тест',
            'customer_email' => 'test@example.test',
            'customer_phone' => '+380000000000',
            'delivery_address' => 'Одеса',
            'delivery_date' => now()->toDateString(),
            'delivery_method' => 'delivery',
            'status' => 'pending',
            'total_amount' => 1290,
            'payment_method' => 'online',
        ]);
        $order->update(['payment_invoice_id' => $order->order_number]);

        return $order;
    }

    private function sendCallback(array $payload, ?string $signature = null)
    {
        $liqpay = app(LiqPayService::class);
        $data = $liqpay->encode($payload);

        return $this->post('/api/liqpay/webhook', [
            'data' => $data,
            'signature' => $signature ?? $liqpay->sign($data),
        ]);
    }

    public function test_checkout_url_is_signed_and_points_back_to_the_order(): void
    {
        $order = $this->order();
        $url = app(LiqPayService::class)->createFor($order);

        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $params = json_decode(base64_decode($query['data']), true);

        $this->assertStringStartsWith('https://www.liqpay.ua/api/3/checkout?', $url);
        $this->assertSame(
            base64_encode(sha1('sandbox_private' . $query['data'] . 'sandbox_private', true)),
            $query['signature'],
        );
        $this->assertSame($order->order_number, $params['order_id']);
        $this->assertEquals(1290, $params['amount']);
        $this->assertSame('https://api.example.test/api/liqpay/webhook', $params['server_url']);
        $this->assertSame("https://www.example.test/checkout/{$order->order_number}", $params['result_url']);
    }

    public function test_success_marks_the_order_paid(): void
    {
        $order = $this->order();

        $this->sendCallback([
            'order_id' => $order->order_number, 'status' => 'success',
            'amount' => 1290, 'currency' => 'UAH', 'payment_id' => 123456,
        ])->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->status);
        $this->assertSame('123456', $order->payment_reference);
    }

    public function test_forged_signature_is_rejected(): void
    {
        $order = $this->order();

        $this->sendCallback(['order_id' => $order->order_number, 'status' => 'success', 'amount' => 1290, 'currency' => 'UAH'], 'forged')
            ->assertStatus(400);

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_wrong_amount_does_not_mark_paid(): void
    {
        $order = $this->order();

        $this->sendCallback(['order_id' => $order->order_number, 'status' => 'success', 'amount' => 1, 'currency' => 'UAH'])
            ->assertOk();

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_failure_and_sandbox_statuses(): void
    {
        $order = $this->order();

        // Sandbox payments are ignored outside sandbox mode.
        $this->sendCallback(['order_id' => $order->order_number, 'status' => 'sandbox', 'amount' => 1290, 'currency' => 'UAH']);
        $this->assertSame('pending', $order->fresh()->status);

        $this->sendCallback(['order_id' => $order->order_number, 'status' => 'failure']);
        $this->assertSame('payment_failed', $order->fresh()->status);
    }
}
