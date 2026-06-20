<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'supplier';
    protected $primaryKey = 'kode_supplier';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['kode_supplier', 'nama_supplier', 'alamat', 'no_hp', 'email', 'status'];
}
