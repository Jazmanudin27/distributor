<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPenjualanDetail extends Model
{
    use HasFactory;

    protected $table = 'retur_penjualan_detail';

    protected $fillable = [
        'no_retur',
        'kode_barang',
        'qty',
        'harga_retur',
        'subtotal_retur',
        'diskon1_persen',
        'diskon2_persen',
        'diskon3_persen',
        'id_satuan',
        'total_diskon_rupiah',
    ];

    protected $casts = [
        'qty'                 => 'decimal:2',
        'harga_retur'         => 'decimal:2',
        'subtotal_retur'      => 'decimal:2',
        'diskon1_persen'      => 'decimal:5',
        'diskon2_persen'      => 'decimal:5',
        'diskon3_persen'      => 'decimal:5',
        'total_diskon_rupiah' => 'decimal:2',
    ];

    public function returPenjualan()
    {
        return $this->belongsTo(ReturPenjualan::class, 'no_retur', 'no_retur');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }

    public function barangSatuan()
    {
        return $this->belongsTo(BarangSatuan::class, 'id_satuan', 'id');
    }
}
