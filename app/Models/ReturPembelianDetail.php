<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPembelianDetail extends Model
{
    use HasFactory;

    protected $table = 'retur_pembelian_detail';
    protected $primaryKey = 'id';

    protected $fillable = [
        'no_retur',
        'kode_barang',
        'satuan_id',
        'qty',
        'harga_retur',
        'subtotal_retur',
        'diskon1_persen',
        'diskon2_persen',
        'diskon3_persen',
        'diskon4_persen',
    ];

    public function returPembelian()
    {
        return $this->belongsTo(ReturPembelian::class, 'no_retur', 'no_retur');
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
