<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanCheckin extends Model
{
    use HasFactory;

    protected $table = 'penjualan_checkin';

    protected $fillable = [
        'kode_sales',
        'kode_pelanggan',
        'tanggal',
        'checkin',
        'checkout',
        'latitude',
        'longitude',
        'catatan'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'checkin' => 'datetime',
        'checkout' => 'datetime',
    ];

    public function sales()
    {
        return $this->belongsTo(User::class, 'kode_sales', 'nik');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'kode_pelanggan', 'kode_pelanggan');
    }
}
