<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';
    protected $primaryKey = 'kode_barang';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['kode_barang', 'nama_barang', 'kategori', 'merk', 'kode_supplier', 'keterangan', 'stok_min', 'stok', 'status', 'kode_item'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'kode_supplier', 'kode_supplier');
    }

    public function satuans()
    {
        return $this->hasMany(BarangSatuan::class, 'kode_barang', 'kode_barang')->orderBy('isi', 'desc');
    }

    public function formatStok($qty)
    {
        $qtyFloat = (float)$qty;
        $isNegative = $qtyFloat < 0;
        $absQty = round(abs($qtyFloat), 4);

        $satuans = $this->satuans;
        $breakdowns = [];
        if ($satuans && $satuans->count() > 0) {
            $sorted = $satuans->sortByDesc('isi');
            $remaining = $absQty;
            $count = $sorted->count();
            $i = 0;
            foreach ($sorted as $sat) {
                $i++;
                $factor = (float)($sat->isi ?: 1);
                if ($i === $count) {
                    $unitQty = round($remaining / $factor, 4);
                    if ($unitQty > 0) {
                        $breakdowns[] = (float)$unitQty . ' ' . $sat->satuan;
                    }
                } else {
                    $unitQty = floor(round($remaining / $factor, 8));
                    if ($unitQty > 0) {
                        $breakdowns[] = $unitQty . ' ' . $sat->satuan;
                        $remaining = round($remaining - ($unitQty * $factor), 4);
                    }
                }
            }
        } else {
            $breakdowns[] = (float)$absQty . ' PCS';
        }
        $formatted = implode(' ', $breakdowns) ?: '0 PCS';
        return $isNegative ? '-' . $formatted : $formatted;
    }

}
