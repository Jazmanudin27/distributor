<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiskonStrata extends Model
{
    use HasFactory;

    protected $table = 'diskon_strata';

    protected $fillable = [
        'nama_diskon',
        'tipe',
        'kategori_id',
        'merk_id',
        'kode_supplier',
        'berlaku_dari',
        'berlaku_sampai',
        'is_active',
    ];

    protected $casts = [
        'berlaku_dari' => 'datetime',
        'berlaku_sampai' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function merk()
    {
        return $this->belongsTo(Merk::class, 'merk_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'kode_supplier', 'kode_supplier');
    }

    public function barangs()
    {
        return $this->belongsToMany(Barang::class, 'diskon_strata_barang', 'diskon_strata_id', 'kode_barang');
    }

    public function details()
    {
        return $this->hasMany(DiskonStrataDetail::class, 'diskon_strata_id');
    }
}
