<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokOpnameDetail extends Model
{
    use HasFactory;

    protected $table = 'stok_opname_detail';
    protected $primaryKey = 'id';

    protected $fillable = [
        'no_opname',
        'kode_barang',
        'stok_sistem',
        'stok_fisik',
        'selisih',
        'keterangan',
    ];

    public function stokOpname()
    {
        return $this->belongsTo(StokOpname::class, 'no_opname', 'no_opname');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }
}
