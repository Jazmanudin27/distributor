<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Supplier;
use App\Models\Kategori;
use App\Models\Merk;
use App\Models\Penjualan;
use App\Models\ReturPenjualan;
use App\Models\Pembelian;
use App\Models\ReturPembelian;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanStokController extends Controller
{
    public function laporanStok(Request $request)
    {
        $this->authorizeReport('stok');

        $barangsList = Barang::where('status', 1)->orderBy('nama_barang', 'asc')->get();
        $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get();
        $merks = Merk::orderBy('nama_merk', 'asc')->get();
        $suppliers = Supplier::orderBy('nama_supplier', 'asc')->get();

        $jenis_laporan = $request->input('jenis_laporan', 'rekap');
        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $kode_barang = $request->input('kode_barang');
        $kategori = $request->input('kategori');
        $merk = $request->input('merk');
        $kode_supplier = $request->input('kode_supplier');
        $search = $request->input('search');
        $tampilkan_stok_kosong = $request->input('tampilkan_stok_kosong') == '1';

        $isCetak = $request->is('*/cetak');
        $isExcel = $request->is('*/excel');
        $isPrintOrExcel = $isCetak || $isExcel;

        $view = $isPrintOrExcel ? 'laporan.stok.cetak' : 'laporan.stok.index';

        if ($jenis_laporan === 'rekap') {
            $items = collect();
            if ($isPrintOrExcel) {
                $query = Barang::with(['satuans', 'supplier']);
                
                if ($kategori) {
                    $query->where('kategori', $kategori);
                }
                if ($merk) {
                    $query->where('merk', $merk);
                }
                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('kode_barang', 'like', "%$search%")
                          ->orWhere('nama_barang', 'like', "%$search%");
                    });
                }

                if (!$tampilkan_stok_kosong) {
                    $query->where('stok', '>', 0);
                }

                $items = $query->orderBy('nama_barang', 'asc')->get();
            }
            
            if ($isExcel) {
                $filename = 'laporan_stok_rekap_' . date('Ymd_His') . '.xls';
                return response(view($view, compact(
                    'barangsList', 'kategoris', 'merks', 'suppliers', 'items', 
                    'jenis_laporan', 'kategori', 'merk', 'kode_supplier', 'tanggal_mulai', 'tanggal_akhir', 'search', 'isExcel', 'tampilkan_stok_kosong'
                )))
                ->header('Content-Type', 'application/vnd-ms-excel')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
            }

            return view($view, compact(
                'barangsList', 'kategoris', 'merks', 'suppliers', 'items', 
                'jenis_laporan', 'kategori', 'merk', 'kode_supplier', 'tanggal_mulai', 'tanggal_akhir', 'search', 'tampilkan_stok_kosong'
            ));
        } elseif ($jenis_laporan === 'rekap_persediaan') {
            $view = $isPrintOrExcel ? 'laporan.stok.persediaan' : 'laporan.stok.index';
            $items = collect();

            if ($isPrintOrExcel) {
                $query = Barang::where('status', 1)->with(['satuans', 'supplier']);
                
                if ($kode_supplier) {
                    $query->where('kode_supplier', $kode_supplier);
                }
                if ($kode_barang) {
                    $query->where('kode_barang', $kode_barang);
                }
                if ($kategori) {
                    $query->where('kategori', $kategori);
                }
                if ($merk) {
                    $query->where('merk', $merk);
                }
                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('kode_barang', 'like', "%$search%")
                          ->orWhere('nama_barang', 'like', "%$search%")
                          ->orWhere('kode_item', 'like', "%$search%");
                    });
                }

                $barangs = $query->orderBy('nama_barang', 'asc')->get();
                $barangIds = $barangs->pluck('kode_barang')->toArray();

                // 1. Get latest mutation BEFORE tanggal_mulai for each product
                $lastMutationsBefore = DB::table('stok_mutasi')
                    ->whereIn('kode_barang', $barangIds)
                    ->where('tanggal', '<', $tanggal_mulai)
                    ->whereIn('id', function($q) use ($tanggal_mulai, $barangIds) {
                        $q->selectRaw('MAX(id)')
                          ->from('stok_mutasi')
                          ->whereIn('kode_barang', $barangIds)
                          ->where('tanggal', '<', $tanggal_mulai)
                          ->groupBy('kode_barang');
                    })
                    ->pluck('saldo_akhir', 'kode_barang');

                // 2. Get all mutations in period ordered by date & id
                $rawMutationsAll = DB::table('stok_mutasi')
                    ->whereIn('kode_barang', $barangIds)
                    ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->orderBy('tanggal', 'asc')
                    ->orderBy('id', 'asc')
                    ->get()
                    ->groupBy('kode_barang');

                foreach ($barangs as $b) {
                    $kb = $b->kode_barang;
                    $baseSatuan = $b->satuans->sortBy('isi')->first();
                    $baseSatuanName = $baseSatuan ? $baseSatuan->satuan : 'PCS';
                    $hargaPokok = $baseSatuan ? (float)$baseSatuan->harga_pokok : 0;
                    $hargaJual = $baseSatuan ? (float)$baseSatuan->harga_jual : 0;

                    $stokAwal = (float)($lastMutationsBefore->get($kb) ?? 0);

                    $rawMutationsItem = $rawMutationsAll->get($kb) ?? collect();

                    $pembelianPeriod = 0;
                    $returJualPeriod = 0;
                    $batalSalesPeriod = 0;
                    $opnameMasukPeriod = 0;
                    $penjualanPeriod = 0;
                    $returBeliPeriod = 0;
                    $opnameKeluarPeriod = 0;

                    $running = $stokAwal;
                    foreach ($rawMutationsItem as $m) {
                        $qtyMasuk = (float)$m->qty_masuk;
                        $qtyKeluar = (float)$m->qty_keluar;

                        if ($m->jenis_transaksi === 'Pembelian') {
                            $pembelianPeriod += $qtyMasuk;
                        } elseif ($m->jenis_transaksi === 'Retur Penjualan') {
                            $returJualPeriod += $qtyMasuk;
                        } elseif (in_array($m->jenis_transaksi, ['Batal Penjualan', 'Batal Penjualan (Edit)', 'Batal Jual'])) {
                            $batalSalesPeriod += $qtyMasuk;
                        } elseif ($m->jenis_transaksi === 'Penjualan') {
                            $penjualanPeriod += $qtyKeluar;
                        } elseif ($m->jenis_transaksi === 'Retur Pembelian') {
                            $returBeliPeriod += $qtyKeluar;
                        } else {
                            if ($qtyMasuk > 0) $opnameMasukPeriod += $qtyMasuk;
                            if ($qtyKeluar > 0) $opnameKeluarPeriod += $qtyKeluar;
                        }

                        if (in_array($m->jenis_transaksi, ['Stok Opname', 'Batal Stok Opname', 'Batal Stok Opname (Edit)']) && isset($m->saldo_akhir)) {
                            $running = (float)$m->saldo_akhir;
                        } else {
                            $running = $running + $qtyMasuk - $qtyKeluar;
                        }
                    }

                    $stokAkhir = $running;

                    // Add computed properties
                    $items->push([
                        'barang'              => $b,
                        'kode_barang'         => $b->kode_barang,
                        'kode_item'           => $b->kode_item,
                        'nama_barang'         => $b->nama_barang,
                        'satuan'              => $baseSatuanName,
                        'jenis'               => $b->jenis,
                        'kategori'            => $b->kategori,
                        'merk'                => $b->merk,
                        'stok_awal'           => $stokAwal,
                        // PENERIMAAN
                        'pembelian'           => $pembelianPeriod,
                        'retur_jual'          => $returJualPeriod,
                        'batal_jual'          => $batalSalesPeriod,
                        'penyesuaian_masuk'   => $opnameMasukPeriod,
                        // PENGELUARAN
                        'penjualan'           => $penjualanPeriod,
                        'retur_beli'          => $returBeliPeriod,
                        'penyesuaian_keluar'  => $opnameKeluarPeriod,
                        'stok_akhir'          => $stokAkhir,
                        'harga_pokok'         => $hargaPokok,
                        'harga_jual'          => $hargaJual,
                        'total_pokok'         => $stokAkhir * $hargaPokok,
                        'total_jual'          => $stokAkhir * $hargaJual,
                        'margin'              => $hargaJual - $hargaPokok,
                        'total_margin'        => $stokAkhir * ($hargaJual - $hargaPokok),
                    ]);
                }

                if (!$tampilkan_stok_kosong) {
                    $items = $items->filter(function($item) {
                        return $item['stok_akhir'] > 0;
                    });
                }
            }

            if ($isExcel) {
                $filename = 'laporan_persediaan_' . date('Ymd_His') . '.xls';
                return response(view($view, compact(
                    'barangsList', 'kategoris', 'merks', 'suppliers', 'items', 
                    'jenis_laporan', 'kode_barang', 'kategori', 'merk', 'kode_supplier', 'tanggal_mulai', 'tanggal_akhir', 'search', 'isExcel', 'tampilkan_stok_kosong'
                )))
                ->header('Content-Type', 'application/vnd-ms-excel')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
            }

            return view($view, compact(
                'barangsList', 'kategoris', 'merks', 'suppliers', 'items', 
                'jenis_laporan', 'kode_barang', 'kategori', 'merk', 'kode_supplier', 'tanggal_mulai', 'tanggal_akhir', 'search', 'tampilkan_stok_kosong'
            ));
        } elseif ($jenis_laporan === 'margin') {
            $view = $isPrintOrExcel ? 'laporan.stok.margin' : 'laporan.stok.index';
            $items = collect();
            
            if ($isPrintOrExcel) {
                $query = Barang::with(['satuans', 'supplier']);
                
                if ($kode_supplier) {
                    $query->where('kode_supplier', $kode_supplier);
                }
                if ($kategori) {
                    $query->where('kategori', $kategori);
                }
                if ($merk) {
                    $query->where('merk', $merk);
                }
                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('kode_barang', 'like', "%$search%")
                          ->orWhere('nama_barang', 'like', "%$search%")
                          ->orWhere('kode_item', 'like', "%$search%");
                    });
                }

                if (!$tampilkan_stok_kosong) {
                    $query->where('stok', '>', 0);
                }

                $barangs = $query->orderBy('nama_barang', 'asc')->get();
                
                foreach ($barangs as $b) {
                    $stokRemaining = (float)$b->stok;
                    $isNegative = $stokRemaining < 0;
                    $absRemaining = abs($stokRemaining);
                    
                    $satuans = $b->satuans;
                    
                    if ($satuans->isEmpty()) {
                        // Fallback if no unit is defined
                        $items->push([
                            'barang'              => $b,
                            'kode_barang'         => $b->kode_barang,
                            'kode_item'           => $b->kode_item,
                            'nama_barang'         => $b->nama_barang,
                            'satuan'              => 'PCS',
                            'jenis'               => $b->jenis,
                            'kategori'            => $b->kategori,
                            'merk'                => $b->merk,
                            'stok'                => $stokRemaining,
                            'harga_pokok'         => 0,
                            'harga_jual'          => 0,
                            'margin_rp'           => 0,
                            'margin_persen'       => 0,
                            'total_pokok'         => 0,
                            'total_jual'          => 0,
                            'total_margin'        => 0,
                        ]);
                        continue;
                    }

                    // Sort units by isi descending (largest unit first)
                    $sortedSatuans = $satuans->sortByDesc('isi');
                    $count = $sortedSatuans->count();
                    $i = 0;

                    foreach ($sortedSatuans as $sat) {
                        $i++;
                        $factor = (float)($sat->isi ?: 1);

                        if ($i === $count) {
                            // Smallest unit gets the remaining stock
                            $unitQty = round($absRemaining / $factor, 4);
                        } else {
                            $unitQty = floor(round($absRemaining / $factor, 8));
                            $absRemaining = round($absRemaining - ($unitQty * $factor), 4);
                        }

                        $qty = $isNegative ? -$unitQty : $unitQty;

                        // Skip rows with 0 stock if tampilkan_stok_kosong is false
                        if (!$tampilkan_stok_kosong && $qty == 0) {
                            continue;
                        }

                        $hargaPokok = (float)$sat->harga_pokok;
                        $hargaJual = (float)$sat->harga_jual;
                        $margin_rp = $hargaJual - $hargaPokok;
                        $margin_persen = $hargaPokok > 0 ? ($margin_rp / $hargaPokok) * 100 : 0;

                        $items->push([
                            'barang'              => $b,
                            'kode_barang'         => $b->kode_barang,
                            'kode_item'           => $b->kode_item,
                            'nama_barang'         => $b->nama_barang,
                            'satuan'              => $sat->satuan,
                            'jenis'               => $b->jenis,
                            'kategori'            => $b->kategori,
                            'merk'                => $b->merk,
                            'stok'                => $qty,
                            'harga_pokok'         => $hargaPokok,
                            'harga_jual'          => $hargaJual,
                            'margin_rp'           => $margin_rp,
                            'margin_persen'       => $margin_persen,
                            'total_pokok'         => $qty * $hargaPokok,
                            'total_jual'          => $qty * $hargaJual,
                            'total_margin'        => $qty * $margin_rp,
                        ]);
                    }
                }
            }

            if ($isExcel) {
                $filename = 'laporan_margin_barang_' . date('Ymd_His') . '.xls';
                return response(view($view, compact(
                    'barangsList', 'kategoris', 'merks', 'suppliers', 'items', 
                    'jenis_laporan', 'kategori', 'merk', 'kode_supplier', 'tanggal_mulai', 'tanggal_akhir', 'search', 'isExcel', 'tampilkan_stok_kosong'
                )))
                ->header('Content-Type', 'application/vnd-ms-excel')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
            }

            return view($view, compact(
                'barangsList', 'kategoris', 'merks', 'suppliers', 'items', 
                'jenis_laporan', 'kategori', 'merk', 'kode_supplier', 'tanggal_mulai', 'tanggal_akhir', 'search', 'tampilkan_stok_kosong'
            ));
        } else {
            // detail (buku/kartu stok per barang)
            $barang = null;
            $movements = collect();
            $stokAwal = 0;
            $stokAkhir = 0;

            if ($isPrintOrExcel && $kode_barang) {
                $barang = Barang::with('satuans')->find($kode_barang);
                if ($barang) {
                    // Calculate Stok Awal = saldo_akhir of the last mutation before tanggal_mulai
                    $lastMutationBefore = DB::table('stok_mutasi')
                        ->where('kode_barang', $kode_barang)
                        ->where('tanggal', '<', $tanggal_mulai)
                        ->orderBy('tanggal', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    
                    $stokAwal = $lastMutationBefore ? (float)$lastMutationBefore->saldo_akhir : 0;

                    // Retrieve movements within range directly from stok_mutasi
                    $rawMutations = DB::table('stok_mutasi')
                        ->where('kode_barang', $kode_barang)
                        ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
                        ->orderBy('tanggal', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();

                    // Pre-fetch related documents to prevent N+1 query overhead
                    $refPenjualan = $rawMutations->filter(function($m) {
                        return in_array($m->jenis_transaksi, ['Penjualan', 'Batal Penjualan', 'Batal Penjualan (Edit)', 'Batal Jual']);
                    })->pluck('no_referensi')->unique()->toArray();

                    $refReturJual = $rawMutations->filter(function($m) {
                        return in_array($m->jenis_transaksi, ['Retur Penjualan', 'Batal Retur Penjualan', 'Batal Retur Penjualan (Edit)']);
                    })->pluck('no_referensi')->unique()->toArray();

                    $refPembelian = $rawMutations->filter(function($m) {
                        return in_array($m->jenis_transaksi, ['Pembelian', 'Batal Pembelian', 'Batal Pembelian (Edit)']);
                    })->pluck('no_referensi')->unique()->toArray();

                    $refReturBeli = $rawMutations->filter(function($m) {
                        return in_array($m->jenis_transaksi, ['Retur Pembelian', 'Batal Retur Pembelian', 'Batal Retur Pembelian (Edit)']);
                    })->pluck('no_referensi')->unique()->toArray();

                    $refOpname = $rawMutations->filter(function($m) {
                        return in_array($m->jenis_transaksi, ['Stok Opname', 'Batal Stok Opname', 'Batal Stok Opname (Edit)']);
                    })->pluck('no_referensi')->unique()->toArray();

                    // Bulk queries
                    $penjualans = $refPenjualan ? Penjualan::with(['pelanggan.wilayah', 'sales'])->whereIn('no_faktur', $refPenjualan)->get()->keyBy('no_faktur') : collect();
                    $returPenjualans = $refReturJual ? ReturPenjualan::with(['pelanggan.wilayah', 'sales'])->whereIn('no_retur', $refReturJual)->get()->keyBy('no_retur') : collect();
                    $pembelians = $refPembelian ? Pembelian::with('supplier')->whereIn('no_faktur', $refPembelian)->get()->keyBy('no_faktur') : collect();
                    $returPembelians = $refReturBeli ? ReturPembelian::with('supplier')->whereIn('no_retur', $refReturBeli)->get()->keyBy('no_retur') : collect();
                    $opnames = $refOpname ? \App\Models\StokOpname::with('user')->whereIn('no_opname', $refOpname)->get()->keyBy('no_opname') : collect();

                    // Map mutations to the format expected by views
                    $running = $stokAwal;
                    foreach ($rawMutations as $m) {
                        $pembelian_masuk = 0;
                        $retur_jual = 0;
                        $batal_jual = 0;
                        $opname_masuk = 0;
                        $penjualan_keluar = 0;
                        $retur_beli = 0;
                        $opname_keluar = 0;

                        // Categorize into view columns
                        if ($m->jenis_transaksi === 'Pembelian') {
                            $pembelian_masuk = $m->qty_masuk;
                        } elseif ($m->jenis_transaksi === 'Retur Penjualan') {
                            $retur_jual = $m->qty_masuk;
                        } elseif (in_array($m->jenis_transaksi, ['Batal Penjualan', 'Batal Penjualan (Edit)', 'Batal Jual'])) {
                            $batal_jual = $m->qty_masuk;
                        } elseif ($m->jenis_transaksi === 'Penjualan') {
                            $penjualan_keluar = $m->qty_keluar;
                        } elseif ($m->jenis_transaksi === 'Retur Pembelian') {
                            $retur_beli = $m->qty_keluar;
                        } else {
                            // Opname or adjustments
                            if ($m->qty_masuk > 0) {
                                $opname_masuk = $m->qty_masuk;
                            }
                            if ($m->qty_keluar > 0) {
                                $opname_keluar = $m->qty_keluar;
                            }
                        }

                        // Determine metadata based on transaction type
                        $pelanggan = '-';
                        $wilayah = '-';
                        $kode_sales = '-';
                        $nama_sales = '-';
                        $keterangan = $m->keterangan ?? $m->jenis_transaksi;

                        if (in_array($m->jenis_transaksi, ['Penjualan', 'Batal Penjualan', 'Batal Penjualan (Edit)', 'Batal Jual'])) {
                            $doc = $penjualans->get($m->no_referensi);
                            if ($doc) {
                                $pelanggan = $doc->pelanggan->nama_pelanggan ?? '-';
                                $wilayah = $doc->pelanggan->wilayah->nama_wilayah ?? '-';
                                $kode_sales = $doc->kode_sales ?? '-';
                                $nama_sales = $doc->sales->name ?? '-';
                                if ($m->jenis_transaksi !== 'Penjualan') {
                                    $keterangan = 'Pembatalan Penjualan' . ($doc->alasan_batal ? ' (' . $doc->alasan_batal . ')' : '');
                                }
                            }
                        } elseif (in_array($m->jenis_transaksi, ['Retur Penjualan', 'Batal Retur Penjualan', 'Batal Retur Penjualan (Edit)'])) {
                            $doc = $returPenjualans->get($m->no_referensi);
                            if ($doc) {
                                $pelanggan = $doc->pelanggan->nama_pelanggan ?? '-';
                                $wilayah = $doc->pelanggan->wilayah->nama_wilayah ?? '-';
                                $kode_sales = $doc->kode_sales ?? '-';
                                $nama_sales = $doc->sales->name ?? '-';
                            }
                        } elseif (in_array($m->jenis_transaksi, ['Pembelian', 'Batal Pembelian', 'Batal Pembelian (Edit)'])) {
                            $doc = $pembelians->get($m->no_referensi);
                            if ($doc) {
                                $pelanggan = $doc->supplier->nama_supplier ?? '-';
                            }
                        } elseif (in_array($m->jenis_transaksi, ['Retur Pembelian', 'Batal Retur Pembelian', 'Batal Retur Pembelian (Edit)'])) {
                            $doc = $returPembelians->get($m->no_referensi);
                            if ($doc) {
                                $pelanggan = $doc->supplier->nama_supplier ?? '-';
                            }
                        } elseif (in_array($m->jenis_transaksi, ['Stok Opname', 'Batal Stok Opname', 'Batal Stok Opname (Edit)'])) {
                            $doc = $opnames->get($m->no_referensi);
                            if ($doc) {
                                $pelanggan = $doc->user->name ?? '-';
                                if ($doc->keterangan) {
                                    $keterangan = 'Stok Opname (' . $doc->keterangan . ')';
                                }
                            }
                        }

                        if (in_array($m->jenis_transaksi, ['Stok Opname', 'Batal Stok Opname', 'Batal Stok Opname (Edit)']) && isset($m->saldo_akhir)) {
                            $running = (float)$m->saldo_akhir;
                        } else {
                            $running = $running + $m->qty_masuk - $m->qty_keluar;
                        }

                        $movements->push([
                            'class' => 'class',
                            'tanggal' => $m->tanggal,
                            'no_referensi' => $m->no_referensi,
                            'jenis' => $m->jenis_transaksi,
                            'keterangan' => $keterangan,
                            'pelanggan' => $pelanggan,
                            'wilayah' => $wilayah,
                            'kode_sales' => $kode_sales,
                            'nama_sales' => $nama_sales,
                            'pembelian_masuk' => $pembelian_masuk,
                            'retur_jual' => $retur_jual,
                            'batal_jual' => $batal_jual,
                            'opname_masuk' => $opname_masuk,
                            'penjualan_keluar' => $penjualan_keluar,
                            'retur_beli' => $retur_beli,
                            'opname_keluar' => $opname_keluar,
                            'masuk' => $m->qty_masuk,
                            'keluar' => $m->qty_keluar,
                            'saldo' => $running
                        ]);
                    }

                    $stokAkhir = $running;
                }
            }

            if ($isExcel) {
                $filename = 'laporan_kartu_stok_' . date('Ymd_His') . '.xls';
                return response(view($view, compact(
                    'barangsList', 'kategoris', 'merks', 'suppliers', 'barang', 
                    'movements', 'stokAwal', 'stokAkhir', 'jenis_laporan', 
                    'kode_barang', 'tanggal_mulai', 'tanggal_akhir', 'kode_supplier', 'search', 'kategori', 'merk', 'isExcel', 'tampilkan_stok_kosong'
                )))
                ->header('Content-Type', 'application/vnd-ms-excel')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
            }

            return view($view, compact(
                'barangsList', 'kategoris', 'merks', 'suppliers', 'barang', 
                'movements', 'stokAwal', 'stokAkhir', 'jenis_laporan', 
                'kode_barang', 'tanggal_mulai', 'tanggal_akhir', 'kode_supplier', 'search', 'kategori', 'merk', 'tampilkan_stok_kosong'
            ));
        }
    }

    private function authorizeReport($type)
    {
        $permission = 'view-laporan_' . $type;
        if (!auth()->user()->can($permission)) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }
    }
}
