<?php

namespace App\Exports;

use App\Models\SowCpuArsipItem;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class SowCpuArsipExport implements
    FromCollection,
    WithMapping,
    WithStyles,
    WithCustomStartCell,
    WithDrawings
{
    protected int $arsipId;
    protected ?string $divisi;

    public function __construct(int $arsipId, ?string $divisi = null)
    {
        $this->arsipId = $arsipId;
        $this->divisi = $divisi;
    }

    /* ================= QUERY ================= */
    public function collection()
    {
        return SowCpuArsipItem::with([
                'prosesor','ram','motherboard','hostname','pic','arsip'
            ])
            ->where('sow_cpu_arsip_id', $this->arsipId)
            ->when($this->divisi, fn (Builder $q) =>
                $q->where('divisi', $this->divisi)
            )
            ->orderBy('tanggal_penggunaan','desc')
            ->get();
    }

    /* ================= MAPPING ================= */
    public function map($sow): array
    {
        static $no = 1;

        return [
            $no++,
            strtoupper(($sow->prosesor?->Merk ?? '-') . ' ' . ($sow->prosesor?->Seri ?? '')),
            strtoupper(($sow->ram?->Merk ?? '-') . ' ' . ($sow->ram?->Seri ?? '')),
            strtoupper(($sow->motherboard?->Merk ?? '-') . ' ' . ($sow->motherboard?->Seri ?? '')),
            optional($item->tanggal_perbaikan)->format('d-m-Y'),
            optional($item->tanggal_penggunaan)->format('d-m-Y'),
            $sow->helpdesk ? '✓' : '',
            $sow->form ? '✓' : '',
            $sow->nomor_perbaikan ?? '-',
            $sow->hostname?->nama ?? '-',
            $sow->keterangan ?? '-',
        ];
    }

    public function startCell(): string
    {
        return 'A8';
    }

    /* ================= LOGO ================= */
    public function drawings()
    {
        $logoPath = match (strtoupper($this->divisi)) {
            'MKM' => public_path('images/mkm.png'),
            'PPG' => public_path('images/ppg.png'),
            'MKP' => public_path('images/MKP.png'),
            'MCP' => public_path('images/MCP.png'),
            'PPM' => public_path('images/PPM.png'),
            default => public_path('images/Logo_cargloss_Paint.png'),
        };

        if (!file_exists($logoPath)) {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Logo PT');
        $drawing->setDescription('Logo perusahaan');
        $drawing->setPath($logoPath);
        $drawing->setHeight(20);
        $drawing->setCoordinates('A4');
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(2);

        return [$drawing];
    }

    /* ================= STYLING ================= */
    public function styles(Worksheet $sheet)
    {
        // Judul
        $sheet->mergeCells('E3:G3');
        $sheet->setCellValue('E3', 'HARDWARE: CPU SET');
        $sheet->getStyle('E3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Blok TTD
        $sheet->setCellValue('I2', 'Dibuat');
        $sheet->setCellValue('J2', 'Diketahui');
        $sheet->setCellValue('K2', 'Disetujui');
        $sheet->setCellValue('L2', 'Diterima');
        $sheet->setCellValue('K4', 'GM');
        $sheet->setCellValue('L4', 'GA');

        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(45);
        $sheet->getRowDimension(4)->setRowHeight(18);

        $sheet->getStyle('I2:L4')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ]);
        $sheet->getStyle('I2:L2')->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getStyle('I2:L4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I2:L4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Header
        $sheet->setCellValue('A6', 'No');
        $sheet->setCellValue('B6', 'Prosesor');
        $sheet->setCellValue('C6', 'RAM');
        $sheet->setCellValue('D6', 'Motherboard');
        $sheet->setCellValue('E6', 'Tanggal Perbaikan');
        $sheet->setCellValue('F6', 'Tanggal Pemakaian');
        $sheet->mergeCells('G6:H6');
        $sheet->setCellValue('G6', 'SPPI');
        $sheet->setCellValue('G7', 'Helpdesk');
        $sheet->setCellValue('H7', 'Form');
        $sheet->setCellValue('I6', 'Nomor Perbaikan');
        $sheet->setCellValue('J6', 'Hostname');
        $sheet->setCellValue('K6', 'Keterangan');

        foreach (['A','B','C','D','E','F','I','J','K'] as $col) {
            $sheet->mergeCells("{$col}6:{$col}7");
        }

        // Style Header
        $sheet->getStyle('A6:K7')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => 'E5E7EB'],
            ],
        ]);

        // Width
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(22);
        $sheet->getColumnDimension('J')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(20);
        $sheet->getColumnDimension('L')->setWidth(15);

        // Wrap Text + Auto Height
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("B8:K{$lastRow}")->getAlignment()->setWrapText(true);
        for ($i = 8; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(-1);
        }

        // Border
        $sheet->getStyle("A6:K{$lastRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle('thin');

        // Alignment Data
        $sheet->getStyle("A8:A{$lastRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G8:H{$lastRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E8:F{$lastRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}
