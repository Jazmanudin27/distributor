<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\ReturPembelian;
use App\Models\ReturPembelianDetail;
use App\Models\StokOpnameDetail;
use App\Models\Barang;
use App\Models\Supplier;
use App\Models\Kategori;
use App\Models\Merk;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\ReturPenjualan;
use App\Models\ReturPenjualanDetail;
use App\Models\User;
use App\Models\Pelanggan;
use App\Models\Wilayah;
use App\Models\SubWilayah;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function laporanPembelian(Request $request)
    {
        $this->authorizeReport('pembelian');

        $suppliers = Supplier::orderBy('nama_supplier', 'asc')->get();
        
        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $kode_supplier = $request->input('kode_supplier');
        $jenis_laporan = $request->input('jenis_laporan', 'rekap');
        $group_by_supplier = $request->input('group_by_supplier', '0');

        $items = collect();
        $isCetak = $request->is('*/cetak');
        $isExcel = $request->is('*/excel');
        $isPrintOrExcel = $isCetak || $isExcel;

        if ($isPrintOrExcel) {
            if ($jenis_laporan === 'rekap') {
                $query = Pembelian::with(['supplier', 'details.barangSatuan']);
                
                if ($tanggal_mulai) {
                    $query->where('tanggal', '>=', $tanggal_mulai);
                }
                if ($tanggal_akhir) {
                    $query->where('tanggal', '<=', $tanggal_akhir);
                }
                if ($kode_supplier) {
                    $query->where('kode_supplier', $kode_supplier);
                }

                $query->orderBy('tanggal', 'asc')->orderBy('no_faktur', 'asc');

                if ($group_by_supplier === '1') {
                    $items = $query->get()->groupBy('kode_supplier');
                } else {
                    $items = $query->get();
                }
            } else {
                // detail
                $query = PembelianDetail::with(['pembelian.supplier', 'barang', 'barangSatuan'])
                    ->whereHas('pembelian', function ($q) use ($tanggal_mulai, $tanggal_akhir, $kode_supplier) {
                        if ($tanggal_mulai) {
                            $q->where('tanggal', '>=', $tanggal_mulai);
                        }
                        if ($tanggal_akhir) {
                            $q->where('tanggal', '<=', $tanggal_akhir);
                        }
                        if ($kode_supplier) {
                            $q->where('kode_supplier', $kode_supplier);
                        }
                    });

                $query->join('pembelian', 'pembelian_detail.no_faktur', '=', 'pembelian.no_faktur')
                      ->orderBy('pembelian.tanggal', 'asc')
                      ->orderBy('pembelian.no_faktur', 'asc')
                      ->select('pembelian_detail.*');

                if ($group_by_supplier === '1') {
                    $items = $query->get()->groupBy(function($item) {
                        return $item->pembelian->kode_supplier;
                    });
                } else {
                    $items = $query->get();
                }
            }
        }

        $view = $isPrintOrExcel ? 'laporan.cetak_pembelian' : 'laporan.pembelian';

        if ($isExcel) {
            $filename = 'laporan_pembelian_' . date('Ymd_His') . '.xls';
            return response(view($view, compact(
                'suppliers', 'items', 'tanggal_mulai', 'tanggal_akhir', 
                'kode_supplier', 'jenis_laporan', 'group_by_supplier', 'isExcel'
            )))
            ->header('Content-Type', 'application/vnd-ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        }

        return view($view, compact(
            'suppliers', 'items', 'tanggal_mulai', 'tanggal_akhir', 
            'kode_supplier', 'jenis_laporan', 'group_by_supplier'
        ));
    }

    public function laporanReturPembelian(Request $request)
    {
        $this->authorizeReport('retur_pembelian');

        $suppliers = Supplier::orderBy('nama_supplier', 'asc')->get();
        
        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $kode_supplier = $request->input('kode_supplier');
        $jenis_laporan = $request->input('jenis_laporan', 'rekap');
        $group_by_supplier = $request->input('group_by_supplier', '0');

        $items = collect();
        $isCetak = $request->is('*/cetak');
        $isExcel = $request->is('*/excel');
        $isPrintOrExcel = $isCetak || $isExcel;

        if ($isPrintOrExcel) {
            if ($jenis_laporan === 'rekap') {
                $query = ReturPembelian::with(['supplier', 'details.barangSatuan']);
                
                if ($tanggal_mulai) {
                    $query->where('tanggal', '>=', $tanggal_mulai);
                }
                if ($tanggal_akhir) {
                    $query->where('tanggal', '<=', $tanggal_akhir);
                }
                if ($kode_supplier) {
                    $query->where('kode_supplier', $kode_supplier);
                }

                $query->orderBy('tanggal', 'asc')->orderBy('no_retur', 'asc');

                if ($group_by_supplier === '1') {
                    $items = $query->get()->groupBy('kode_supplier');
                } else {
                    $items = $query->get();
                }
            } else {
                // detail
                $query = ReturPembelianDetail::with(['returPembelian.supplier', 'barang', 'barangSatuan'])
                    ->whereHas('returPembelian', function ($q) use ($tanggal_mulai, $tanggal_akhir, $kode_supplier) {
                        if ($tanggal_mulai) {
                            $q->where('tanggal', '>=', $tanggal_mulai);
                        }
                        if ($tanggal_akhir) {
                            $q->where('tanggal', '<=', $tanggal_akhir);
                        }
                        if ($kode_supplier) {
                            $q->where('kode_supplier', $kode_supplier);
                        }
                    });

                $query->join('retur_pembelian', 'retur_pembelian_detail.no_retur', '=', 'retur_pembelian.no_retur')
                      ->orderBy('retur_pembelian.tanggal', 'asc')
                      ->orderBy('retur_pembelian.no_retur', 'asc')
                      ->select('retur_pembelian_detail.*');

                if ($group_by_supplier === '1') {
                    $items = $query->get()->groupBy(function($item) {
                        return $item->returPembelian->kode_supplier;
                    });
                } else {
                    $items = $query->get();
                }
            }
        }

        $view = $isPrintOrExcel ? 'laporan.cetak_retur_pembelian' : 'laporan.retur_pembelian';

        if ($isExcel) {
            $filename = 'laporan_retur_pembelian_' . date('Ymd_His') . '.xls';
            return response(view($view, compact(
                'suppliers', 'items', 'tanggal_mulai', 'tanggal_akhir', 
                'kode_supplier', 'jenis_laporan', 'group_by_supplier', 'isExcel'
            )))
            ->header('Content-Type', 'application/vnd-ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        }

        return view($view, compact(
            'suppliers', 'items', 'tanggal_mulai', 'tanggal_akhir', 
            'kode_supplier', 'jenis_laporan', 'group_by_supplier'
        ));
    }

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

        $view = $isPrintOrExcel ? 'laporan.cetak_stok' : 'laporan.stok';

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
            $view = $isPrintOrExcel ? 'laporan.cetak_persediaan' : 'laporan.stok';
            $items = collect();

            if ($isPrintOrExcel) {
                $query = Barang::where('status', 1)->with(['satuans', 'supplier']);
                
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

                $barangs = $query->orderBy('nama_barang', 'asc')->get();
                $barangIds = $barangs->pluck('kode_barang')->toArray();

                // 1. Get latest mutation saldo_akhir on or before tanggal_akhir
                $latestMutations = DB::table('stok_mutasi')
                    ->whereIn('kode_barang', $barangIds)
                    ->where('tanggal', '<=', $tanggal_akhir)
                    ->whereIn('id', function($q) use ($tanggal_akhir, $barangIds) {
                        $q->selectRaw('MAX(id)')
                          ->from('stok_mutasi')
                          ->whereIn('kode_barang', $barangIds)
                          ->where('tanggal', '<=', $tanggal_akhir)
                          ->groupBy('kode_barang');
                    })
                    ->select('kode_barang', 'saldo_akhir')
                    ->get()
                    ->pluck('saldo_akhir', 'kode_barang');

                // 2. Get mutations in period grouped by kode_barang & jenis_transaksi
                $mutationsInRange = DB::table('stok_mutasi')
                    ->whereIn('kode_barang', $barangIds)
                    ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->select(
                        'kode_barang',
                        'jenis_transaksi',
                        DB::raw('SUM(qty_masuk) as total_masuk'),
                        DB::raw('SUM(qty_keluar) as total_keluar')
                    )
                    ->groupBy('kode_barang', 'jenis_transaksi')
                    ->get()
                    ->groupBy('kode_barang');

                foreach ($barangs as $b) {
                    $kb = $b->kode_barang;
                    $baseSatuan = $b->satuans->sortBy('isi')->first();
                    $baseSatuanName = $baseSatuan ? $baseSatuan->satuan : 'PCS';
                    $hargaPokok = $baseSatuan ? (float)$baseSatuan->harga_pokok : 0;
                    $hargaJual = $baseSatuan ? (float)$baseSatuan->harga_jual : 0;

                    $stokAkhir = (float)($latestMutations->get($kb) ?? 0);

                    $pPeriod = $mutationsInRange->get($kb) ?? collect();

                    // PENERIMAAN
                    $pembelianPeriod = (float)$pPeriod->where('jenis_transaksi', 'Pembelian')->sum('total_masuk');
                    $returJualPeriod = (float)$pPeriod->where('jenis_transaksi', 'Retur Penjualan')->sum('total_masuk');
                    $batalSalesPeriod = (float)$pPeriod->whereIn('jenis_transaksi', ['Batal Penjualan', 'Batal Penjualan (Edit)', 'Batal Jual'])->sum('total_masuk');
                    $opnameMasukPeriod = (float)$pPeriod->whereNotIn('jenis_transaksi', ['Pembelian', 'Retur Penjualan', 'Batal Penjualan', 'Batal Penjualan (Edit)', 'Batal Jual'])->sum('total_masuk');

                    // PENGELUARAN
                    $penjualanPeriod = (float)$pPeriod->where('jenis_transaksi', 'Penjualan')->sum('total_keluar');
                    $returBeliPeriod = (float)$pPeriod->where('jenis_transaksi', 'Retur Pembelian')->sum('total_keluar');
                    $opnameKeluarPeriod = (float)$pPeriod->whereNotIn('jenis_transaksi', ['Penjualan', 'Retur Pembelian'])->sum('total_keluar');

                    $penerimaanTotal = $pembelianPeriod + $returJualPeriod + $batalSalesPeriod + $opnameMasukPeriod;
                    $pengeluaranTotal = $penjualanPeriod + $returBeliPeriod + $opnameKeluarPeriod;

                    $stokAwal = $stokAkhir - $penerimaanTotal + $pengeluaranTotal;

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

                        $running = $running + $m->qty_masuk - $m->qty_keluar;

                        $movements->push([
                            'class' => 'class', // dummy for mapping logic helper if needed, but not required
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

    public function laporanPenjualan(Request $request)
    {
        $this->authorizeReport('penjualan');

        $salesmen = User::where(function ($q) {
            $q->where('role', 'sales')->orWhere('role', 'Salesman');
        })->where('status', '1')->orderBy('name')->get();
        
        $suppliers = Supplier::orderBy('nama_supplier', 'asc')->get();
        
        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $kode_sales = $request->input('kode_sales');
        $kode_pelanggan = $request->input('kode_pelanggan');
        $kode_supplier = $request->input('kode_supplier');
        $jenis_laporan = $request->input('jenis_laporan', 'rekap'); // rekap, detail, detail_rowspan
        $jenis_transaksi = $request->input('jenis_transaksi');
        $status_faktur = $request->input('status_faktur', 'aktif'); // aktif, batal, semua

        $pelanggans = collect();
        if ($kode_pelanggan) {
            $pelanggans = Pelanggan::where('kode_pelanggan', $kode_pelanggan)->get();
        }

        $items = collect();
        $isCetak = $request->is('*/cetak');
        $isExcel = $request->is('*/excel');
        $isPrintOrExcel = $isCetak || $isExcel;

        if ($isPrintOrExcel) {
            if ($jenis_laporan === 'rekap') {
                $query = Penjualan::with(['pelanggan.wilayah', 'sales', 'user'])
                    ->where(function($q) use ($status_faktur) {
                        if ($status_faktur === 'aktif') {
                            $q->where('batal', 0);
                        } elseif ($status_faktur === 'batal') {
                            $q->where('batal', 1);
                        }
                    });
                
                if ($tanggal_mulai) $query->where('tanggal', '>=', $tanggal_mulai);
                if ($tanggal_akhir) $query->where('tanggal', '<=', $tanggal_akhir);
                if ($kode_sales) $query->where('kode_sales', $kode_sales);
                if ($kode_pelanggan) $query->where('kode_pelanggan', $kode_pelanggan);
                if ($jenis_transaksi) $query->where('jenis_transaksi', $jenis_transaksi);
                
                if ($kode_supplier) {
                    $query->whereHas('details.barang', function($q) use ($kode_supplier) {
                        $q->where('kode_supplier', $kode_supplier);
                    });
                }

                $items = $query->orderBy('tanggal', 'asc')->orderBy('no_faktur', 'asc')->get();
            } else {
                // detail_rowspan (Format 3)
                $query = Penjualan::with([
                    'pelanggan.wilayah', 
                    'sales', 
                    'user', 
                    'details' => function($q) use ($kode_supplier) {
                        $q->with(['barang', 'barangSatuan']);
                        if ($kode_supplier) {
                            $q->whereHas('barang', function($bq) use ($kode_supplier) {
                                $bq->where('kode_supplier', $kode_supplier);
                            });
                        }
                    }
                ])
                ->where(function($q) use ($status_faktur) {
                    if ($status_faktur === 'aktif') {
                        $q->where('batal', 0);
                    } elseif ($status_faktur === 'batal') {
                        $q->where('batal', 1);
                    }
                });
                
                if ($tanggal_mulai) $query->where('tanggal', '>=', $tanggal_mulai);
                if ($tanggal_akhir) $query->where('tanggal', '<=', $tanggal_akhir);
                if ($kode_sales) $query->where('kode_sales', $kode_sales);
                if ($kode_pelanggan) $query->where('kode_pelanggan', $kode_pelanggan);
                if ($jenis_transaksi) $query->where('jenis_transaksi', $jenis_transaksi);
                
                if ($kode_supplier) {
                    $query->whereHas('details.barang', function($q) use ($kode_supplier) {
                        $q->where('kode_supplier', $kode_supplier);
                    });
                }

                $items = $query->orderBy('tanggal', 'asc')->orderBy('no_faktur', 'asc')->get();

                // Pre-aggregate payments and returs for these invoices
                $invoiceIds = $items->pluck('no_faktur')->toArray();

                $cashPayments = DB::table('penjualan_pembayaran')
                    ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                    ->where('status', 'disetujui')
                    ->whereIn('no_faktur', $invoiceIds)
                    ->groupBy('no_faktur')
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                $transferPayments = DB::table('penjualan_pembayaran_transfer')
                    ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                    ->where('status', 'disetujui')
                    ->whereIn('no_faktur', $invoiceIds)
                    ->groupBy('no_faktur')
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                $giroPayments = DB::table('penjualan_pembayaran_giro')
                    ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                    ->where('status', 'disetujui')
                    ->whereIn('no_faktur', $invoiceIds)
                    ->groupBy('no_faktur')
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                $returs = DB::table('retur_penjualan')
                    ->select('no_faktur', DB::raw('SUM(total) as total'))
                    ->whereIn('no_faktur', $invoiceIds)
                    ->groupBy('no_faktur')
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                foreach ($items as $invoice) {
                    $cashPaid = $cashPayments[$invoice->no_faktur] ?? 0;
                    $transferPaid = $transferPayments[$invoice->no_faktur] ?? 0;
                    $giroPaid = $giroPayments[$invoice->no_faktur] ?? 0;
                    $paid = $cashPaid + $transferPaid + $giroPaid;
                    $returPaid = $returs[$invoice->no_faktur] ?? 0;

                    $invoice->total_bayar = $paid;
                    $invoice->total_retur = $returPaid;
                    $sisa = (float)($invoice->grand_total - $paid - $returPaid);
                    $invoice->sisa_bayar = $sisa < 1 ? 0.0 : $sisa;
                    $invoice->status_pembayaran = $invoice->sisa_bayar <= 0 ? 'Lunas' : 'Belum Lunas';
                }
            }
        }

        $view = $isPrintOrExcel ? 'laporan.cetak_penjualan' : 'laporan.penjualan';

        if ($isExcel) {
            $filename = 'laporan_penjualan_' . date('Ymd_His') . '.xls';
            return response(view($view, compact(
                'salesmen', 'pelanggans', 'suppliers', 'items', 'tanggal_mulai', 'tanggal_akhir', 
                'kode_sales', 'kode_pelanggan', 'kode_supplier', 'jenis_laporan', 'jenis_transaksi', 'status_faktur', 'isExcel'
            )))
            ->header('Content-Type', 'application/vnd-ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        }

        return view($view, compact(
            'salesmen', 'pelanggans', 'suppliers', 'items', 'tanggal_mulai', 'tanggal_akhir', 
            'kode_sales', 'kode_pelanggan', 'kode_supplier', 'jenis_laporan', 'jenis_transaksi', 'status_faktur'
        ));
    }

    public function laporanReturPenjualan(Request $request)
    {
        $this->authorizeReport('retur_penjualan');

        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $kode_pelanggan = $request->input('kode_pelanggan');
        $jenis_laporan = $request->input('jenis_laporan', 'rekap');

        $pelanggans = collect();
        if ($kode_pelanggan) {
            $pelanggans = Pelanggan::where('kode_pelanggan', $kode_pelanggan)->get();
        }

        $items = collect();
        $isCetak = $request->is('*/cetak');
        $isExcel = $request->is('*/excel');
        $isPrintOrExcel = $isCetak || $isExcel;

        if ($isPrintOrExcel) {
            if ($jenis_laporan === 'rekap') {
                $query = ReturPenjualan::with(['pelanggan', 'sales']);
                
                if ($tanggal_mulai) $query->where('tanggal', '>=', $tanggal_mulai);
                if ($tanggal_akhir) $query->where('tanggal', '<=', $tanggal_akhir);
                if ($kode_pelanggan) $query->where('kode_pelanggan', $kode_pelanggan);

                $items = $query->orderBy('tanggal', 'asc')->orderBy('no_retur', 'asc')->get();
            } else {
                // detail
                $query = ReturPenjualanDetail::with(['returPenjualan.pelanggan', 'barang', 'barangSatuan'])
                    ->whereHas('returPenjualan', function ($q) use ($tanggal_mulai, $tanggal_akhir, $kode_pelanggan) {
                        if ($tanggal_mulai) $q->where('tanggal', '>=', $tanggal_mulai);
                        if ($tanggal_akhir) $q->where('tanggal', '<=', $tanggal_akhir);
                        if ($kode_pelanggan) $q->where('kode_pelanggan', $kode_pelanggan);
                    });

                $items = $query->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
                    ->orderBy('retur_penjualan.tanggal', 'asc')
                    ->orderBy('retur_penjualan.no_retur', 'asc')
                    ->select('retur_penjualan_detail.*')
                    ->get();
            }
        }

        $view = $isPrintOrExcel ? 'laporan.cetak_retur_penjualan' : 'laporan.retur_penjualan';

        if ($isExcel) {
            $filename = 'laporan_retur_penjualan_' . date('Ymd_His') . '.xls';
            return response(view($view, compact(
                'pelanggans', 'items', 'tanggal_mulai', 'tanggal_akhir', 
                'kode_pelanggan', 'jenis_laporan', 'isExcel'
            )))
            ->header('Content-Type', 'application/vnd-ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        }

        return view($view, compact(
            'pelanggans', 'items', 'tanggal_mulai', 'tanggal_akhir', 
            'kode_pelanggan', 'jenis_laporan'
        ));
    }

    public function laporanPiutang(Request $request)
    {
        $this->authorizeReport('piutang');

        $kode_pelanggan = $request->input('kode_pelanggan');
        $jenis_laporan = $request->input('jenis_laporan', 'rekap');

        $pelanggans = collect();
        if ($kode_pelanggan) {
            $pelanggans = Pelanggan::where('kode_pelanggan', $kode_pelanggan)->get();
        }

        $items = collect();
        $isCetak = $request->is('*/cetak');
        $isExcel = $request->is('*/excel');
        $isPrintOrExcel = $isCetak || $isExcel;

        if ($isPrintOrExcel) {
            if ($jenis_laporan === 'rekap') {
                $query = Pelanggan::with('wilayah');
                if ($kode_pelanggan) {
                    $query->where('kode_pelanggan', $kode_pelanggan);
                }
                $customers = $query->orderBy('nama_pelanggan')->get();
                $customerIds = $customers->pluck('kode_pelanggan')->toArray();

                // Pre-aggregate all unpaid/outstanding invoices and payments
                $invoices = DB::table('penjualan')
                    ->select('no_faktur', 'tanggal', 'kode_pelanggan', 'grand_total', 'jenis_transaksi')
                    ->where('batal', 0)
                    ->whereIn('kode_pelanggan', $customerIds)
                    ->get();

                $cashPayments = DB::table('penjualan_pembayaran')
                    ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                    ->where('status', 'disetujui')
                    ->whereIn('kode_pelanggan', $customerIds)
                    ->groupBy('no_faktur')
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                $transferPayments = DB::table('penjualan_pembayaran_transfer')
                    ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                    ->where('status', 'disetujui')
                    ->whereIn('kode_pelanggan', $customerIds)
                    ->groupBy('no_faktur')
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                $giroPayments = DB::table('penjualan_pembayaran_giro')
                    ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                    ->where('status', 'disetujui')
                    ->whereIn('kode_pelanggan', $customerIds)
                    ->groupBy('no_faktur')
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                $invoicesByCustomer = [];
                foreach ($invoices as $inv) {
                    $paid = ($cashPayments[$inv->no_faktur] ?? 0) + ($transferPayments[$inv->no_faktur] ?? 0) + ($giroPayments[$inv->no_faktur] ?? 0);
                    $rem = (float)$inv->grand_total - $paid;
                    $remaining = $rem < 1 ? 0.0 : $rem;

                    if ($remaining >= 1) {
                        $invoicesByCustomer[$inv->kode_pelanggan][] = [
                            'invoice' => $inv,
                            'remaining' => $remaining
                        ];
                    }
                }

                $today = Carbon::now();
                foreach ($customers as $c) {
                    $custInvoices = $invoicesByCustomer[$c->kode_pelanggan] ?? [];
                    $outstanding = 0;
                    $total_overdue = 0;
                    $overdue_count = 0;
                    $ljt = $c->ljt ?? 30;

                    foreach ($custInvoices as $item) {
                        $outstanding += $item['remaining'];
                        $inv = $item['invoice'];

                        if ($inv->jenis_transaksi === 'K' || $inv->jenis_transaksi === 'Kredit') {
                            $jatuh_tempo = Carbon::parse($inv->tanggal)->addDays($ljt);
                            if ($today->greaterThan($jatuh_tempo)) {
                                $total_overdue += $item['remaining'];
                                $overdue_count++;
                            }
                        }
                    }

                    $sisa_limit = max(0, (float)$c->limit_pelanggan - $outstanding);

                    if ($outstanding > 0 || $kode_pelanggan) {
                        $items->push([
                            'pelanggan' => $c,
                            'limit_kredit' => $c->limit_pelanggan,
                            'outstanding' => $outstanding,
                            'sisa_limit' => $sisa_limit,
                            'total_overdue' => $total_overdue,
                            'overdue_count' => $overdue_count,
                        ]);
                    }
                }
            } elseif ($jenis_laporan === 'aging') {
                $query = Pelanggan::with('wilayah');
                if ($kode_pelanggan) {
                    $query->where('kode_pelanggan', $kode_pelanggan);
                }
                $customers = $query->orderBy('nama_pelanggan')->get();
                $customerIds = $customers->pluck('kode_pelanggan')->toArray();

                // Pre-aggregate all unpaid/outstanding invoices and payments
                $invoices = DB::table('penjualan')
                    ->select('no_faktur', 'tanggal', 'kode_pelanggan', 'grand_total', 'jenis_transaksi')
                    ->where('batal', 0)
                    ->whereIn('kode_pelanggan', $customerIds)
                    ->get();

                $cashPayments = DB::table('penjualan_pembayaran')
                    ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                    ->where('status', 'disetujui')
                    ->whereIn('kode_pelanggan', $customerIds)
                    ->groupBy('no_faktur')
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                $transferPayments = DB::table('penjualan_pembayaran_transfer')
                    ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                    ->where('status', 'disetujui')
                    ->whereIn('kode_pelanggan', $customerIds)
                    ->groupBy('no_faktur')
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                $giroPayments = DB::table('penjualan_pembayaran_giro')
                    ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                    ->where('status', 'disetujui')
                    ->whereIn('kode_pelanggan', $customerIds)
                    ->groupBy('no_faktur')
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                $invoicesByCustomer = [];
                foreach ($invoices as $inv) {
                    $paid = ($cashPayments[$inv->no_faktur] ?? 0) + ($transferPayments[$inv->no_faktur] ?? 0) + ($giroPayments[$inv->no_faktur] ?? 0);
                    $rem = (float)$inv->grand_total - $paid;
                    $remaining = $rem < 1 ? 0.0 : $rem;

                    if ($remaining >= 1) {
                        $invoicesByCustomer[$inv->kode_pelanggan][] = [
                            'invoice' => $inv,
                            'remaining' => $remaining
                        ];
                    }
                }

                $today = Carbon::today(); // midnight local time
                $todayTs = $today->timestamp; // cache timestamp agar tidak termutasi
                foreach ($customers as $c) {
                    $custInvoices = $invoicesByCustomer[$c->kode_pelanggan] ?? [];
                    $total_piutang = 0;
                    $belum_jt = 0;
                    $overdue_1_30 = 0;
                    $overdue_31_60 = 0;
                    $overdue_61_90 = 0;
                    $overdue_90 = 0;
                    $ljt = $c->ljt ?? 30;

                    foreach ($custInvoices as $item) {
                        $rem = $item['remaining'];
                        $total_piutang += $rem;
                        $inv = $item['invoice'];

                        if (in_array(strtolower($inv->jenis_transaksi), ['k', 'kredit'])) {
                            $jatuh_tempo = Carbon::parse($inv->tanggal)->addDays($ljt)->startOfDay();
                            if ($today->greaterThan($jatuh_tempo)) {
                                // Hitung selisih hari: berapa hari sejak jatuh tempo terlewati
                                // Gunakan timestamp agar tidak ada mutasi Carbon object
                                $diff = (int) floor(($todayTs - $jatuh_tempo->timestamp) / 86400);
                                if ($diff <= 30) {
                                    $overdue_1_30 += $rem;
                                } elseif ($diff <= 60) {
                                    $overdue_31_60 += $rem;
                                } elseif ($diff <= 90) {
                                    $overdue_61_90 += $rem;
                                } else {
                                    $overdue_90 += $rem;
                                }
                            } else {
                                $belum_jt += $rem;
                            }
                        } else {
                            $belum_jt += $rem;
                        }
                    }

                    if ($total_piutang > 0 || $kode_pelanggan) {
                        $items->push([
                            'pelanggan' => $c,
                            'total_piutang' => $total_piutang,
                            'belum_jt' => $belum_jt,
                            'overdue_1_30' => $overdue_1_30,
                            'overdue_31_60' => $overdue_31_60,
                            'overdue_61_90' => $overdue_61_90,
                            'overdue_90' => $overdue_90,
                        ]);
                    }
                }
            } else {
                // detail (per unpaid invoice)
                $query = Penjualan::with(['pelanggan.wilayah'])
                    ->whereIn('jenis_transaksi', ['K', 'Kredit'])
                    ->where('batal', 0);
                
                if ($kode_pelanggan) {
                    $query->where('kode_pelanggan', $kode_pelanggan);
                }

                $invoices = $query->orderBy('tanggal', 'asc')
                    ->orderBy('no_faktur', 'asc')
                    ->get();

                $invoiceIds = $invoices->pluck('no_faktur')->toArray();

                // Pre-aggregate payments for these specific invoices
                $cashPayments = DB::table('penjualan_pembayaran')
                    ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                    ->where('status', 'disetujui')
                    ->where('jenis_bayar', '!=', 'Retur')
                    ->whereIn('no_faktur', $invoiceIds)
                    ->groupBy('no_faktur')
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                $returPayments = DB::table('penjualan_pembayaran')
                    ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                    ->where('status', 'disetujui')
                    ->where('jenis_bayar', 'Retur')
                    ->whereIn('no_faktur', $invoiceIds)
                    ->groupBy('no_faktur')
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                $transferPayments = DB::table('penjualan_pembayaran_transfer')
                    ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                    ->where('status', 'disetujui')
                    ->whereIn('no_faktur', $invoiceIds)
                    ->groupBy('no_faktur')
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                $giroPayments = DB::table('penjualan_pembayaran_giro')
                    ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                    ->where('status', 'disetujui')
                    ->whereIn('no_faktur', $invoiceIds)
                    ->groupBy('no_faktur')
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                foreach ($invoices as $inv) {
                    $cashPaid = $cashPayments[$inv->no_faktur] ?? 0;
                    $transferPaid = $transferPayments[$inv->no_faktur] ?? 0;
                    $giroPaid = $giroPayments[$inv->no_faktur] ?? 0;
                    $returPaid = $returPayments[$inv->no_faktur] ?? 0;

                    $paid = $cashPaid + $transferPaid + $giroPaid;
                    $sisa = (float)($inv->grand_total - $paid - $returPaid);
                    $sisa_piutang = $sisa < 1 ? 0.0 : $sisa;

                    if ($sisa_piutang >= 1) {
                        $ljt = $inv->pelanggan->ljt ?? 30;
                        $jatuh_tempo = Carbon::parse($inv->tanggal)->addDays($ljt);
                        $umur_piutang = (int) round(Carbon::parse($inv->tanggal)->diffInDays(Carbon::now()));
                        $status = Carbon::now()->greaterThan($jatuh_tempo) ? 'OVERDUE' : 'LANCAR';

                        $items->push([
                            'no_faktur' => $inv->no_faktur,
                            'tanggal' => $inv->tanggal,
                            'jatuh_tempo' => $jatuh_tempo,
                            'pelanggan' => $inv->pelanggan,
                            'grand_total' => $inv->grand_total,
                            'total_bayar' => $paid,
                            'total_retur' => $returPaid,
                            'sisa_piutang' => $sisa_piutang,
                            'umur_piutang' => $umur_piutang,
                            'status' => $status,
                        ]);
                    }
                }
            }
        }

        $view = $isPrintOrExcel ? 'laporan.cetak_piutang' : 'laporan.piutang';

        if ($isExcel) {
            $filename = 'laporan_piutang_' . date('Ymd_His') . '.xls';
            return response(view($view, compact('pelanggans', 'items', 'kode_pelanggan', 'jenis_laporan', 'isExcel')))
            ->header('Content-Type', 'application/vnd-ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        }

        return view($view, compact('pelanggans', 'items', 'kode_pelanggan', 'jenis_laporan'));
    }

    public function laporanRekapSisaPiutang(Request $request)
    {
        $this->authorizeReport('piutang');

        $kode_pelanggan = $request->input('kode_pelanggan');
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $wilayah_id = $request->input('wilayah_id');
        $sub_wilayah_id = $request->input('sub_wilayah_id');
        $kode_sales = $request->input('kode_sales');

        // Fetch master data for dropdown filters
        $wilayahs = Wilayah::orderBy('nama_wilayah')->get();
        $subWilayahs = SubWilayah::orderBy('nama_wilayah')->get();
        $salesmen = User::where(function ($q) {
            $q->where('role', 'sales')->orWhere('role', 'Salesman');
        })->where('status', '1')->orderBy('name')->get();

        $pelanggans = collect();
        if ($kode_pelanggan) {
            $pelanggans = Pelanggan::where('kode_pelanggan', $kode_pelanggan)->get();
        }

        $items = collect();
        $isCetak = $request->is('*/cetak');
        $isExcel = $request->is('*/excel');
        $isPrintOrExcel = $isCetak || $isExcel;

        if ($isPrintOrExcel) {
            $query = Penjualan::with(['pelanggan.wilayah', 'pelanggan.subWilayah', 'sales'])
                ->whereIn('jenis_transaksi', ['K', 'Kredit'])
                ->where('batal', 0)
                ->where('tanggal', '<=', $tanggal);
            
            if ($kode_sales) {
                $query->where('kode_sales', $kode_sales);
            }
            if ($kode_pelanggan) {
                $query->where('kode_pelanggan', $kode_pelanggan);
            }
            if ($wilayah_id) {
                $query->whereHas('pelanggan', function($q) use ($wilayah_id) {
                    $q->where('kode_wilayah', $wilayah_id);
                });
            }
            if ($sub_wilayah_id) {
                $query->whereHas('pelanggan', function($q) use ($sub_wilayah_id) {
                    $q->where('sub_wilayah', $sub_wilayah_id);
                });
            }

            $invoices = $query->orderBy('tanggal', 'asc')
                ->orderBy('no_faktur', 'asc')
                ->get();

            $invoiceIds = $invoices->pluck('no_faktur')->toArray();

            // Pre-aggregate payments for these specific invoices up to $tanggal
            $cashPayments = DB::table('penjualan_pembayaran')
                ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                ->where('status', 'disetujui')
                ->where('jenis_bayar', '!=', 'Retur')
                ->where('tanggal', '<=', $tanggal)
                ->whereIn('no_faktur', $invoiceIds)
                ->groupBy('no_faktur')
                ->pluck('total', 'no_faktur')
                ->toArray();

            $returPayments = DB::table('penjualan_pembayaran')
                ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                ->where('status', 'disetujui')
                ->where('jenis_bayar', 'Retur')
                ->where('tanggal', '<=', $tanggal)
                ->whereIn('no_faktur', $invoiceIds)
                ->groupBy('no_faktur')
                ->pluck('total', 'no_faktur')
                ->toArray();

            $transferPayments = DB::table('penjualan_pembayaran_transfer')
                ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                ->where('status', 'disetujui')
                ->where('tanggal', '<=', $tanggal)
                ->whereIn('no_faktur', $invoiceIds)
                ->groupBy('no_faktur')
                ->pluck('total', 'no_faktur')
                ->toArray();

            $giroPayments = DB::table('penjualan_pembayaran_giro')
                ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                ->where('status', 'disetujui')
                ->where('tanggal', '<=', $tanggal)
                ->whereIn('no_faktur', $invoiceIds)
                ->groupBy('no_faktur')
                ->pluck('total', 'no_faktur')
                ->toArray();

            // Pre-aggregate potong-faktur returns (linked to invoices via no_faktur)
            $potongFakturReturs = DB::table('retur_penjualan')
                ->select('no_faktur', DB::raw('SUM(total) as total'))
                ->where('tanggal', '<=', $tanggal)
                ->whereIn('no_faktur', $invoiceIds)
                ->groupBy('no_faktur')
                ->pluck('total', 'no_faktur')
                ->toArray();

            foreach ($invoices as $inv) {
                $cashPaid = $cashPayments[$inv->no_faktur] ?? 0;
                $transferPaid = $transferPayments[$inv->no_faktur] ?? 0;
                $giroPaid = $giroPayments[$inv->no_faktur] ?? 0;
                $returPaid = $returPayments[$inv->no_faktur] ?? 0;
                $pfRetur = $potongFakturReturs[$inv->no_faktur] ?? 0;

                $paid = $cashPaid + $transferPaid + $giroPaid;
                $sisa = (float)($inv->grand_total - $paid - $returPaid - $pfRetur);
                $sisa_piutang = $sisa < 1 ? 0.0 : $sisa;

                if ($sisa_piutang >= 1) {
                    $items->push([
                        'no_faktur' => $inv->no_faktur,
                        'tanggal' => $inv->tanggal,
                        'pelanggan' => $inv->pelanggan,
                        'sales' => $inv->sales,
                        'grand_total' => $inv->grand_total,
                        'total_bayar' => $paid,
                        'total_retur' => $returPaid + $pfRetur,
                        'sisa_piutang' => $sisa_piutang,
                    ]);
                }
            }
        }
        $items = $items->sortBy(function ($item) {
            return $item['pelanggan']->nama_pelanggan ?? '';
        })->values();

        $view = $isPrintOrExcel ? 'laporan.cetak_rekap_sisa_piutang' : 'laporan.rekap_sisa_piutang';

        $compactData = compact(
            'pelanggans', 'items', 'kode_pelanggan',
            'tanggal', 'wilayah_id', 'sub_wilayah_id', 'kode_sales',
            'wilayahs', 'subWilayahs', 'salesmen'
        );

        if ($isExcel) {
            $filename = 'laporan_rekap_sisa_piutang_' . date('Ymd_His') . '.xls';
            $compactData['isExcel'] = true;
            return response(view($view, $compactData))
            ->header('Content-Type', 'application/vnd-ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        }

        return view($view, $compactData);
    }

    public function laporanSetoran(Request $request)
    {
        $this->authorizeReport('setoran');

        $salesmen = User::where(function ($q) {
            $q->where('role', 'sales')->orWhere('role', 'Salesman');
        })->where('status', '1')->orderBy('name')->get();
        
        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $kode_sales = $request->input('kode_sales');
        $jenis_bayar = $request->input('jenis_bayar', 'semua');

        $items = collect();
        $isCetak = $request->is('*/cetak');
        $isExcel = $request->is('*/excel');
        $isPrintOrExcel = $isCetak || $isExcel;

        if ($isPrintOrExcel) {
            // Cash Payments
            if ($jenis_bayar === 'semua' || $jenis_bayar === 'cash') {
                $query = DB::table('penjualan_pembayaran')
                    ->join('pelanggan', 'penjualan_pembayaran.kode_pelanggan', '=', 'pelanggan.kode_pelanggan')
                    ->leftJoin('users as sales', 'penjualan_pembayaran.kode_sales', '=', 'sales.nik')
                    ->select(
                        'penjualan_pembayaran.tanggal',
                        'penjualan_pembayaran.no_bukti',
                        'penjualan_pembayaran.no_faktur',
                        'pelanggan.nama_pelanggan',
                        'sales.name as nama_sales',
                        DB::raw("'Cash' as tipe_pembayaran"),
                        'penjualan_pembayaran.jumlah',
                        'penjualan_pembayaran.keterangan'
                    )
                    ->where('penjualan_pembayaran.status', 'disetujui');
                if ($tanggal_mulai) $query->where('penjualan_pembayaran.tanggal', '>=', $tanggal_mulai);
                if ($tanggal_akhir) $query->where('penjualan_pembayaran.tanggal', '<=', $tanggal_akhir);
                if ($kode_sales) $query->where('penjualan_pembayaran.kode_sales', $kode_sales);
                
                $items = $items->concat($query->get());
            }

            // Transfer Payments
            if ($jenis_bayar === 'semua' || $jenis_bayar === 'transfer') {
                $query = DB::table('penjualan_pembayaran_transfer')
                    ->join('pelanggan', 'penjualan_pembayaran_transfer.kode_pelanggan', '=', 'pelanggan.kode_pelanggan')
                    ->leftJoin('users as sales', 'penjualan_pembayaran_transfer.kode_sales', '=', 'sales.nik')
                    ->select(
                        'penjualan_pembayaran_transfer.tanggal',
                        'penjualan_pembayaran_transfer.kode_transfer as no_bukti',
                        'penjualan_pembayaran_transfer.no_faktur',
                        'pelanggan.nama_pelanggan',
                        'sales.name as nama_sales',
                        DB::raw("'Transfer' as tipe_pembayaran"),
                        'penjualan_pembayaran_transfer.jumlah',
                        'penjualan_pembayaran_transfer.keterangan'
                    )
                    ->where('penjualan_pembayaran_transfer.status', 'disetujui');
                if ($tanggal_mulai) $query->where('penjualan_pembayaran_transfer.tanggal', '>=', $tanggal_mulai);
                if ($tanggal_akhir) $query->where('penjualan_pembayaran_transfer.tanggal', '<=', $tanggal_akhir);
                if ($kode_sales) $query->where('penjualan_pembayaran_transfer.kode_sales', $kode_sales);
                
                $items = $items->concat($query->get());
            }

            // Giro Payments
            if ($jenis_bayar === 'semua' || $jenis_bayar === 'giro') {
                $query = DB::table('penjualan_pembayaran_giro')
                    ->join('pelanggan', 'penjualan_pembayaran_giro.kode_pelanggan', '=', 'pelanggan.kode_pelanggan')
                    ->leftJoin('users as sales', 'penjualan_pembayaran_giro.kode_sales', '=', 'sales.nik')
                    ->select(
                        'penjualan_pembayaran_giro.tanggal',
                        'penjualan_pembayaran_giro.kode_giro as no_bukti',
                        'penjualan_pembayaran_giro.no_faktur',
                        'pelanggan.nama_pelanggan',
                        'sales.name as nama_sales',
                        DB::raw("'Giro' as tipe_pembayaran"),
                        'penjualan_pembayaran_giro.jumlah',
                        'penjualan_pembayaran_giro.keterangan'
                    )
                    ->where('penjualan_pembayaran_giro.status', 'disetujui');
                if ($tanggal_mulai) $query->where('penjualan_pembayaran_giro.tanggal', '>=', $tanggal_mulai);
                if ($tanggal_akhir) $query->where('penjualan_pembayaran_giro.tanggal', '<=', $tanggal_akhir);
                if ($kode_sales) $query->where('penjualan_pembayaran_giro.kode_sales', $kode_sales);
                
                $items = $items->concat($query->get());
            }

            $items = $items->sortBy('tanggal')->values();
        }

        $view = $isPrintOrExcel ? 'laporan.cetak_setoran' : 'laporan.setoran';

        if ($isExcel) {
            $filename = 'laporan_setoran_' . date('Ymd_His') . '.xls';
            return response(view($view, compact('salesmen', 'items', 'tanggal_mulai', 'tanggal_akhir', 'kode_sales', 'jenis_bayar', 'isExcel')))
            ->header('Content-Type', 'application/vnd-ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        }

        return view($view, compact('salesmen', 'items', 'tanggal_mulai', 'tanggal_akhir', 'kode_sales', 'jenis_bayar'));
    }

    public function laporanLabaRugi(Request $request)
    {
        $this->authorizeReport('laba_rugi');

        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $jenis_laporan = $request->input('jenis_laporan', 'rekap');
        $kode_supplier = $request->input('kode_supplier');

        $isCetak = $request->is('*/cetak');
        $isExcel = $request->is('*/excel');
        $isPrintOrExcel = $isCetak || $isExcel;

        // Initialize variables
        $salesGross = 0;
        $salesReturn = 0;
        $salesNet = 0;
        $hppGross = 0;
        $hppReturn = 0;
        $hppNet = 0;
        $purchaseReturn = 0;
        $profit = 0;
        $marginPercent = 0;
        $data = [];

        $suppliersList = \App\Models\Supplier::orderBy('nama_supplier')->get();
        $suppliers = $suppliersList->keyBy('kode_supplier');

        if ($isPrintOrExcel) {
            if ($jenis_laporan === 'rekap') {
                if ($kode_supplier) {
                    // Filtered rekap per supplier
                    $salesGross = (float) DB::table('penjualan_detail')
                        ->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
                        ->join('barang', 'penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                        ->where('penjualan.batal', 0)
                        ->whereBetween('penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                        ->where('barang.kode_supplier', $kode_supplier)
                        ->sum('penjualan_detail.total');

                    $salesReturn = (float) DB::table('retur_penjualan_detail')
                        ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
                        ->join('barang', 'retur_penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                        ->whereBetween('retur_penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                        ->where('barang.kode_supplier', $kode_supplier)
                        ->sum(DB::raw('retur_penjualan_detail.subtotal_retur - COALESCE(retur_penjualan_detail.total_diskon_rupiah, 0)'));

                    $hppGross = (float) DB::table('penjualan_detail')
                        ->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
                        ->join('barang', 'penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                        ->where('penjualan.batal', 0)
                        ->whereBetween('penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                        ->where('barang.kode_supplier', $kode_supplier)
                        ->sum(DB::raw('penjualan_detail.qty * penjualan_detail.harga_pokok'));

                    $hppReturn = (float) DB::table('retur_penjualan_detail')
                        ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
                        ->join('barang', 'retur_penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                        ->join('barang_satuan', 'retur_penjualan_detail.id_satuan', '=', 'barang_satuan.id')
                        ->whereBetween('retur_penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                        ->where('barang.kode_supplier', $kode_supplier)
                        ->sum(DB::raw('retur_penjualan_detail.qty * barang_satuan.harga_pokok'));

                    $purchaseReturn = (float) DB::table('retur_pembelian')
                        ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
                        ->where('kode_supplier', $kode_supplier)
                        ->sum('total');
                } else {
                    // Global rekap using detail tables for perfect consistency with per_supplier reports
                    $salesGross = (float) DB::table('penjualan_detail')
                        ->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
                        ->join('barang', 'penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                        ->where('penjualan.batal', 0)
                        ->whereBetween('penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                        ->sum('penjualan_detail.total');

                    $salesReturn = (float) DB::table('retur_penjualan_detail')
                        ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
                        ->join('barang', 'retur_penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                        ->whereBetween('retur_penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                        ->sum(DB::raw('retur_penjualan_detail.subtotal_retur - COALESCE(retur_penjualan_detail.total_diskon_rupiah, 0)'));

                    $hppGross = (float) DB::table('penjualan_detail')
                        ->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
                        ->join('barang', 'penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                        ->where('penjualan.batal', 0)
                        ->whereBetween('penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                        ->sum(DB::raw('penjualan_detail.qty * penjualan_detail.harga_pokok'));

                    $hppReturn = (float) DB::table('retur_penjualan_detail')
                        ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
                        ->join('barang', 'retur_penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                        ->join('barang_satuan', 'retur_penjualan_detail.id_satuan', '=', 'barang_satuan.id')
                        ->whereBetween('retur_penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                        ->sum(DB::raw('retur_penjualan_detail.qty * barang_satuan.harga_pokok'));

                    $purchaseReturn = (float) DB::table('retur_pembelian')
                        ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
                        ->sum('total');
                }

                $salesNet = $salesGross - $salesReturn;
                $hppNet = $hppGross - $hppReturn;
                $profit = $salesNet - $hppNet + $purchaseReturn;
                $marginPercent = $salesNet > 0 ? ($profit / $salesNet) * 100 : 0;
            } elseif ($jenis_laporan === 'per_supplier') {
                $sales = DB::table('penjualan_detail')
                    ->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
                    ->join('barang', 'penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                    ->where('penjualan.batal', 0)
                    ->whereBetween('penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->when($kode_supplier, fn($q) => $q->where('barang.kode_supplier', $kode_supplier))
                    ->select('barang.kode_supplier', DB::raw('SUM(penjualan_detail.total) as total_sales'))
                    ->groupBy('barang.kode_supplier')
                    ->get()
                    ->keyBy('kode_supplier');

                $returns = DB::table('retur_penjualan_detail')
                    ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
                    ->join('barang', 'retur_penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                    ->whereBetween('retur_penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->when($kode_supplier, fn($q) => $q->where('barang.kode_supplier', $kode_supplier))
                    ->select('barang.kode_supplier', DB::raw('SUM(retur_penjualan_detail.subtotal_retur - COALESCE(retur_penjualan_detail.total_diskon_rupiah, 0)) as total_return'))
                    ->groupBy('barang.kode_supplier')
                    ->get()
                    ->keyBy('kode_supplier');

                $hpp = DB::table('penjualan_detail')
                    ->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
                    ->join('barang', 'penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                    ->where('penjualan.batal', 0)
                    ->whereBetween('penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->when($kode_supplier, fn($q) => $q->where('barang.kode_supplier', $kode_supplier))
                    ->select('barang.kode_supplier', DB::raw('SUM(penjualan_detail.qty * penjualan_detail.harga_pokok) as total_hpp'))
                    ->groupBy('barang.kode_supplier')
                    ->get()
                    ->keyBy('kode_supplier');

                $hppReturns = DB::table('retur_penjualan_detail')
                    ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
                    ->join('barang', 'retur_penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                    ->join('barang_satuan', 'retur_penjualan_detail.id_satuan', '=', 'barang_satuan.id')
                    ->whereBetween('retur_penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->when($kode_supplier, fn($q) => $q->where('barang.kode_supplier', $kode_supplier))
                    ->select('barang.kode_supplier', DB::raw('SUM(retur_penjualan_detail.qty * barang_satuan.harga_pokok) as total_hpp_return'))
                    ->groupBy('barang.kode_supplier')
                    ->get()
                    ->keyBy('kode_supplier');

                $purchaseReturns = DB::table('retur_pembelian')
                    ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->when($kode_supplier, fn($q) => $q->where('kode_supplier', $kode_supplier))
                    ->select('kode_supplier', DB::raw('SUM(total) as total_purchase_return'))
                    ->groupBy('kode_supplier')
                    ->get()
                    ->keyBy('kode_supplier');

                foreach ($suppliers as $kode => $supplier) {
                    $sSales = (float) ($sales[$kode]->total_sales ?? 0);
                    $sReturn = (float) ($returns[$kode]->total_return ?? 0);
                    $sHppGross = (float) ($hpp[$kode]->total_hpp ?? 0);
                    $sHppReturn = (float) ($hppReturns[$kode]->total_hpp_return ?? 0);
                    $sPurchaseReturn = (float) ($purchaseReturns[$kode]->total_purchase_return ?? 0);

                    $sHppNet = $sHppGross - $sHppReturn;
                    $sProfit = ($sSales - $sReturn) - $sHppNet + $sPurchaseReturn;

                    if ($sSales != 0 || $sReturn != 0 || $sHppGross != 0 || $sPurchaseReturn != 0 || $sProfit != 0) {
                        $data[] = [
                            'kode_supplier' => $kode,
                            'nama_supplier' => $supplier->nama_supplier,
                            'jumlah_penjualan' => $sSales,
                            'retur_penjualan' => $sReturn,
                            'total_hpp' => $sHppNet,
                            'retur_pembelian' => $sPurchaseReturn,
                            'laba_kotor' => $sProfit
                        ];
                    }
                }

                usort($data, function($a, $b) {
                    return strcmp($a['nama_supplier'], $b['nama_supplier']);
                });

            } elseif ($jenis_laporan === 'per_tanggal_supplier') {
                $salesGrouped = DB::table('penjualan_detail')
                    ->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
                    ->join('barang', 'penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                    ->where('penjualan.batal', 0)
                    ->whereBetween('penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->when($kode_supplier, fn($q) => $q->where('barang.kode_supplier', $kode_supplier))
                    ->select('penjualan.tanggal', 'barang.kode_supplier', DB::raw('SUM(penjualan_detail.total) as total_sales'))
                    ->groupBy('penjualan.tanggal', 'barang.kode_supplier')
                    ->get();

                $returnsGrouped = DB::table('retur_penjualan_detail')
                    ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
                    ->join('barang', 'retur_penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                    ->whereBetween('retur_penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->when($kode_supplier, fn($q) => $q->where('barang.kode_supplier', $kode_supplier))
                    ->select('retur_penjualan.tanggal', 'barang.kode_supplier', DB::raw('SUM(retur_penjualan_detail.subtotal_retur - COALESCE(retur_penjualan_detail.total_diskon_rupiah, 0)) as total_return'))
                    ->groupBy('retur_penjualan.tanggal', 'barang.kode_supplier')
                    ->get();

                $hppGrouped = DB::table('penjualan_detail')
                    ->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
                    ->join('barang', 'penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                    ->where('penjualan.batal', 0)
                    ->whereBetween('penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->when($kode_supplier, fn($q) => $q->where('barang.kode_supplier', $kode_supplier))
                    ->select('penjualan.tanggal', 'barang.kode_supplier', DB::raw('SUM(penjualan_detail.qty * penjualan_detail.harga_pokok) as total_hpp'))
                    ->groupBy('penjualan.tanggal', 'barang.kode_supplier')
                    ->get()
                    ->keyBy(fn($item) => $item->tanggal . '_' . $item->kode_supplier);

                $hppReturnsGrouped = DB::table('retur_penjualan_detail')
                    ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
                    ->join('barang', 'retur_penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                    ->join('barang_satuan', 'retur_penjualan_detail.id_satuan', '=', 'barang_satuan.id')
                    ->whereBetween('retur_penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->when($kode_supplier, fn($q) => $q->where('barang.kode_supplier', $kode_supplier))
                    ->select('retur_penjualan.tanggal', 'barang.kode_supplier', DB::raw('SUM(retur_penjualan_detail.qty * barang_satuan.harga_pokok) as total_hpp_return'))
                    ->groupBy('retur_penjualan.tanggal', 'barang.kode_supplier')
                    ->get()
                    ->keyBy(fn($item) => $item->tanggal . '_' . $item->kode_supplier);

                $purchaseReturnsGrouped = DB::table('retur_pembelian')
                    ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->when($kode_supplier, fn($q) => $q->where('kode_supplier', $kode_supplier))
                    ->select('tanggal', 'kode_supplier', DB::raw('SUM(total) as total_purchase_return'))
                    ->groupBy('tanggal', 'kode_supplier')
                    ->get();

                $compositeData = [];

                foreach ($salesGrouped as $s) {
                    $key = $s->tanggal . '_' . $s->kode_supplier;
                    $compositeData[$key] = [
                        'tanggal' => $s->tanggal,
                        'kode_supplier' => $s->kode_supplier,
                        'nama_supplier' => $suppliers[$s->kode_supplier]->nama_supplier ?? '-',
                        'jumlah_penjualan' => (float) $s->total_sales,
                        'retur_penjualan' => 0.0,
                        'total_hpp' => 0.0,
                        'retur_pembelian' => 0.0,
                    ];
                    if (isset($hppGrouped[$key])) {
                        $compositeData[$key]['total_hpp'] = (float) $hppGrouped[$key]->total_hpp;
                    }
                }

                foreach ($returnsGrouped as $r) {
                    $key = $r->tanggal . '_' . $r->kode_supplier;
                    if (!isset($compositeData[$key])) {
                        $compositeData[$key] = [
                            'tanggal' => $r->tanggal,
                            'kode_supplier' => $r->kode_supplier,
                            'nama_supplier' => $suppliers[$r->kode_supplier]->nama_supplier ?? '-',
                            'jumlah_penjualan' => 0.0,
                            'retur_penjualan' => 0.0,
                            'total_hpp' => 0.0,
                            'retur_pembelian' => 0.0,
                        ];
                    }
                    $compositeData[$key]['retur_penjualan'] = (float) $r->total_return;
                    if (isset($hppReturnsGrouped[$key])) {
                        $compositeData[$key]['total_hpp'] -= (float) $hppReturnsGrouped[$key]->total_hpp_return;
                    }
                }

                foreach ($purchaseReturnsGrouped as $pr) {
                    $key = $pr->tanggal . '_' . $pr->kode_supplier;
                    if (!isset($compositeData[$key])) {
                        $compositeData[$key] = [
                            'tanggal' => $pr->tanggal,
                            'kode_supplier' => $pr->kode_supplier,
                            'nama_supplier' => $suppliers[$pr->kode_supplier]->nama_supplier ?? '-',
                            'jumlah_penjualan' => 0.0,
                            'retur_penjualan' => 0.0,
                            'total_hpp' => 0.0,
                            'retur_pembelian' => 0.0,
                        ];
                    }
                    $compositeData[$key]['retur_pembelian'] = (float) $pr->total_purchase_return;
                }

                foreach ($compositeData as $item) {
                    $item['laba_kotor'] = ($item['jumlah_penjualan'] - $item['retur_penjualan']) - $item['total_hpp'] + $item['retur_pembelian'];
                    if ($item['jumlah_penjualan'] != 0 || $item['retur_penjualan'] != 0 || $item['total_hpp'] != 0 || $item['retur_pembelian'] != 0 || $item['laba_kotor'] != 0) {
                        $data[] = $item;
                    }
                }

                usort($data, function($a, $b) {
                    $dateCompare = strcmp($a['tanggal'], $b['tanggal']);
                    if ($dateCompare !== 0) {
                        return $dateCompare;
                    }
                    return strcmp($a['nama_supplier'], $b['nama_supplier']);
                });

            } elseif ($jenis_laporan === 'detail') {
                $salesDetails = DB::table('penjualan_detail')
                    ->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
                    ->join('barang', 'penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                    ->leftJoin('supplier', 'barang.kode_supplier', '=', 'supplier.kode_supplier')
                    ->where('penjualan.batal', 0)
                    ->whereBetween('penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->when($kode_supplier, fn($q) => $q->where('barang.kode_supplier', $kode_supplier))
                    ->select(
                        DB::raw("'Penjualan' as tipe"),
                        'penjualan.tanggal',
                        'penjualan.no_faktur as no_transaksi',
                        'penjualan_detail.kode_barang',
                        'barang.nama_barang',
                        'supplier.nama_supplier',
                        'penjualan_detail.qty',
                        'penjualan_detail.harga',
                        'penjualan_detail.total as total_jual',
                        DB::raw('(penjualan_detail.qty * penjualan_detail.harga_pokok) as total_hpp')
                    )
                    ->get();

                $returnDetails = DB::table('retur_penjualan_detail')
                    ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
                    ->join('barang', 'retur_penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                    ->leftJoin('supplier', 'barang.kode_supplier', '=', 'supplier.kode_supplier')
                    ->join('barang_satuan', 'retur_penjualan_detail.id_satuan', '=', 'barang_satuan.id')
                    ->whereBetween('retur_penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->when($kode_supplier, fn($q) => $q->where('barang.kode_supplier', $kode_supplier))
                    ->select(
                        DB::raw("'Retur Jual' as tipe"),
                        'retur_penjualan.tanggal',
                        'retur_penjualan.no_retur as no_transaksi',
                        'retur_penjualan_detail.kode_barang',
                        'barang.nama_barang',
                        'supplier.nama_supplier',
                        DB::raw('-retur_penjualan_detail.qty as qty'),
                        'retur_penjualan_detail.harga_retur as harga',
                        DB::raw('-(retur_penjualan_detail.subtotal_retur - COALESCE(retur_penjualan_detail.total_diskon_rupiah, 0)) as total_jual'),
                        DB::raw('-(retur_penjualan_detail.qty * barang_satuan.harga_pokok) as total_hpp')
                    )
                    ->get();

                $purchaseReturnDetails = DB::table('retur_pembelian_detail')
                    ->join('retur_pembelian', 'retur_pembelian_detail.no_retur', '=', 'retur_pembelian.no_retur')
                    ->join('barang', 'retur_pembelian_detail.kode_barang', '=', 'barang.kode_barang')
                    ->leftJoin('supplier', 'retur_pembelian.kode_supplier', '=', 'supplier.kode_supplier')
                    ->whereBetween('retur_pembelian.tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->when($kode_supplier, fn($q) => $q->where('retur_pembelian.kode_supplier', $kode_supplier))
                    ->select(
                        DB::raw("'Retur Beli' as tipe"),
                        'retur_pembelian.tanggal',
                        'retur_pembelian.no_retur as no_transaksi',
                        'retur_pembelian_detail.kode_barang',
                        'barang.nama_barang',
                        'supplier.nama_supplier',
                        'retur_pembelian_detail.qty',
                        'retur_pembelian_detail.harga_retur as harga',
                        DB::raw('0 as total_jual'),
                        DB::raw('-retur_pembelian_detail.subtotal_retur as total_hpp')
                    )
                    ->get();

                foreach ($salesDetails as $sd) {
                    $data[] = [
                        'tipe' => $sd->tipe,
                        'tanggal' => $sd->tanggal,
                        'no_transaksi' => $sd->no_transaksi,
                        'kode_barang' => $sd->kode_barang,
                        'nama_barang' => $sd->nama_barang,
                        'nama_supplier' => $sd->nama_supplier ?? '-',
                        'qty' => (float) $sd->qty,
                        'harga' => (float) $sd->harga,
                        'total_jual' => (float) $sd->total_jual,
                        'total_hpp' => (float) $sd->total_hpp,
                        'laba_kotor' => (float) $sd->total_jual - (float) $sd->total_hpp
                    ];
                }

                foreach ($returnDetails as $rd) {
                    $data[] = [
                        'tipe' => $rd->tipe,
                        'tanggal' => $rd->tanggal,
                        'no_transaksi' => $rd->no_transaksi,
                        'kode_barang' => $rd->kode_barang,
                        'nama_barang' => $rd->nama_barang,
                        'nama_supplier' => $rd->nama_supplier ?? '-',
                        'qty' => (float) $rd->qty,
                        'harga' => (float) $rd->harga,
                        'total_jual' => (float) $rd->total_jual,
                        'total_hpp' => (float) $rd->total_hpp,
                        'laba_kotor' => (float) $rd->total_jual - (float) $rd->total_hpp
                    ];
                }

                foreach ($purchaseReturnDetails as $prd) {
                    $data[] = [
                        'tipe' => $prd->tipe,
                        'tanggal' => $prd->tanggal,
                        'no_transaksi' => $prd->no_transaksi,
                        'kode_barang' => $prd->kode_barang,
                        'nama_barang' => $prd->nama_barang,
                        'nama_supplier' => $prd->nama_supplier ?? '-',
                        'qty' => (float) $prd->qty,
                        'harga' => (float) $prd->harga,
                        'total_jual' => (float) $prd->total_jual,
                        'total_hpp' => (float) $prd->total_hpp,
                        'laba_kotor' => (float) $prd->total_jual - (float) $prd->total_hpp
                    ];
                }

                usort($data, function($a, $b) {
                    $dateCompare = strcmp($a['tanggal'], $b['tanggal']);
                    if ($dateCompare !== 0) {
                        return $dateCompare;
                    }
                    return strcmp($a['no_transaksi'], $b['no_transaksi']);
                });
            }
        }

        $view = $isPrintOrExcel ? 'laporan.cetak_laba_rugi' : 'laporan.laba_rugi';

        if ($isExcel) {
            $filename = 'laporan_laba_rugi_' . date('Ymd_His') . '.xls';
            return response(view($view, compact(
                'tanggal_mulai', 'tanggal_akhir', 'jenis_laporan', 'kode_supplier',
                'salesGross', 'salesReturn', 'salesNet', 'hppGross', 'hppReturn', 'hppNet', 
                'purchaseReturn', 'profit', 'marginPercent', 'data', 'suppliersList', 'isExcel'
            )))
            ->header('Content-Type', 'application/vnd-ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        }

        return view($view, compact(
            'tanggal_mulai', 'tanggal_akhir', 'jenis_laporan', 'kode_supplier',
            'salesGross', 'salesReturn', 'salesNet', 'hppGross', 'hppReturn', 'hppNet', 
            'purchaseReturn', 'profit', 'marginPercent', 'data', 'suppliersList'
        ));
    }

    private function authorizeReport($type)
    {
        $permission = 'view-laporan_' . $type;
        if (!auth()->user()->can($permission)) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }
    }
}
