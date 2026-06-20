<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeuanganMutasi extends Model
{
    use HasFactory;

    protected $table = 'keuangan_mutasi';

    protected $fillable = [
        'tanggal',
        'keterangan',
        'tipe',
        'jumlah',
        'kode_bank',
        'id_user',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'kode_bank', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
