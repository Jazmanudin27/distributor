<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';
    protected $primaryKey = 'no_faktur';
    protected $keyType = 'string';
    public $incrementing = false;

    public function getRouteKeyName()
    {
        return 'no_faktur';
    }

    protected $fillable = [
        'no_faktur',
        'tanggal',
        'kode_pelanggan',
        'kode_sales',
        'jenis_transaksi',
        'jenis_bayar',
        'total',
        'diskon',
        'grand_total',
        'keterangan',
        'id_user',
        'batal',
        'alasan_batal',
        'tanggal_kirim',
        'cetak',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_kirim' => 'date',
        'grand_total' => 'decimal:2',
        'total' => 'decimal:2',
        'diskon' => 'decimal:2',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'kode_pelanggan', 'kode_pelanggan');
    }

    public function details()
    {
        return $this->hasMany(PenjualanDetail::class, 'no_faktur', 'no_faktur');
    }

    public function pembayarans()
    {
        return $this->hasMany(PenjualanPembayaran::class, 'no_faktur', 'no_faktur');
    }

    public function getAllPembayarans()
    {
        $cash = $this->pembayarans->where('jenis_bayar', '!=', 'Retur')->map(function ($item) {
            $item->jenis_pembayaran_label = 'Cash';
            $item->source_table = 'cash';
            return $item;
        });

        $transfers = \Illuminate\Support\Facades\DB::table('penjualan_pembayaran_transfer')
            ->where('no_faktur', $this->no_faktur)
            ->get()
            ->map(function ($item) {
                // Map fields to match PenjualanPembayaran attributes
                $obj = new \stdClass();
                $obj->id = $item->kode_transfer;
                $obj->no_bukti = $item->kode_transfer;
                $obj->tanggal = \Carbon\Carbon::parse($item->tanggal);
                $obj->no_faktur = $item->no_faktur;
                $obj->kode_pelanggan = $item->kode_pelanggan;
                $obj->kode_sales = $item->kode_sales;
                $obj->jenis_bayar = $item->jenis_bayar ?? 'Transfer';
                $obj->jumlah = $item->jumlah;
                $obj->keterangan = $item->keterangan;
                $obj->id_user = $item->id_user;
                $obj->status = $item->status;
                $obj->jenis_pembayaran_label = 'Transfer';
                $obj->source_table = 'transfer';
                return $obj;
            });

        $giros = \Illuminate\Support\Facades\DB::table('penjualan_pembayaran_giro')
            ->where('no_faktur', $this->no_faktur)
            ->get()
            ->map(function ($item) {
                // Map fields to match PenjualanPembayaran attributes
                $obj = new \stdClass();
                $obj->id = $item->kode_giro;
                $obj->no_bukti = $item->kode_giro;
                $obj->tanggal = \Carbon\Carbon::parse($item->tanggal);
                $obj->no_faktur = $item->no_faktur;
                $obj->kode_pelanggan = $item->kode_pelanggan;
                $obj->kode_sales = $item->kode_sales;
                $obj->jenis_bayar = $item->jenis_bayar ?? 'Giro';
                $obj->jumlah = $item->jumlah;
                $obj->keterangan = $item->keterangan;
                $obj->id_user = $item->id_user;
                $obj->status = $item->status;
                $obj->jenis_pembayaran_label = 'Giro';
                $obj->source_table = 'giro';
                return $obj;
            });

        return $cash->concat($transfers)->concat($giros)->sortByDesc('tanggal');
    }

    public function getApprovedPembayaranTotal()
    {
        $cashTotal = $this->pembayarans->where('status', 'disetujui')->sum('jumlah');

        $transferTotal = \Illuminate\Support\Facades\DB::table('penjualan_pembayaran_transfer')
            ->where('no_faktur', $this->no_faktur)
            ->where('status', 'disetujui')
            ->sum('jumlah');

        $giroTotal = \Illuminate\Support\Facades\DB::table('penjualan_pembayaran_giro')
            ->where('no_faktur', $this->no_faktur)
            ->where('status', 'disetujui')
            ->sum('jumlah');

        return (float) ($cashTotal + $transferTotal + $giroTotal);
    }

    public function getTotalRetur()
    {
        return (float) \Illuminate\Support\Facades\DB::table('retur_penjualan')
            ->where('no_faktur', $this->no_faktur)
            ->sum('total');
    }

    public function getSisaPiutang()
    {
        $totalBayar = $this->getApprovedPembayaranTotal();
        $totalRetur = $this->getTotalRetur();
        $sisa = $this->grand_total - $totalBayar - $totalRetur;
        return $sisa < 1 ? 0.0 : (float) $sisa;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function sales()
    {
        return $this->belongsTo(User::class, 'kode_sales', 'nik');
    }
}
