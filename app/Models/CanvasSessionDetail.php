<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CanvasSessionDetail extends Model
{
    use HasFactory;

    protected $table = 'canvas_session_details';

    protected $fillable = [
        'canvas_session_id',
        'kode_barang',
        'satuan_id',
        'qty_ambil',
        'diskon_persen',
        'qty_terjual',
        'qty_kembali',
        'selisih'
    ];

    protected $casts = [
        'qty_ambil' => 'decimal:2',
        'diskon_persen' => 'decimal:2',
        'qty_terjual' => 'decimal:2',
        'qty_kembali' => 'decimal:2',
        'selisih' => 'decimal:2',
    ];

    public function session()
    {
        return $this->belongsTo(CanvasSession::class, 'canvas_session_id', 'id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }

    public function barangSatuan()
    {
        return $this->belongsTo(BarangSatuan::class, 'satuan_id', 'id');
    }
}
