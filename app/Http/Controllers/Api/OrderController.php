<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\District;
use App\Models\Order;
use App\Models\Product;
use App\Services\MonobankInvoiceService;
use App\Services\OrderNotificationService;
use App\Services\OrderService;
use App\Services\TelegramService;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orders,
        private OrderNotificationService $notifications,
        private MonobankInvoiceService $monobank,
    ){}
    public function store(StoreOrderRequest $request)
    {
        $order = $this->orders->create($request->validated());

        $this->notifications->notifyNewOrder($order);

        $paymentUrl = $order->payment_method === 'online'
            ? $this->monobank->createFor($order)
            : null;

        return (new OrderResource($order))
            ->additional(['payment_url' => $paymentUrl]);
    }
}
