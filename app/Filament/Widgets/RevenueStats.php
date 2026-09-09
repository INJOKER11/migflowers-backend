<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class RevenueStats extends ChartWidget
{
    protected ?string $heading = 'Статистика доходу';

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
                    'label' => 'Дохід',
                    'data' => $orders->pluck('revenue'),
                ],
            ],
            'labels' => $orders->pluck('date')->map(fn ($date) => Carbon::parse($date)->locale('uk')->translatedFormat('d M')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
