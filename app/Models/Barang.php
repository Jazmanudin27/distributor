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
        $absQty = abs($qtyFloat);

        $satuans = $this->satuans;
        $breakdowns = [];
        if ($satuans && $satuans->count() > 0) {
            $sorted = $satuans->sortByDesc('isi');
            $remaining = $absQty;
            foreach ($sorted as $sat) {
                $factor = (float)($sat->isi ?: 1);
                $unitQty = floor($remaining / $factor);
                if ($unitQty > 0) {
                    $breakdowns[] = $unitQty . ' ' . $sat->satuan;
                    $remaining = fmod($remaining, $factor);
                }
            }
            if ($remaining > 0 && $sorted->count() > 0) {
                $last = $sorted->last();
                $breakdowns[] = $remaining . ' ' . $last->satuan;
            }
        } else {
            $breakdowns[] = $absQty . ' PCS';
        }
        $formatted = implode(', ', $breakdowns) ?: '0 PCS';
        return $isNegative ? '-' . $formatted : $formatted;
    }

}
