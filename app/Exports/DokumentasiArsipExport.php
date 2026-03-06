<?php

namespace App\Exports;

use App\Models\DokumentasiArsipItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class DokumentasiArsipExport implements FromCollection, WithStyles
{
    protected $data;

    public function collection()
    {
        $this->data = DokumentasiArsipItem::all();
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
        $ttdPath = public_path('images/ttd.png'); // 1 file yang sama

        $cols = ['C'];

        foreach ($cols as $col) {
            $drawing = new Drawing();
            $drawing->setName('TTD');
            $drawing->setDescription('Tanda Tangan');
            $drawing->setPath($ttdPath);
            $drawing->setCoordinates($col . '1');

            // atur ukuran gambar
            $drawing->setHeight(105);

            // posisi di dalam cell
            $drawing->setOffsetX(10);
            $drawing->setOffsetY(5);

            $drawing->setWorksheet($sheet);
                }
        
       

        /* =========================
         * GRID BARANG
         * ========================= */
        $itemsPerRow = 4;
        $startRow    = 7;
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
               
            ]);
            $sheet->getRowDimension($row)->setRowHeight(15);

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
                
            ]);
        }

        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setWidth(23);
        }
    }
}
