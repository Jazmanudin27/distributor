<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianDetail extends Model
{
    use HasFactory;

    protected $table = 'pembelian_detail';
    protected $primaryKey = 'id';

    protected $fillable = [
        'no_faktur', 'kode_barang', 'satuan', 'satuan_id',
        'qty', 'harga', 'diskon', 'total', 'subtotal'
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'no_faktur', 'no_faktur');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }

    public function barangSatuan()
    {
        return $this->belongsTo(BarangSatuan::class, 'satuan_id', 'id');
    }
}
