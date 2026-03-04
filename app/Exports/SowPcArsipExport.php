<?php

namespace App\Exports;

use App\Models\SowPcArsip;
use Maatwebsite\Excel\Concerns\FromCollection;

class SowPcArsipExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return SowPcArsip::all();
    }
}
