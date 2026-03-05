<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\RefJenisDokumen;
use Carbon\Carbon;

class DokumenPengadaan extends Model
{
    protected $table = 'dokumen_pengadaan';
    protected $fillable = ['notdin_ppkom_id', 'nomor_agenda', 'tanggal', 'jenis_dokumen_id'];

    public function jenisDokumen()
    {
        return $this->belongsTo(RefJenisDokumen::class, 'jenis_dokumen_id', 'id');
    }

    public function notdinPpkom()
    {
        return $this->belongsTo(NotdinPpkom::class, 'notdin_ppkom_id', 'id');
    }

    public function getTanggalAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }
}
