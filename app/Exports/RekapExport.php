<?php

namespace App\Exports;

use App\Models\Rekap;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RekapExport implements
    FromCollection,
    WithStyles,
    WithCustomStartCell,
    WithColumnWidths
{
    const TABLE_START_ROW = 11;

    protected ?string $divisi;

    public function __construct(?string $divisi = null)
    {
        $this->divisi = $divisi;
    }

    /**
     * =====================
     * DATA (FIX JUMLAH)
     * =====================
     */
    public function collection(): Collection
    {
        $data = Rekap::query()
            ->orderBy('kategori')
            ->get()
            ->groupBy('kategori');

        $rows = collect();
        $no = 1;

        foreach ($data as $kategori => $items) {

            $merkSeri = $items->map(function ($item) {
                return strtoupper($item->merk . ' ' . $item->seri)
                    . ' (' . $item->jumlah . ')';
            })->implode(', ');

            $rows->push([
                $no++,                         // A
                strtoupper($kategori),         // B
                $merkSeri,                     // C
                '',                            // D (merge)
                '',                            // E (merge)
                '',                            // F (merge)
                $items->sum('jumlah'),         // G ← JUMLAH FIX
            ]);
        }

        return $rows;
    }

    public function startCell(): string
    {
        return 'A' . self::TABLE_START_ROW;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 18,
            'C' => 8,
            'D' => 14.33,
            'E' => 14.33,
            'F' => 14.33,
            'G' => 14.33,
        ];
    }

    /**
     * =====================
     * STYLING (TIDAK DIUBAH)
     * =====================
     */
    public function styles(Worksheet $sheet)
    {
        /** HEADER ATAS */
        $sheet->setCellValue('A1', 'CARGLOSS');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['argb' => 'FFFF0000']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        $sheet->setCellValue('G1', 'FO-GDG-02');
        $sheet->getStyle('G1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);

        $sheet->setCellValue('A3', 'STOCK OUT WORK');
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        /** BLOK TANDA TANGAN */
        $sheet->getRowDimension(4)->setRowHeight(42);

        $sheet->setCellValue('D3', 'Dibuat');
        $sheet->setCellValue('E3', 'Diketahui');
        $sheet->setCellValue('F3', 'Disetujui');
        $sheet->setCellValue('G3', 'Diterima');

        $sheet->setCellValue('F5', 'GM');
        $sheet->setCellValue('G5', 'GA');

        $sheet->getStyle('D3:G5')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
        ]);

        $sheet->getStyle('D3:G3')->getFont()->setBold(true);

        /** INFO */
        $sheet->setCellValue('A7', 'Dept.: MIS');
        $sheet->setCellValue('A8', 'No.: __________________');
        $sheet->setCellValue('F8', 'Tanggal: ____ / ____ / ______');

        /** HEADER TABEL */
        $headerRow = self::TABLE_START_ROW - 1;

        $sheet->setCellValue("A{$headerRow}", 'No');
        $sheet->setCellValue("B{$headerRow}", 'Kategori');
        $sheet->mergeCells("C{$headerRow}:F{$headerRow}");
        $sheet->setCellValue("C{$headerRow}", 'Merk / Seri');
        $sheet->setCellValue("G{$headerRow}", 'Jumlah');

        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '14532D']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
        ]);

        /** STYLE DATA */
        $lastRow = $sheet->getHighestRow();

        for ($row = self::TABLE_START_ROW; $row <= $lastRow; $row++) {
            $sheet->mergeCells("C{$row}:F{$row}");
        }

        $sheet->getStyle("A{$headerRow}:G{$lastRow}")
            ->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ],
            ]);

        $sheet->getStyle("A" . self::TABLE_START_ROW . ":A{$lastRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("G" . self::TABLE_START_ROW . ":G{$lastRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("C" . self::TABLE_START_ROW . ":F{$lastRow}")
            ->getAlignment()->setWrapText(true);

        /** KETERANGAN */
        $sheet->setCellValue('A' . ($lastRow + 1), 'Keterangan:');
        $sheet->mergeCells('A' . ($lastRow + 2) . ':D' . ($lastRow + 2));

        $sheet->getStyle('A' . ($lastRow + 2))
            ->getFont()->setBold(true);

        $sheet->getStyle('A' . ($lastRow + 1) . ':G' . ($lastRow + 3))
            ->applyFromArray([
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ]);

        $sheet->setCellValue('A' . ($lastRow + 4),
            '*1 Lampirkan dokumentasi pembuangan/penyerahan.');
        $sheet->getStyle('A' . ($lastRow + 4))
        ->getFont()
        ->setSize(6);
        $sheet->setCellValue('G' . ($lastRow + 4), 'Rev 03;02/11/21');

        $sheet->getStyle('G' . ($lastRow + 4))->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'font' => [
                'size' => 10,
            ],
        ]);
        $sheet->setCellValue('A' . ($lastRow + 5),
            'CC/Tembusan : Putih : Dept. Penerbit - Merah : GA - Kuning : ACC');
        $sheet->getStyle('A' . ($lastRow + 5))
        ->getFont()
        ->setSize(6);
        $sheet->setCellValue('A' . ($lastRow + 6), 'CARGLOSS');
        $sheet->setCellValue('G' . ($lastRow + 6), 'FO-GDG-02');

        $sheet->getStyle('A' . ($lastRow + 6))->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFF0000'],
                'size' => 14
            ]
        ]);

        $sheet->getStyle('G' . ($lastRow + 6))->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'font' => ['bold' => true, 'size' => 14]
        ]);
    }
}
