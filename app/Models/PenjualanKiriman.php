<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanKiriman extends Model
{
    use HasFactory;

    protected $table = 'penjualan_kiriman';

    protected $fillable = [
        'tanggal',
        'kode_wilayah',
        'no_faktur',
        'keterangan',
        'kirimanke',
        'driver_name',
        'no_kendaraan',
        'status',
        'nama_penerima',
        'foto_penerima',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'kode_wilayah', 'kode_wilayah');
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'no_faktur', 'no_faktur');
    }
}
