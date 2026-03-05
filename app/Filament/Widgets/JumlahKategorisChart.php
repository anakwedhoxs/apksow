<?php

namespace App\Filament\Widgets;

use App\Models\Sow;
use App\Models\SowPc;
use App\Models\SowCpu;
use Filament\Widgets\ChartWidget;

class JumlahKategorisChart extends ChartWidget
{
    protected static ?string $heading = ' ';

    protected function getData(): array
    {
        $data = collect();

        // ===== SOW =====
        $sow = Sow::with('inventaris')->get()
            ->filter(fn ($item) => filled($item->inventaris?->Kategori))
            ->map(fn ($item) => $item->inventaris->Kategori);

        // ===== SOW PC =====
        $sowPc = SowPc::with(['case'])
            ->get()
            ->flatMap(function ($item) {
                return collect([
                    $item->case?->Kategori,
                    
                ])->filter();
            });

        // ===== SOW CPU =====
        $sowCpu = SowCpu::with(['motherboard'])
            ->get()
            ->flatMap(function ($item) {
                return collect([
                    $item->motherboard?->Kategori,
                ])->filter();
            });

        $data = $data
            ->merge($sow)
            ->merge($sowPc)
            ->merge($sowCpu);

        $kategoriCounts = $data
            ->groupBy(fn ($item) => $item)
            ->map(fn ($group) => $group->count());

        return [
            'datasets' => [
                [
                    'data' => $kategoriCounts->values()->toArray(),
                    'backgroundColor' => [
                        '#3b82f6',
                        '#22c55e',
                        '#ef4444',
                        '#f59e0b',
                        '#6366f1',
                        '#ec4899',
                        '#14b8a6',
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $kategoriCounts->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'title' => [
                    'display' => true,
                    'text' => "Jumlah per Kategori (Semua SOW)",
                    'align' => 'center',
                    'font' => [
                        'size' => 18,
                        'weight' => 'bold',
                    ],
                ],
            ],
        ];
    }
}