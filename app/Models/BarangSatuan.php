<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangSatuan extends Model
{
    use HasFactory;

    protected $table = 'barang_satuan';
    protected $primaryKey = 'id';
    

    protected $fillable = ['kode_barang', 'satuan', 'isi', 'harga_pokok', 'harga_jual'];
}
