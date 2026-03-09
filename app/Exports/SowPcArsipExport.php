<?php

namespace App\Exports;

use App\Models\SowPcArsipItem;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class SowPcArsipExport implements
    FromCollection,
    WithMapping,
    WithStyles,
    WithCustomStartCell,
    WithDrawings
{
    protected ?string $divisi;

     public function __construct(int $arsipId, ?string $divisi = null)
    {
        $this->arsipId = $arsipId;
        $this->divisi = $divisi;
    }


    /*
    |--------------------------------------------------------------------------
    | LOGO
    |--------------------------------------------------------------------------
    */
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


        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo Perusahaan');
        $drawing->setPath($logoPath);
        $drawing->setHeight(15);
        $drawing->setCoordinates('A5');
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(15);

        return [$drawing];
    }

    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */
    public function collection()
    {
        return SowPcArsipItem::with([
                'case','psu','prosesor','ram','motherboard','hostname','arsip'
            ])
            ->where('sow_pc_arsip_id', $this->arsipId)
            ->when($this->divisi, fn (Builder $q) =>
                $q->where('divisi', $this->divisi)
            )
            ->orderBy('tanggal_penggunaan', 'desc')
            ->get();

    }

    /*
    |--------------------------------------------------------------------------
    | MAPPING
    |--------------------------------------------------------------------------
    */
    public function map($sow): array
    {
        static $no = 1;

        return [
            $no++,
            strtoupper(($sow->case?->Merk ?? '-') . ' / ' . ($sow->psu?->Merk ?? '-')),
            strtoupper(($sow->prosesor?->Merk ?? '-') . ' / ' . ($sow->ram?->Merk ?? '-')),
            strtoupper(($sow->motherboard?->Merk ?? '-')),
            optional($sow->tanggal_perbaikan)->format('d-m-Y'),
            optional($sow->tanggal_penggunaan)->format('d-m-Y'),
            $sow->helpdesk ? '✓' : '',
            $sow->form ? '✓' : '',
            $sow->nomor_perbaikan ?? '-',
            $sow->hostname?->nama ?? '-',
            $sow->keterangan ?? '-',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | START ROW
    |--------------------------------------------------------------------------
    */
    public function startCell(): string
    {
        return 'A8';
    }

    /*
    |--------------------------------------------------------------------------
    | STYLING
    |--------------------------------------------------------------------------
    */
    public function styles(Worksheet $sheet)
    {
        $sheet->getRowDimension(5)->setRowHeight(25);

        $columns = [
            'A' => 3, 'B' => 12, 'C' => 10, 'D' => 10, 'E' => 10, 'F' => 11,
            'G' => 12, 'H' => 5, 'I' => 15, 'J' => 15, 'K' => 20
        ];

        foreach ($columns as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        /*
        |--------------------------------------------------------------------------
        | JUDUL
        |--------------------------------------------------------------------------
        */
        $sheet->mergeCells('E3:G3');
        $sheet->setCellValue('E3', 'HARDWARE: PC SET');
        $sheet->getStyle('E3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
        ]);

        /* ================= BLOK TTD ================= */
    $ttdPath = public_path('images/ttd-1.png');

        $drawing = new Drawing();
        $drawing->setName('TTD');
        $drawing->setDescription('Tanda Tangan');
        $drawing->setPath($ttdPath);
        $drawing->setCoordinates('I1');
        $drawing->setHeight(105);
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(2);
        $drawing->setWorksheet($sheet);


        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */
        $sheet->setCellValue('A6', 'No');
        $sheet->setCellValue('B6', 'CASHING DAN PSU');
        $sheet->setCellValue('C6', 'PROSESOR DAN RAM');
        $sheet->setCellValue('D6', 'MOTHERBOARD');
        $sheet->setCellValue('E6', 'Tanggal Perbaikan');
        $sheet->setCellValue('F6', 'Tanggal Pemakaian');

        $sheet->mergeCells('G6:H6');
        $sheet->setCellValue('G6', 'SPPI');

        $sheet->setCellValue('I6', 'Nomor Perbaikan');
        $sheet->setCellValue('J6', 'Hostname');
        $sheet->setCellValue('K6', 'Keterangan');

        $sheet->setCellValue('G7', 'Helpdesk');
        $sheet->setCellValue('H7', 'Form');

        foreach (['A','B','C','D','E','F','I','J','K'] as $col) {
            $sheet->mergeCells("{$col}6:{$col}7");
        }

        /*
        |--------------------------------------------------------------------------
        | WIDTH
        |--------------------------------------------------------------------------
        */
        

        /*
        |--------------------------------------------------------------------------
        | STYLE HEADER
        |--------------------------------------------------------------------------
        */
        /** Style header */
        $sheet->getStyle('A6:K7')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E5E7EB']],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ]);

        /** Border & Wrap semua data */
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle("A8:K{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin']]
        ]);

        // Wrap text untuk semua kolom A–L
        $sheet->getStyle("A6:K{$lastRow}")
            ->getAlignment()
            ->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Auto height agar wrap terlihat
        for ($i = 6; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(-1);
        }
    }
}
