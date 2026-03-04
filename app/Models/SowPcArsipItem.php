<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SowPcArsipItem extends Model
{
    protected $fillable = [
        'sow_pc_arsip_id','case_id','psu_id','prosesor_id','ram_id','motherboard_id',
        'tanggal_penggunaan','tanggal_perbaikan','helpdesk','form','nomor_perbaikan',
        'hostname_id','divisi','pic_id','keterangan','foto','status'
    ];

    protected $casts = [
    'tanggal_penggunaan' => 'date',
    'tanggal_perbaikan' => 'date',
];


    public function arsip()
    {
        return $this->belongsTo(SowPcArsip::class);
    }

    // Relasi ke tabel master Inventaris
    public function case()
    {
        return $this->belongsTo(Inventaris::class, 'case_id');
    }

    public function psu()
    {
        return $this->belongsTo(Inventaris::class, 'psu_id');
    }

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
