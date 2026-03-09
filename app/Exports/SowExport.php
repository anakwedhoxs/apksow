<?php

namespace App\Exports;

use App\Models\Sow;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SowExport implements
    FromCollection,
    WithMapping,
    WithStyles,
    WithCustomStartCell,
    WithDrawings
{
    protected ?string $divisi;

    public function __construct(?string $divisi = null)
    {
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
        $drawing->setCoordinates('A5'); 
        $drawing->setOffsetX(10); 
        $drawing->setOffsetY(15);

        return [$drawing];
    }

    /* ================= QUERY ================= */
    public function collection()
    {
        return Sow::with('inventaris')
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
        /** Ukuran baris dan kolom */
        $sheet->getRowDimension(5)->setRowHeight(25);

        $columns = [
            'A' => 3, 'B' => 10, 'C' => 10, 'D' => 10, 'E' => 10, 'F' => 8,
            'G' => 12, 'H' => 10, 'I' => 10, 'J' => 5, 'K' => 15, 'L' => 15
        ];

        foreach ($columns as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        /** Judul */
        $sheet->mergeCells('D3:F3');
        $sheet->setCellValue('D3', 'S.O.W (Stock Out Work)');
        $sheet->getStyle('D3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center'
            ],
        ]);

        /** Tanda tangan */
        $ttdPath = public_path('images/ttd-1.png');

        $drawing = new Drawing();
        $drawing->setName('TTD');
        $drawing->setDescription('Tanda Tangan');
        $drawing->setPath($ttdPath);
        $drawing->setCoordinates('H1');
        $drawing->setHeight(105);
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(2);
        $drawing->setWorksheet($sheet);

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

        /** Style header */
        $sheet->getStyle('A6:L7')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E5E7EB']],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ]);

        /** Border & Wrap semua data */
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle("A8:L{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin']]
        ]);

        // Wrap text untuk semua kolom A–L
        $sheet->getStyle("A6:L{$lastRow}")
            ->getAlignment()
            ->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Auto height agar wrap terlihat
        for ($i = 6; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(-1);
        }
    }
}
