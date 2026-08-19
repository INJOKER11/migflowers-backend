<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class RevenueStats extends ChartWidget
{
    protected ?string $heading = 'Revenue Stats';

    protected function getData(): array
    {
        $orders = Order::selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Выручка',
                    'data' => $orders->pluck('revenue'),
                ],
            ],
            'labels' => $orders->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('M d')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
