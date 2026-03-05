<?php

namespace App\Filament\Widgets;

use App\Models\Sow;
use App\Models\SowPc;
use App\Models\SowCpu;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // TOTAL SEMUA SOW
        $totalSow = 
            Sow::count() +
            SowPc::count() +
            SowCpu::count();

        // TOTAL ACCEPT
        $totalAccepted =
            Sow::where('status', false)->count() +
            SowPc::where('status', false)->count() +
            SowCpu::where('status', false)->count();

        // TOTAL REJECT
        $totalRejected =
            Sow::where('status', true)->count() +
            SowPc::where('status', true)->count() +
            SowCpu::where('status', true)->count();

        return [
            Stat::make('Jumlah SOW', $totalSow)
                ->icon('heroicon-s-queue-list')
                ->color('primary')
                ->extraAttributes([
                    'style' => 'border: 2px solid #3b82f6; border-radius: 0.80rem;',
                ]),

            Stat::make('Jumlah Accepted', $totalAccepted)
                ->icon('heroicon-s-check-circle')
                ->color('success')
                ->extraAttributes([
                    'style' => 'border: 2px solid #22c55e; border-radius: 0.80rem;',
                ]),

            Stat::make('Jumlah Rejected', $totalRejected)
                ->icon('heroicon-s-x-circle')
                ->color('danger')
                ->extraAttributes([
                    'style' => 'border: 2px solid #ef4444; border-radius: 0.80rem;',
                ]),

        ];    }
}