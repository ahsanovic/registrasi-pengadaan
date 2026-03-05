<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\NotdinKpa;

class RefBidang extends Model
{
    protected $table = 'ref_bidang';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['id', 'parent_id', 'nama'];

    public function notdinKpa()
    {
        return $this->hasMany(NotdinKpa::class, 'bidang_id', 'id');
    }
}
