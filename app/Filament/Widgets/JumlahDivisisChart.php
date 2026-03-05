<?php

namespace App\Filament\Widgets;

use App\Models\Sow;
use App\Models\SowPc;
use App\Models\SowCpu;
use Filament\Widgets\ChartWidget;

class JumlahDivisisChart extends ChartWidget
{
    protected static ?string $heading = ' ';

    protected function getData(): array
    {
        // Ambil semua divisi dari ketiga model
        $divisis = collect()
            ->merge(Sow::pluck('divisi'))
            ->merge(SowPc::pluck('divisi'))
            ->merge(SowCpu::pluck('divisi'))
            ->filter(); // buang null / kosong

        $divisiCounts = $divisis
            ->groupBy(fn ($divisi) => $divisi)
            ->map(fn ($group) => $group->count());

        // Mapping warna sesuai divisi
        $colorMap = [
            'MCP' => '#ef4444',
            'MKM' => '#3b82f6',
            'MKP' => '#f97316',
            'PPM' => '#22c55e',
            'PPG' => '#facc15',
        ];

        $labels = $divisiCounts->keys()->toArray();
        $data   = $divisiCounts->values()->toArray();
        $colors = collect($labels)
            ->map(fn ($divisi) => $colorMap[$divisi] ?? '#9ca3af')
            ->toArray();

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
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
                    'text' => "Jumlah per Divisi (Semua SOW)",
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