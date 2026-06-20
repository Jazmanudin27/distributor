<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    use HasFactory;

    protected $table = 'penjualan_detail';

    protected $fillable = [
        'no_faktur', 'kode_barang', 'satuan_id', 'qty',
        'harga', 'subtotal', 'diskon1_persen', 'diskon2_persen',
        'diskon3_persen', 'diskon4_persen', 'total_diskon', 'total',
        'is_promo', 'harga_pokok',
    ];

    protected $casts = [
        'qty'           => 'decimal:2',
        'harga'         => 'decimal:2',
        'subtotal'      => 'decimal:2',
        'total_diskon'  => 'decimal:2',
        'total'         => 'decimal:2',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'no_faktur', 'no_faktur');
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
