<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create table
        Schema::create('stok_mutasi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang', 50);
            $table->date('tanggal');
            $table->string('jenis_transaksi', 50); // e.g. Pembelian, Penjualan, Retur Penjualan, Retur Pembelian, Stok Opname, Batal Penjualan, Penyesuaian Awal
            $table->string('no_referensi', 50);
            $table->decimal('qty_masuk', 12, 2)->default(0);
            $table->decimal('qty_keluar', 12, 2)->default(0);
            $table->decimal('saldo_awal', 12, 2);
            $table->decimal('saldo_akhir', 12, 2);
            $table->unsignedBigInteger('id_user')->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('kode_barang');
            $table->index('tanggal');
            $table->index(['kode_barang', 'tanggal']);
        });

        // 2. Migrate historical data chronologically per product
        $barangs = DB::table('barang')->get();

        foreach ($barangs as $barang) {
            $kb = $barang->kode_barang;
            $movements = collect();

            // A. Pembelian
            $pembelians = DB::table('pembelian_detail')
                ->join('pembelian', 'pembelian_detail.no_faktur', '=', 'pembelian.no_faktur')
                ->leftJoin('barang_satuan', 'pembelian_detail.satuan_id', '=', 'barang_satuan.id')
                ->where('pembelian_detail.kode_barang', $kb)
                ->select(
                    'pembelian.tanggal',
                    'pembelian.no_faktur as no_ref',
                    'pembelian.id_user',
                    'pembelian_detail.qty',
                    'barang_satuan.isi',
                    'pembelian.created_at'
                )->get();

            foreach ($pembelians as $p) {
                $qty = (float)$p->qty * (float)($p->isi ?? 1);
                $movements->push([
                    'tanggal' => $p->tanggal,
                    'no_ref' => $p->no_ref,
                    'jenis' => 'Pembelian',
                    'qty_masuk' => $qty,
                    'qty_keluar' => 0,
                    'id_user' => $p->id_user,
                    'created_at' => $p->created_at,
                ]);
            }

            // B. Penjualan
            $penjualans = DB::table('penjualan_detail')
                ->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
                ->leftJoin('barang_satuan', 'penjualan_detail.satuan_id', '=', 'barang_satuan.id')
                ->where('penjualan_detail.kode_barang', $kb)
                ->select(
                    'penjualan.tanggal',
                    'penjualan.no_faktur as no_ref',
                    'penjualan.id_user',
                    'penjualan_detail.qty',
                    'barang_satuan.isi',
                    'penjualan.batal',
                    'penjualan.alasan_batal',
                    'penjualan.created_at',
                    'penjualan.updated_at'
                )->get();

            foreach ($penjualans as $s) {
                $qty = (float)$s->qty * (float)($s->isi ?? 1);
                // Sales deduction
                $movements->push([
                    'tanggal' => $s->tanggal,
                    'no_ref' => $s->no_ref,
                    'jenis' => 'Penjualan',
                    'qty_masuk' => 0,
                    'qty_keluar' => $qty,
                    'id_user' => $s->id_user,
                    'created_at' => $s->created_at,
                ]);

                // If canceled, add Batal Penjualan back
                if ($s->batal == 1) {
                    $movements->push([
                        'tanggal' => $s->tanggal,
                        'no_ref' => $s->no_ref,
                        'jenis' => 'Batal Penjualan',
                        'qty_masuk' => $qty,
                        'qty_keluar' => 0,
                        'id_user' => $s->id_user,
                        'created_at' => $s->updated_at ?? $s->created_at,
                        'keterangan' => 'Pembatalan: ' . ($s->alasan_batal ?? '-'),
                    ]);
                }
            }

            // C. Retur Penjualan (Customer returns) - only Bagus or NULL conditions affect stock
            $returJuals = DB::table('retur_penjualan_detail')
                ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
                ->leftJoin('barang_satuan', 'retur_penjualan_detail.id_satuan', '=', 'barang_satuan.id')
                ->where('retur_penjualan_detail.kode_barang', $kb)
                ->where(function($q) {
                    $q->where('retur_penjualan_detail.kondisi', 'Bagus')
                      ->orWhereNull('retur_penjualan_detail.kondisi');
                })
                ->select(
                    'retur_penjualan.tanggal',
                    'retur_penjualan.no_retur as no_ref',
                    'retur_penjualan.user_id as id_user',
                    'retur_penjualan_detail.qty',
                    'barang_satuan.isi',
                    'retur_penjualan.created_at'
                )->get();

            foreach ($returJuals as $rj) {
                $qty = (float)$rj->qty * (float)($rj->isi ?? 1);
                $movements->push([
                    'tanggal' => $rj->tanggal,
                    'no_ref' => $rj->no_ref,
                    'jenis' => 'Retur Penjualan',
                    'qty_masuk' => $qty,
                    'qty_keluar' => 0,
                    'id_user' => $rj->id_user,
                    'created_at' => $rj->created_at,
                ]);
            }

            // D. Retur Pembelian (Returns to Supplier)
            $returBelis = DB::table('retur_pembelian_detail')
                ->join('retur_pembelian', 'retur_pembelian_detail.no_retur', '=', 'retur_pembelian.no_retur')
                ->leftJoin('barang_satuan', 'retur_pembelian_detail.satuan_id', '=', 'barang_satuan.id')
                ->where('retur_pembelian_detail.kode_barang', $kb)
                ->select(
                    'retur_pembelian.tanggal',
                    'retur_pembelian.no_retur as no_ref',
                    'retur_pembelian.user_id as id_user',
                    'retur_pembelian_detail.qty',
                    'barang_satuan.isi',
                    'retur_pembelian.created_at'
                )->get();

            foreach ($returBelis as $rb) {
                $qty = (float)$rb->qty * (float)($rb->isi ?? 1);
                $movements->push([
                    'tanggal' => $rb->tanggal,
                    'no_ref' => $rb->no_ref,
                    'jenis' => 'Retur Pembelian',
                    'qty_masuk' => 0,
                    'qty_keluar' => $qty,
                    'id_user' => $rb->id_user,
                    'created_at' => $rb->created_at,
                ]);
            }

            // E. Stok Opname
            $opnames = DB::table('stok_opname_detail')
                ->join('stok_opname', 'stok_opname_detail.no_opname', '=', 'stok_opname.no_opname')
                ->where('stok_opname_detail.kode_barang', $kb)
                ->select(
                    'stok_opname.tanggal',
                    'stok_opname.no_opname as no_ref',
                    'stok_opname.user_id as id_user',
                    'stok_opname_detail.selisih',
                    'stok_opname.created_at'
                )->get();

            foreach ($opnames as $o) {
                $selisih = (float)$o->selisih;
                $movements->push([
                    'tanggal' => $o->tanggal,
                    'no_ref' => $o->no_ref,
                    'jenis' => 'Stok Opname',
                    'qty_masuk' => $selisih > 0 ? $selisih : 0,
                    'qty_keluar' => $selisih < 0 ? abs($selisih) : 0,
                    'id_user' => $o->id_user,
                    'created_at' => $o->created_at,
                ]);
            }

            // Sort movements chronologically
            $movements = $movements->sortBy(function($m) {
                return $m['tanggal'] . '_' . ($m['created_at'] ?? '0000-00-00 00:00:00') . '_' . $m['no_ref'];
            })->values();

            // Insert mutations and calculate running balance
            $running = 0;
            foreach ($movements as $m) {
                $masuk = $m['qty_masuk'];
                $keluar = $m['qty_keluar'];
                $awal = $running;
                $akhir = $running + $masuk - $keluar;

                DB::table('stok_mutasi')->insert([
                    'kode_barang' => $kb,
                    'tanggal' => $m['tanggal'],
                    'jenis_transaksi' => $m['jenis'],
                    'no_referensi' => $m['no_ref'],
                    'qty_masuk' => $masuk,
                    'qty_keluar' => $keluar,
                    'saldo_awal' => $awal,
                    'saldo_akhir' => $akhir,
                    'id_user' => (is_numeric($m['id_user']) && (int)$m['id_user'] > 0) ? (int)$m['id_user'] : 1,
                    'keterangan' => $m['keterangan'] ?? null,
                    'created_at' => $m['created_at'] ?? now(),
                    'updated_at' => $m['created_at'] ?? now(),
                ]);

                $running = $akhir;
            }

            // If the calculated running balance doesn't match the current stock, add a correction
            $currentStock = (float)$barang->stok;
            if ($running != $currentStock) {
                $diff = $currentStock - $running;
                $masuk = $diff > 0 ? $diff : 0;
                $keluar = $diff < 0 ? abs($diff) : 0;

                DB::table('stok_mutasi')->insert([
                    'kode_barang' => $kb,
                    'tanggal' => now()->toDateString(),
                    'jenis_transaksi' => 'Penyesuaian Awal',
                    'no_referensi' => 'ADJ-MIGRATE',
                    'qty_masuk' => $masuk,
                    'qty_keluar' => $keluar,
                    'saldo_awal' => $running,
                    'saldo_akhir' => $currentStock,
                    'id_user' => 1,
                    'keterangan' => 'Penyesuaian saldo awal migrasi tabel mutasi',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_mutasi');
    }
};
