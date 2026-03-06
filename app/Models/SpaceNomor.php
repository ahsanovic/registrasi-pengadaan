<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SpaceNomor extends Model
{
    protected $table = 'space_nomor';
    protected $fillable = ['tahun', 'tanggal', 'nomor_agenda', 'used_at'];

    public function getTanggalAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }
}
