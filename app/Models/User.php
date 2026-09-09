<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nik',
        'status',
        'jenis_sales',
        'jenis_barang',
        'is_kanvas',
        'kode_pelanggan',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function pelanggan()
    {
        return $this->belongsTo(\App\Models\Pelanggan::class, 'kode_pelanggan', 'kode_pelanggan');
    }

    /**
     * Scope query untuk mengambil semua personel sales (sales reguler dan SPV sales).
     */
    public function scopeSalesmen($query)
    {
        return $query->where(function ($q) {
            $q->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(role)'), ['sales', 'salesman', 'spv sales'])
              ->orWhereIn('role', ['sales', 'Sales', 'Salesman', 'salesman', 'spv sales', 'SPV Sales', 'spv_sales', 'SPV_Sales'])
              ->orWhereHas('roles', function ($rq) {
                  $rq->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(name)'), ['sales', 'salesman', 'spv sales'])
                    ->orWhereIn('name', ['sales', 'Sales', 'Salesman', 'salesman', 'spv sales', 'SPV Sales', 'spv_sales', 'SPV_Sales']);
              });
        });
    }
}
