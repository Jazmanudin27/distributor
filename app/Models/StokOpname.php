<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokOpname extends Model
{
    use HasFactory;

    protected $table = 'stok_opname';
    protected $primaryKey = 'no_opname';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'no_opname',
        'tanggal',
        'keterangan',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(StokOpnameDetail::class, 'no_opname', 'no_opname');
    }
}
