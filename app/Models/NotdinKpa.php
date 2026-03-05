<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class NotdinKpa extends Model
{
    protected $table = 'notdin_kpa';
    protected $fillable = ['nomor_agenda', 'bidang_id', 'tanggal', 'program', 'kegiatan', 'mata_anggaran', 'rencana_kegiatan', 'rencana_anggaran'];

    public function getTanggalAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function getRencanaAnggaranAttribute($value)
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    public function bidang()
    {
        return $this->belongsTo(RefBidang::class, 'bidang_id', 'id');
    }

    public function notdinPpkom()
    {
        return $this->hasMany(NotdinPpkom::class, 'notdin_kpa_id', 'id');
    }

    public function dokumenPengadaan()
    {
        return $this->hasMany(DokumenPengadaan::class, 'notdin_kpa_id', 'id');
    }
}
