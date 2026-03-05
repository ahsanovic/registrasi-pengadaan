<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefJenisDokumen extends Model
{
    protected $table = 'ref_jenis_dokumen';
    protected $fillable = ['jenis_dokumen'];

    public function dokumenPengadaan()
    {
        return $this->hasMany(DokumenPengadaan::class, 'jenis_dokumen_id', 'id');
    }
}
