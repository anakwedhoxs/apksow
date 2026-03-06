<?php

namespace App\Exports;

use App\Models\Sow;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;


class SowExport implements
    FromCollection,
    WithMapping,
    ShouldAutoSize,
    WithStyles,
    WithCustomStartCell,
    WithDrawings
{
    protected ?string $divisi;

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

        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing(); 
        $drawing->setName('Logo PT'); 
        $drawing->setDescription('Logo perusahaan'); 
        $drawing->setPath($logoPath); // pastikan file ada 
        $drawing->setHeight(10);// sesuaikan tinggi agar proporsional$drawing->setCoordinates('A4'); // posisi tepat di sel A4 $drawing->setOffsetX(10); // geser sedikit ke kanan $drawing->setOffsetY(5); // geser sedikit ke bawah
        $drawing->setCoordinates('A4'); // posisi tepat di sel A4 
        $drawing->setOffsetX(10); // geser sedikit ke kanan 
        $drawing->setOffsetY(2); // geser sedikit ke bawah

        return [$drawing];
    }


    public function __construct(?string $divisi = null)
    {
        $this->divisi = $divisi;
    }

    public function collection()
{
    return Sow::with(['inventaris', 'hostname']) // tambahkan hostname
        ->when($this->divisi, fn (Builder $q) =>
            $q->where('divisi', $this->divisi)
        )
        ->orderBy('tanggal_penggunaan', 'desc')
        ->get();
}

    public function map($sow): array
{
    static $no = 1;

    return [
        $no++,
        strtoupper($sow->inventaris?->Kategori ?? '-'),
        strtoupper($sow->inventaris?->Merk ?? '-'),
        strtoupper($sow->inventaris?->Seri ?? '-'),
        $sow->hostname?->nama ?? '-', // ✅ FIX DI SINI
        $sow->divisi ?? '-',
        optional($sow->tanggal_penggunaan)->format('d-m-Y'),
        optional($sow->tanggal_perbaikan)->format('d-m-Y'),
        $sow->helpdesk ? '✓' : '',
        $sow->form ? '✓' : '',
        $sow->nomor_perbaikan ?? '-',
        $sow->keterangan ?? '-',
    ];
}

    public function startCell(): string
    {
        return 'A8';
    }

    public function styles(Worksheet $sheet)
    {
        /** ----------------------------- * Judul utama dokumen * ----------------------------- */
        $sheet->mergeCells('E3:G3'); 
        $sheet->setCellValue('E3', 'S.O.W (Stock Out Work)'); 
        $sheet->getStyle('E3')->applyFromArray([ 
            'font' => ['bold' => true, 'size' => 14], 
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'], ]);
        /** -----------------------------
         *  Blok tanda tangan kanan atas
         * ----------------------------- */
        $sheet->setCellValue('I2', 'Dibuat');
        $sheet->setCellValue('J2', 'Diketahui');
        $sheet->setCellValue('K2', 'Disetujui');
        $sheet->setCellValue('L2', 'Diterima');

        $sheet->setCellValue('K4', 'GM');
        $sheet->setCellValue('L4', 'GA');

        // Atur tinggi baris tanda tangan
        $sheet->getRowDimension(2)->setRowHeight(10);
        $sheet->getRowDimension(3)->setRowHeight(40);
        $sheet->getRowDimension(4)->setRowHeight(10);

        // Atur posisi tengah vertikal untuk GM dan GA
        $sheet->getStyle('K3:L3')->getAlignment()->setVertical('center');

        // Styling blok tanda tangan
        $sheet->getStyle('I2:L2')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);

        $sheet->getStyle('I2:L4')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ]);

        $sheet->getStyle('I2:L4')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('I2:L4')->getAlignment()->setVertical('center');

        /** -----------------------------
         *  Header utama tabel (baris 6–7)
         * ----------------------------- */
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

        $sheet->getStyle('A6:L6')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E5E7EB']],
        ]);
        $sheet->getRowDimension(6)->setRowHeight(25);

        $sheet->getStyle('I7:J7')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F3F4F6']],
        ]);

        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A6:L{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ]);

        $sheet->getStyle("A8:A{$lastRow}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("I8:J{$lastRow}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("G8:H{$lastRow}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("K8:K{$lastRow}")->getAlignment()->setHorizontal('center');

        $sheet->getStyle('A6:L7')->getAlignment()->setVertical('center');

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
