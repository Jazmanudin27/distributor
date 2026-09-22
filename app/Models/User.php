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
        'spv_type',
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
     * Relasi ke sales-salesman yang ditugaskan di bawah pengawasan SPV Sales 2
     */
    public function assignedSalesmen()
    {
        return $this->belongsToMany(User::class, 'spv_salesman', 'spv_id', 'sales_id');
    }

    /**
     * Mendapatkan array user ID dari sales-salesman binaan SPV
     */
    public function getAssignedSalesIdsAttribute(): array
    {
        return $this->assignedSalesmen()->pluck('users.id')->toArray();
    }

    /**
     * Mendapatkan array NIK dari sales-salesman binaan SPV
     */
    public function getAssignedSalesNiksAttribute(): array
    {
        return $this->assignedSalesmen()->pluck('users.nik')->filter()->toArray();
    }

    /**
     * Mendapatkan tipe SPV (1 = Full/Semua, 2 = Limited/Beberapa Sales)
     */
    public function getEffectiveSpvTypeAttribute(): string
    {
        $r = strtolower($this->role ?? '');
        if ($r === 'spv sales 2') {
            return '2';
        }
        if ($r === 'spv sales 1') {
            return '1';
        }
        return (string) ($this->spv_type ?? '1');
    }

    /**
     * Cek apakah user adalah SPV Sales 2 (Approval terbatas hanya sales binaan)
     */
    public function isSpv2(): bool
    {
        $r = strtolower($this->role ?? '');
        $isSpvRole = in_array($r, ['spv sales', 'spv_sales', 'spv sales 1', 'spv sales 2']);
        return $isSpvRole && $this->effective_spv_type === '2';
    }

    /**
     * Cek apakah user adalah SPV Sales 1 (Akses penuh ke semua sales)
     */
    public function isSpv1(): bool
    {
        $r = strtolower($this->role ?? '');
        $isSpvRole = in_array($r, ['spv sales', 'spv_sales', 'spv sales 1', 'spv sales 2']);
        return $isSpvRole && $this->effective_spv_type !== '2';
    }

    /**
     * Scope query untuk mengambil semua personel sales (sales reguler dan SPV sales).
     */
    public function scopeSalesmen($query)
    {
        return $query->where(function ($q) {
            $q->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(role)'), ['sales', 'salesman', 'spv sales', 'spv sales 1', 'spv sales 2'])
              ->orWhereIn('role', ['sales', 'Sales', 'Salesman', 'salesman', 'spv sales', 'SPV Sales', 'spv_sales', 'SPV_Sales', 'SPV Sales 1', 'SPV Sales 2'])
              ->orWhereHas('roles', function ($rq) {
                  $rq->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(name)'), ['sales', 'salesman', 'spv sales', 'spv sales 1', 'spv sales 2'])
                    ->orWhereIn('name', ['sales', 'Sales', 'Salesman', 'salesman', 'spv sales', 'SPV Sales', 'spv_sales', 'SPV_Sales', 'SPV Sales 1', 'SPV Sales 2']);
              });
        });
    }
}
