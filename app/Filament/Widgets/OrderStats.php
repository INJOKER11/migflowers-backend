<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class OrderStats extends ChartWidget
{
    protected ?string $heading = 'Статистика замовлень';

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
                    'label' => 'Кількість замовлень',
                    'data' => $orders->pluck('count'),
                ],
                [
                    'label' => 'Дохід',
                    'data' => $orders->pluck('revenue'),
                    'yAxisID' => 'y1',
                    'borderColor' => 'transparent',
                ],
            ],
            'labels' => $orders->pluck('date')->map(fn ($date) => Carbon::parse($date)->locale('uk')->translatedFormat('d M')),
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
