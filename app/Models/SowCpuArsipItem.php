<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SowCpuArsipItem extends Model
{
    protected $fillable = [
        'sow_cpu_arsip_id','prosesor_id','ram_id','motherboard_id',
        'tanggal_penggunaan','tanggal_perbaikan','helpdesk','form','nomor_perbaikan',
        'hostname_id','divisi','pic_id','keterangan','foto','status'
    ];

    protected $casts = [
    'tanggal_penggunaan' => 'date',
    'tanggal_perbaikan' => 'date',
];

    public function arsip()
    {
        return $this->belongsTo(SowCpuArsip::class);
    }

    // Relasi ke tabel master Inventaris

    public function prosesor()
    {
        return $this->belongsTo(Inventaris::class, 'prosesor_id');
    }

    public function ram()
    {
        return $this->belongsTo(Inventaris::class, 'ram_id');
    }

    public function motherboard()
    {
        return $this->belongsTo(Inventaris::class, 'motherboard_id');
    }

    // Relasi ke tabel Hostname
    public function hostname()
    {
        return $this->belongsTo(Hostname::class, 'hostname_id');
    }

    // Relasi ke tabel PIC
    public function pic()
    {
        return $this->belongsTo(Pic::class, 'pic_id');
    }
}

