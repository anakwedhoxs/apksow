<?php

namespace App\Traits;

use App\Models\Rekap;
use Illuminate\Support\Facades\DB;

trait UpdatesRekap
{
    // Hitung dan update rekap
    public function updateRekap(string $kategori, string $merk, string $seri): void
    {
        // ==========================
        // 1️⃣ Hitung dari SOW biasa
        // ==========================
        $jumlahSow = DB::table('sows')
            ->join('inventaris', 'sows.inventaris_id', '=', 'inventaris.id')
            ->where('inventaris.Kategori', $kategori)
            ->where('inventaris.Merk', $merk)
            ->where('inventaris.Seri', $seri)
            ->count();

        // ==========================
        // 2️⃣ Hitung dari SOW PC (CASE)
        // ==========================
        $jumlahPC = DB::table('sow_pcs')
            ->join('inventaris', 'sow_pcs.case_id', '=', 'inventaris.id')
            ->where('inventaris.Kategori', 'PC CASE')
            ->where('inventaris.Merk', $merk)
            ->where('inventaris.Seri', $seri)
            ->count();

        // ==========================
        // 3️⃣ Hitung dari SOW CPU
        // ==========================
        $jumlahCPU = DB::table('sow_cpu')
            ->join('inventaris', 'sow_cpu.motherboard_id', '=', 'inventaris.id')
            ->where('inventaris.Kategori', 'MOTHERBOARD')
            ->where('inventaris.Merk', $merk)
            ->where('inventaris.Seri', $seri)
            ->count();

        // Total semua
        $total = $jumlahSow + $jumlahPC + $jumlahCPU;

        // Kalau dari CPU → ubah nama kategori jadi CPU SET
        $kategoriRekap = strtolower($kategori) === 'processor'
            ? 'CPU SET'
            : $kategori;

        // Update atau create rekap
        Rekap::updateOrCreate(
            [
                'kategori' => $kategoriRekap,
                'merk'     => $merk,
                'seri'     => $seri,
            ],
            [
                'jumlah' => $total,
            ]
        );
    }

    // Trigger rekap otomatis
    public function triggerRekap(): void
    {
        if (isset($this->inventaris)) {
            // SOW biasa
            $this->updateRekap(
                $this->inventaris->Kategori,
                $this->inventaris->Merk,
                $this->inventaris->Seri
            );

        } elseif (isset($this->case)) {
            // SowPC (CASE)
            $this->updateRekap(
                'PC SET',
                $this->case->Merk,
                $this->case->Seri
            );

        } elseif (isset($this->motherboard)) {
            // SowCPU → tulis sebagai CPU SET
            $this->updateRekap(
                'CPU SET',
                $this->motherboard->Merk,
                $this->motherboard->Seri
            );
        }
    }
}