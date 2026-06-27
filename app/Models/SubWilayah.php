<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubWilayah extends Model
{
    use HasFactory;

    protected $table = 'sub_wilayah';
    protected $primaryKey = 'kode_wilayah';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = ['kode_wilayah', 'nama_wilayah'];
}
