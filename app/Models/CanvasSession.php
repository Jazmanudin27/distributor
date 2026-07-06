<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CanvasSession extends Model
{
    use HasFactory;

    protected $table = 'canvas_sessions';

    protected $fillable = [
        'no_canvas',
        'kode_sales',
        'tanggal',
        'status',
        'keterangan',
        'approved_at',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'approved_at' => 'datetime',
    ];

    public function sales()
    {
        return $this->belongsTo(User::class, 'kode_sales', 'nik');
    }

    public function details()
    {
        return $this->hasMany(CanvasSessionDetail::class, 'canvas_session_id', 'id');
    }
}
