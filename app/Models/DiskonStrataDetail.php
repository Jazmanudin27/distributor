<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiskonStrataDetail extends Model
{
    use HasFactory;

    protected $table = 'diskon_strata_detail';

    protected $fillable = [
        'diskon_strata_id',
        'satuan_id',
        'min_qty',
        'max_qty',
        'min_nominal',
        'max_nominal',
        'tipe_nilai',
        'dis1',
        'dis2',
    ];

    protected $casts = [
        'satuan_id' => 'integer',
        'min_qty' => 'integer',
        'max_qty' => 'integer',
        'min_nominal' => 'decimal:2',
        'max_nominal' => 'decimal:2',
        'dis1' => 'decimal:2',
        'dis2' => 'decimal:2',
    ];

    public function header()
    {
        return $this->belongsTo(DiskonStrata::class, 'diskon_strata_id');
    }

    public function satuan()
    {
        return $this->belongsTo(BarangSatuan::class, 'satuan_id');
    }
}
