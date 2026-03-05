<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TanggalLibur extends Model
{
    protected $table = 'tanggal_libur';
    protected $fillable = ['tanggal', 'keterangan'];

    public function getTanggalAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }
}
