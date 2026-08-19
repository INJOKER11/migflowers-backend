<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class OrderStats extends ChartWidget
{
    protected ?string $heading = 'Order Stats';

    protected function getData(): array
    {
        $orders = Order::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as revenue')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Orders',
                    'data' => $orders->pluck('count'),
                ],
                [
                    'label' => 'Revenue',
                    'data' => $orders->pluck('revenue'),
                    'yAxisID' => 'y1',
                    'borderColor' => 'transparent',
                ],
            ],
            'labels' => $orders->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('M d')),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'scales' => [
                'y' => ['position' => 'left'],
                'y1' => [
                    'position' => 'right',
                    'grid' => ['drawOnChartArea' => false],
                ],
            ],
        ];
    }
    protected function getType(): string
    {
        return 'line';
    }
}
