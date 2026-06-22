<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';
    protected $primaryKey = 'kode_pelanggan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'kode_pelanggan', 'nama_pelanggan', 'alamat_pelanggan', 'alamat_toko',
        'tanggal_register', 'no_hp_pelanggan', 'kepemilikan', 'omset_toko',
        'limit_pelanggan', 'hari', 'kunjungan', 'metode_bayar',
        'latitude', 'longitude', 'status', 'foto', 'foto_ktp',
        'kode_wilayah', 'email', 'ljt', 'max_faktur',
        'kode_toko', 'sub_wilayah', 'jenis_pelanggan', 'approve'
    ];

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'kode_wilayah', 'kode_wilayah');
    }

    public function subWilayah()
    {
        return $this->belongsTo(SubWilayah::class, 'sub_wilayah', 'kode_wilayah');
    }

    public function getOutstandingPiutang($excludeNoFaktur = null)
    {
        $query = \App\Models\Penjualan::where('kode_pelanggan', $this->kode_pelanggan)
            ->where('batal', 0);
            
        if ($excludeNoFaktur) {
            $query->where('no_faktur', '!=', $excludeNoFaktur);
        }
        
        return (float) $query->selectRaw("SUM(CASE WHEN (grand_total - (COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran WHERE penjualan_pembayaran.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) + COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_transfer WHERE penjualan_pembayaran_transfer.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) + COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_giro WHERE penjualan_pembayaran_giro.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) + COALESCE((SELECT SUM(total) FROM retur_penjualan WHERE retur_penjualan.no_faktur = penjualan.no_faktur), 0))) >= 1 THEN (grand_total - (COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran WHERE penjualan_pembayaran.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) + COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_transfer WHERE penjualan_pembayaran_transfer.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) + COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_giro WHERE penjualan_pembayaran_giro.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) + COALESCE((SELECT SUM(total) FROM retur_penjualan WHERE retur_penjualan.no_faktur = penjualan.no_faktur), 0))) ELSE 0 END) as outstanding")
            ->value('outstanding') ?: 0.0;
    }

    public function getSisaLimitKredit($excludeNoFaktur = null)
    {
        $outstanding = $this->getOutstandingPiutang($excludeNoFaktur);
        return max(0, $this->limit_pelanggan - $outstanding);
    }

    public function getOverdueInvoices($excludeNoFaktur = null)
    {
        $query = \App\Models\Penjualan::where('kode_pelanggan', $this->kode_pelanggan)
            ->whereIn('jenis_transaksi', ['K', 'Kredit'])
            ->where('batal', 0)
            ->whereRaw('DATE_ADD(tanggal, INTERVAL COALESCE(?, 30) DAY) < ?', [$this->ljt ?: 30, now()->toDateString()])
            ->whereRaw("(grand_total - (COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran WHERE penjualan_pembayaran.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) + COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_transfer WHERE penjualan_pembayaran_transfer.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) + COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_giro WHERE penjualan_pembayaran_giro.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) + COALESCE((SELECT SUM(total) FROM retur_penjualan WHERE retur_penjualan.no_faktur = penjualan.no_faktur), 0))) >= 1");
            
        if ($excludeNoFaktur) {
            $query->where('no_faktur', '!=', $excludeNoFaktur);
        }
        
        return $query->get();
    }

    public function hasOverdueInvoices($excludeNoFaktur = null)
    {
        // Pelanggan dengan jenis_pelanggan '1' mendapat dispensasi dan bisa bertransaksi meski ada tagihan overdue
        if ($this->jenis_pelanggan == '1') {
            return false;
        }
        
        $query = \App\Models\Penjualan::where('kode_pelanggan', $this->kode_pelanggan)
            ->whereIn('jenis_transaksi', ['K', 'Kredit'])
            ->where('batal', 0)
            ->whereRaw('DATE_ADD(tanggal, INTERVAL COALESCE(?, 30) DAY) < ?', [$this->ljt ?: 30, now()->toDateString()])
            ->whereRaw("(grand_total - (COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran WHERE penjualan_pembayaran.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) + COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_transfer WHERE penjualan_pembayaran_transfer.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) + COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_giro WHERE penjualan_pembayaran_giro.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) + COALESCE((SELECT SUM(total) FROM retur_penjualan WHERE retur_penjualan.no_faktur = penjualan.no_faktur), 0))) >= 1");
            
        if ($excludeNoFaktur) {
            $query->where('no_faktur', '!=', $excludeNoFaktur);
        }
        
        return $query->exists();
    }
}
