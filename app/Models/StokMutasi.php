<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokMutasi extends Model
{
    use HasFactory;

    protected $table = 'stok_mutasi';

    protected $fillable = [
        'kode_barang', 'tanggal', 'jenis_transaksi', 'no_referensi',
        'qty_masuk', 'qty_keluar', 'saldo_awal', 'saldo_akhir',
        'id_user', 'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'qty_masuk' => 'decimal:2',
        'qty_keluar' => 'decimal:2',
        'saldo_awal' => 'decimal:2',
        'saldo_akhir' => 'decimal:2',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    /**
     * Log a stock mutation and update the stock balance.
     * Must be run inside a database transaction with a lock on the product.
     *
     * @param string $kodeBarang
     * @param string $tanggal (Y-m-d)
     * @param string $jenisTransaksi
     * @param string $noReferensi
     * @param float $qtyMasuk
     * @param float $qtyKeluar
     * @param int|null $idUser
     * @param string|null $keterangan
     * @return StokMutasi
     */
    public static function log($kodeBarang, $tanggal, $jenisTransaksi, $noReferensi, $qtyMasuk, $qtyKeluar, $idUser = null, $keterangan = null)
    {
        $barang = Barang::lockForUpdate()->findOrFail($kodeBarang);
        $saldoAwal = (float)$barang->stok;

        // Perform stock adjustment on the barang table
        $adjustment = (float)$qtyMasuk - (float)$qtyKeluar;
        $barang->stok = $saldoAwal + $adjustment;
        $barang->save();

        $saldoAkhir = (float)$barang->stok;

        return self::create([
            'kode_barang' => $kodeBarang,
            'tanggal' => $tanggal,
            'jenis_transaksi' => $jenisTransaksi,
            'no_referensi' => $noReferensi,
            'qty_masuk' => $qtyMasuk,
            'qty_keluar' => $qtyKeluar,
            'saldo_awal' => $saldoAwal,
            'saldo_akhir' => $saldoAkhir,
            'id_user' => $idUser ?? auth()->id() ?? 1,
            'keterangan' => $keterangan,
        ]);
    }
}
