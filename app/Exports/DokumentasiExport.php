<?php

namespace App\Exports;

use App\Models\Dokumentasi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class DokumentasiExport implements FromCollection, WithStyles
{
    protected $data;

    public function collection()
    {
        $this->data = Dokumentasi::all();
        return collect([]);
    }

    public function styles(Worksheet $sheet)
    {
        /* =========================
         * HEADER UTAMA
         * ========================= */
        $sheet->setCellValue('A1', 'LAMPIRAN');
        $sheet->setCellValue('A2', 'S.O.W (STOCK OUT WORK)');

        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
        ]);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => [
                'horizontal' => 'left',
                'vertical' => 'center',
                'wrapText' => false
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(19.44);

        /* =========================
         * BLOK TANDA TANGAN C–E
         * ========================= */
        $sheet->setCellValue('C1', 'Dibuat');
        $sheet->setCellValue('D1', 'Diketahui');
        $sheet->setCellValue('E1', 'Disetujui');

        $sheet->getStyle('C1:E1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(20);

        foreach (['C', 'D', 'E'] as $col) {
            // Merge baris 2 untuk gambar
            $sheet->mergeCells("{$col}2:{$col}3");

            // Border dan alignment
            $sheet->getStyle("{$col}2")->applyFromArray([
                'alignment' => ['horizontal' => 'center', 'vertical' => 'top'],
                'borders' => ['allBorders' => ['borderStyle' => 'thin']],
            ]);

            // Baris 3 untuk label
            $label = $col === 'E' ? 'GA' : ' ';
            $sheet->setCellValue("{$col}4", $label);
            $sheet->getStyle("{$col}4")->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                'borders' => ['allBorders' => ['borderStyle' => 'thin']],
            ]);
        }
        
        $sheet->getRowDimension(2)->setRowHeight(19.44);
        $sheet->getRowDimension(3)->setRowHeight(35);
        $sheet->getRowDimension(4)->setRowHeight(20);

        /* =========================
         * GRID BARANG
         * ========================= */
        $itemsPerRow = 5;
        $startRow    = 6;
        $columns     = range('A', chr(ord('A') + $itemsPerRow - 1)); // A–E

        $imgWidth  = 153;
        $imgHeight = 102;

        foreach ($this->data as $index => $doc) {
            $colIndex = $index % $itemsPerRow;
            $rowGroup = intdiv($index, $itemsPerRow);

            $col = $columns[$colIndex];
            $row = $startRow + ($rowGroup * 2);

            $sheet->setCellValue("{$col}{$row}", $doc->nama_barang ?? '-');

            $sheet->getStyle("{$col}{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical'   => 'center',
                    'wrapText'   => true,
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => 'thin'],
                ],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(30);

            $imageRow = $row + 1;

            if ($doc->foto && file_exists(public_path('storage/' . $doc->foto))) {
                $drawing = new Drawing();
                $drawing->setPath(public_path('storage/' . $doc->foto));
                $drawing->setResizeProportional(false);
                $drawing->setWidth($imgWidth);
                $drawing->setHeight($imgHeight);
                $drawing->setCoordinates("{$col}{$imageRow}");
                $drawing->setOffsetX(10);
                $drawing->setOffsetY(5);
                $drawing->setWorksheet($sheet);
            }

            $sheet->getRowDimension($imageRow)->setRowHeight($imgHeight + 10);
            $sheet->getStyle("{$col}{$imageRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => 'thin'],
                ],
            ]);
        }

        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setWidth(23);
        }
    }
}
