<?php

namespace App\Exports;

use App\Models\SowPc;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class SowPcExport implements
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
        $drawing->setHeight(25);
        $drawing->setCoordinates('A4');
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(5);

        return [$drawing];
    }

    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */
    public function collection()
    {
        return SowPc::with([
                'case',
                'psu',
                'prosesor',
                'ram',
                'motherboard',
                'hostname'
            ])
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
        'borders' => [
            'allBorders' => ['borderStyle' => 'thin'],
        ],
    ]);

    $sheet->getStyle('I2:L2')->applyFromArray([
        'font' => ['bold' => true],
    ]);

    $sheet->getStyle('I2:L4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('I2:L4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


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

        /*
        |--------------------------------------------------------------------------
        | STYLE HEADER
        |--------------------------------------------------------------------------
        */
        $sheet->getStyle('A6:K7')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => 'E5E7EB']
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | WRAP TEXT + AUTO HEIGHT
        |--------------------------------------------------------------------------
        */
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle("B8:K{$lastRow}")
            ->getAlignment()
            ->setWrapText(true);

        for ($i = 8; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(-1);
        }

        /*
        |--------------------------------------------------------------------------
        | BORDER
        |--------------------------------------------------------------------------
        */
        $sheet->getStyle("A6:K{$lastRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle('thin');

        /*
        |--------------------------------------------------------------------------
        | ALIGNMENT DATA
        |--------------------------------------------------------------------------
        */
        $sheet->getStyle("A8:A{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("G8:H{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("E8:F{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}