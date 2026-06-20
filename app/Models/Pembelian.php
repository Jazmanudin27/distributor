<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    protected $table = 'pembelian';
    protected $primaryKey = 'no_faktur';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'no_faktur', 'no_po', 'tanggal', 'jatuh_tempo', 'kode_supplier',
        'jenis_transaksi', 'potongan', 'pajak', 'biaya_lain',
        'potongan_claim', 'grand_total', 'keterangan', 'id_user', 'tanggal_approve'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'kode_supplier', 'kode_supplier');
    }

    public function details()
    {
        return $this->hasMany(PembelianDetail::class, 'no_faktur', 'no_faktur');
    }

    public function pembayarans()
    {
        return $this->hasMany(PembelianPembayaran::class, 'no_faktur', 'no_faktur');
    }
}
