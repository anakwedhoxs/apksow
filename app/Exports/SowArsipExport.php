<?php

namespace App\Exports;

use App\Models\SowArsipItem;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;


class SowArsipExport implements
    FromCollection,
    WithMapping,
    ShouldAutoSize,
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

    /* ================= LOGO ================= */
    public function drawings(): array
    {
        $logoPath = match (strtoupper($this->divisi ?? '')) {
            'MKM' => public_path('images/mkm.png'),
            'PPG' => public_path('images/ppg.png'),
            'MKP' => public_path('images/MKP.png'),
            'MCP' => public_path('images/MCP.png'),
            'PPM' => public_path('images/PPM.png'),
            default => public_path('images/Logo_cargloss_Paint.png'),
        };

        $drawing = new Drawing();
        $drawing->setName('Logo PT');
        $drawing->setDescription('Logo perusahaan');
        $drawing->setPath($logoPath);
        $drawing->setHeight(10);
        $drawing->setCoordinates('A4');
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(2);

        return [$drawing];
    }

    /* ================= QUERY ================= */
    public function collection()
    {
        return SowArsipItem::with('inventaris')
            ->where('sow_arsip_id', $this->arsipId)
            ->when($this->divisi, fn (Builder $q) =>
                $q->where('divisi', $this->divisi)
            )
            ->orderBy('tanggal_penggunaan', 'desc')
            ->get();
    }

    /* ================= MAP ================= */
    public function map($item): array
    {
        static $no = 1;

        return [
            $no++,
            strtoupper($item->inventaris?->Kategori ?? '-'),
            strtoupper($item->inventaris?->Merk ?? '-'),
            strtoupper($item->inventaris?->Seri ?? '-'),
            $item->hostname ?? '-',
            $item->divisi ?? '-',
            optional($item->tanggal_penggunaan)->format('d-m-Y'),
            optional($item->tanggal_perbaikan)->format('d-m-Y'),
            $item->helpdesk ? '✓' : '',
            $item->form ? '✓' : '',
            $item->nomor_perbaikan ?? '-',
            $item->keterangan ?? '-',
        ];
    }

    /* ================= START CELL ================= */
    public function startCell(): string
    {
        return 'A8';
    }

    /* ================= STYLES ================= */
    public function styles(Worksheet $sheet)
    {
        /** Judul */
        $sheet->mergeCells('E3:G3');
        $sheet->setCellValue('E3', 'S.O.W (Stock Out Work)');
        $sheet->getStyle('E3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);

        /** Tanda tangan */
        $sheet->setCellValue('I2', 'Dibuat');
        $sheet->setCellValue('J2', 'Diketahui');
        $sheet->setCellValue('K2', 'Disetujui');
        $sheet->setCellValue('L2', 'Diterima');

        $sheet->setCellValue('K4', 'GM');
        $sheet->setCellValue('L4', 'GA');

        $sheet->getStyle('I2:L4')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ]);

        /** Header tabel */
        $sheet->setCellValue('A6', 'No');
        $sheet->setCellValue('B6', 'Kategori');
        $sheet->setCellValue('C6', 'Merk');
        $sheet->setCellValue('D6', 'Seri');
        $sheet->setCellValue('E6', 'Hostname');
        $sheet->setCellValue('F6', 'Divisi');
        $sheet->setCellValue('G6', 'Tanggal Penggunaan');
        $sheet->setCellValue('H6', 'Tanggal Perbaikan');
        $sheet->mergeCells('I6:J6');
        $sheet->setCellValue('I6', 'SPPI');
        $sheet->setCellValue('K6', 'Nomor Perbaikan');
        $sheet->setCellValue('L6', 'Keterangan');

        $sheet->setCellValue('I7', 'Helpdesk');
        $sheet->setCellValue('J7', 'Form');

        foreach (['A','B','C','D','E','F','G','H','K','L'] as $col) {
            $sheet->mergeCells("{$col}6:{$col}7");
        }

        $sheet->getStyle('A6:L7')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E5E7EB']],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ]);

        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A8:L{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ]);

        $lastRow = $sheet->getHighestRow();

        // Checklist Helpdesk & Form rata tengah
        $sheet->getStyle("I8:J{$lastRow}")->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Supaya nomor juga rata tengah
        $sheet->getStyle("A8:A{$lastRow}")->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Tanggal rata tengah
        $sheet->getStyle("G8:H{$lastRow}")->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Nomor perbaikan rata tengah
        $sheet->getStyle("K8:K{$lastRow}")->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

    }
}
