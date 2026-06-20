<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPembelian extends Model
{
    use HasFactory;

    protected $table = 'retur_pembelian';
    protected $primaryKey = 'no_retur';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'no_retur',
        'tanggal',
        'jenis_retur',
        'kode_supplier',
        'no_faktur',
        'total',
        'keterangan',
        'user_id',
        'kondisi',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'kode_supplier', 'kode_supplier');
    }

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'no_faktur', 'no_faktur');
    }

    public function details()
    {
        return $this->hasMany(ReturPembelianDetail::class, 'no_retur', 'no_retur');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
