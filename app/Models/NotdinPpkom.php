<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\NotdinKpa;
use App\Models\DokumenPengadaan;
use Carbon\Carbon;

class NotdinPpkom extends Model
{
    protected $table = 'notdin_ppkom';
    protected $fillable = ['notdin_kpa_id', 'nomor_agenda', 'tanggal', 'penyedia', 'alamat', 'npwp'];

    public function notdinKpa()
    {
        return $this->belongsTo(NotdinKpa::class, 'notdin_kpa_id', 'id');
    }

    public function dokumenPengadaan()
    {
        return $this->hasMany(DokumenPengadaan::class, 'notdin_ppkom_id', 'id');
    }

    public function getTanggalAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }
}
