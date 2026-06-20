<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanPembayaran extends Model
{
    use HasFactory;

    protected $table = 'penjualan_pembayaran';

    protected $fillable = [
        'no_bukti', 'tanggal', 'no_faktur', 'kode_pelanggan',
        'kode_sales', 'jenis_bayar', 'jumlah', 'keterangan',
        'id_user', 'jumlah_setor', 'tanggal_diterima', 'status',
    ];

    protected $casts = [
        'tanggal'          => 'date',
        'tanggal_diterima' => 'date',
        'jumlah'           => 'decimal:2',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'no_faktur', 'no_faktur');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'kode_pelanggan', 'kode_pelanggan');
    }
}
