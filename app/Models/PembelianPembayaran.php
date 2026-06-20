<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianPembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembelian_pembayaran';
    protected $primaryKey = 'id';

    protected $fillable = [
        'no_bukti', 'tanggal', 'no_faktur', 'jenis_bayar', 'jumlah', 'keterangan'
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'no_faktur', 'no_faktur');
    }
}
