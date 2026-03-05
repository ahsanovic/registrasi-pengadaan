<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaceNomor extends Model
{
    protected $table = 'space_nomor';
    protected $fillable = ['tahun', 'tanggal', 'nomor_agenda', 'used_at'];
}
