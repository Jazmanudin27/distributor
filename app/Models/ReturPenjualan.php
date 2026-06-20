<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPenjualan extends Model
{
    use HasFactory;

    protected $table = 'retur_penjualan';
    protected $primaryKey = 'no_retur';
    protected $keyType = 'string';
    public $incrementing = false;

    public function getRouteKeyName()
    {
        return 'no_retur';
    }

    protected $fillable = [
        'no_retur',
        'tanggal',
        'jenis_retur',
        'kode_pelanggan',
        'kode_sales',
        'no_faktur',
        'total',
        'keterangan',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total'   => 'decimal:2',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'kode_pelanggan', 'kode_pelanggan');
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'no_faktur', 'no_faktur');
    }

    public function details()
    {
        return $this->hasMany(ReturPenjualanDetail::class, 'no_retur', 'no_retur');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function sales()
    {
        return $this->belongsTo(User::class, 'kode_sales', 'nik');
    }
}
