<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\ReturPembelian;
use App\Models\ReturPembelianDetail;
use App\Models\StokOpname;
use App\Models\StokOpnameDetail;
use App\Models\Barang;
use App\Models\Supplier;
use App\Models\Kategori;
use App\Models\Merk;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\ReturPenjualan;
use App\Models\ReturPenjualanDetail;
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

                // Bulk queries grouped by product to prevent N+1 queries
                $purchasesAfterGroup = PembelianDetail::whereIn('kode_barang', $barangIds)
                    ->whereHas('pembelian', function($q) use ($tanggal_akhir) {
                        $q->where('tanggal', '>', $tanggal_akhir);
                    })->with('barangSatuan')->get()->groupBy('kode_barang');

                $returnsAfterGroup = ReturPembelianDetail::whereIn('kode_barang', $barangIds)
                    ->whereHas('returPembelian', function($q) use ($tanggal_akhir) {
                        $q->where('tanggal', '>', $tanggal_akhir);
                    })->with('barangSatuan')->get()->groupBy('kode_barang');

                $opnameAfterGroup = StokOpnameDetail::whereIn('kode_barang', $barangIds)
                    ->whereHas('stokOpname', function($q) use ($tanggal_akhir) {
                        $q->where('tanggal', '>', $tanggal_akhir);
                    })->get()->groupBy('kode_barang');

                $purchasesPeriodGroup = PembelianDetail::whereIn('kode_barang', $barangIds)
                    ->whereHas('pembelian', function($q) use ($tanggal_mulai, $tanggal_akhir) {
                        $q->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);
                    })->with('barangSatuan')->get()->groupBy('kode_barang');

                $opnamePeriodGroup = StokOpnameDetail::whereIn('kode_barang', $barangIds)
                    ->whereHas('stokOpname', function($q) use ($tanggal_mulai, $tanggal_akhir) {
                        $q->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);
                    })->get()->groupBy('kode_barang');

                $returnsPeriodGroup = ReturPembelianDetail::whereIn('kode_barang', $barangIds)
                    ->whereHas('returPembelian', function($q) use ($tanggal_mulai, $tanggal_akhir) {
                        $q->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);
                    })->with('barangSatuan')->get()->groupBy('kode_barang');

                // === TAMBAHAN: Penjualan & Retur Penjualan ===
                // After period (untuk mundur hitung stok awal)
                $salesAfterGroup = PenjualanDetail::whereIn('kode_barang', $barangIds)
                    ->whereHas('penjualan', function($q) use ($tanggal_akhir) {
                        $q->where('tanggal', '>', $tanggal_akhir)->where('batal', 0);
                    })->with('barangSatuan')->get()->groupBy('kode_barang');

                $returSalesAfterGroup = ReturPenjualanDetail::whereIn('kode_barang', $barangIds)
                    ->whereHas('returPenjualan', function($q) use ($tanggal_akhir) {
                        $q->where('tanggal', '>', $tanggal_akhir);
                    })->with('barangSatuan')->get()->groupBy('kode_barang');

                // Within period
                $salesPeriodGroup = PenjualanDetail::whereIn('kode_barang', $barangIds)
                    ->whereHas('penjualan', function($q) use ($tanggal_mulai, $tanggal_akhir) {
                        $q->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])->where('batal', 0);
                    })->with('barangSatuan')->get()->groupBy('kode_barang');

                $returSalesPeriodGroup = ReturPenjualanDetail::whereIn('kode_barang', $barangIds)
                    ->whereHas('returPenjualan', function($q) use ($tanggal_mulai, $tanggal_akhir) {
                        $q->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);
                    })->with('barangSatuan')->get()->groupBy('kode_barang');

                foreach ($barangs as $b) {
                    $kb = $b->kode_barang;
                    $baseSatuan = $b->satuans->sortBy('isi')->first();
                    $baseSatuanName = $baseSatuan ? $baseSatuan->satuan : 'PCS';
                    $hargaPokok = $baseSatuan ? (float)$baseSatuan->harga_pokok : 0;
                    $hargaJual = $baseSatuan ? (float)$baseSatuan->harga_jual : 0;

                    $currentStock = (float)$b->stok;

                    // Transactions AFTER the target period (tanggal_akhir to now)
                    $pAfterList = $purchasesAfterGroup->get($kb) ?? collect();
                    $purchasesAfter = $pAfterList->sum(function($d) {
                        return (float)$d->qty * (float)($d->barangSatuan->isi ?? 1);
                    });

                    $rAfterList = $returnsAfterGroup->get($kb) ?? collect();
                    $returnsAfter = $rAfterList->sum(function($d) {
                        return (float)$d->qty * (float)($d->barangSatuan->isi ?? 1);
                    });

                    $oAfterList = $opnameAfterGroup->get($kb) ?? collect();
                    $opnameAfter = $oAfterList->sum(function($d) {
                        return (float)$d->selisih;
                    });

                    $sAfterList = $salesAfterGroup->get($kb) ?? collect();
                    $salesAfter = $sAfterList->sum(function($d) {
                        return (float)$d->qty * (float)($d->barangSatuan->isi ?? 1);
                    });

                    $rsAfterList = $returSalesAfterGroup->get($kb) ?? collect();
                    $returSalesAfter = $rsAfterList->sum(function($d) {
                        return (float)$d->qty * (float)($d->barangSatuan->isi ?? 1);
                    });

                    // Stok Akhir periode = stok sekarang dikurangi/tambah semua transaksi setelah periode
                    // Setelah tanggal_akhir: pembelian +, retur_pembelian -, opname +/-, penjualan -, retur_penjualan +
                    $stokAkhir = $currentStock
                        - $purchasesAfter   // pembelian setelah periode menambah stok sekarang → kurangkan
                        + $returnsAfter     // retur pembelian setelah periode mengurangi stok sekarang → tambahkan
                        - $opnameAfter      // opname neto setelah periode
                        + $salesAfter       // penjualan setelah periode mengurangi stok sekarang → tambahkan balik
                        - $returSalesAfter; // retur penjualan setelah periode menambah stok sekarang → kurangkan

                    // Transactions WITHIN the period
                    $pPeriodList = $purchasesPeriodGroup->get($kb) ?? collect();
                    $pembelianPeriod = $pPeriodList->sum(function($d) {
                        return (float)$d->qty * (float)($d->barangSatuan->isi ?? 1);
                    });

                    $oPeriodList = $opnamePeriodGroup->get($kb) ?? collect();
                    $opnameMasukPeriod = (float)$oPeriodList->where('selisih', '>', 0)->sum('selisih');
                    $opnameKeluarPeriod = (float)abs($oPeriodList->where('selisih', '<', 0)->sum('selisih'));

                    $rPeriodList = $returnsPeriodGroup->get($kb) ?? collect();
                    $returBeliPeriod = $rPeriodList->sum(function($d) {
                        return (float)$d->qty * (float)($d->barangSatuan->isi ?? 1);
                    });

                    $sPeriodList = $salesPeriodGroup->get($kb) ?? collect();
                    $penjualanPeriod = $sPeriodList->sum(function($d) {
                        return (float)$d->qty * (float)($d->barangSatuan->isi ?? 1);
                    });

                    $rsPeriodList = $returSalesPeriodGroup->get($kb) ?? collect();
                    $returJualPeriod = $rsPeriodList->sum(function($d) {
                        return (float)$d->qty * (float)($d->barangSatuan->isi ?? 1);
                    });

                    $penerimaanTotal = $pembelianPeriod + $returJualPeriod + $opnameMasukPeriod;
                    $pengeluaranTotal = $penjualanPeriod + $returBeliPeriod + $opnameKeluarPeriod;

                    // Stok Awal of the period
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
                    $currentStock = (float)$barang->stok;

                    // Calculate movements from start date to now to determine Stok Awal
                    // Stok Awal = current_stock - (Purchases + Positive Opname + Sales Returns) + (Purchase Returns + Absolute Negative Opname + Sales)
                    $purchasesAfter = PembelianDetail::where('kode_barang', $kode_barang)
                        ->whereHas('pembelian', function($q) use ($tanggal_mulai) {
                            $q->where('tanggal', '>=', $tanggal_mulai);
                        })->with('barangSatuan')->get()->sum(function($d) {
                            return (float)$d->qty * (float)($d->barangSatuan->isi ?? 1);
                        });

                    $returnsAfter = ReturPembelianDetail::where('kode_barang', $kode_barang)
                        ->whereHas('returPembelian', function($q) use ($tanggal_mulai) {
                            $q->where('tanggal', '>=', $tanggal_mulai);
                        })->with('barangSatuan')->get()->sum(function($d) {
                            return (float)$d->qty * (float)($d->barangSatuan->isi ?? 1);
                        });

                    $opnameAfter = StokOpnameDetail::where('kode_barang', $kode_barang)
                        ->whereHas('stokOpname', function($q) use ($tanggal_mulai) {
                            $q->where('tanggal', '>=', $tanggal_mulai);
                        })->get()->sum('selisih');

                    $salesAfter = PenjualanDetail::where('kode_barang', $kode_barang)
                        ->whereHas('penjualan', function($q) use ($tanggal_mulai) {
                            $q->where('tanggal', '>=', $tanggal_mulai)->where('batal', 0);
                        })->with('barangSatuan')->get()->sum(function($d) {
                            return (float)$d->qty * (float)($d->barangSatuan->isi ?? 1);
                        });

                    $returSalesAfter = ReturPenjualanDetail::where('kode_barang', $kode_barang)
                        ->whereHas('returPenjualan', function($q) use ($tanggal_mulai) {
                            $q->where('tanggal', '>=', $tanggal_mulai);
                        })->with('barangSatuan')->get()->sum(function($d) {
                            return (float)$d->qty * (float)($d->barangSatuan->isi ?? 1);
                        });

                    $stokAwal = $currentStock - $purchasesAfter + $returnsAfter - $opnameAfter + $salesAfter - $returSalesAfter;

                    // Retrieve movements within range
                    $purchasesInRange = PembelianDetail::with(['pembelian.supplier', 'barangSatuan'])
                        ->where('kode_barang', $kode_barang)
                        ->whereHas('pembelian', function($q) use ($tanggal_mulai, $tanggal_akhir) {
                            $q->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);
                        })->get();

                    $returnsInRange = ReturPembelianDetail::with(['returPembelian.supplier', 'barangSatuan'])
                        ->where('kode_barang', $kode_barang)
                        ->whereHas('returPembelian', function($q) use ($tanggal_mulai, $tanggal_akhir) {
                            $q->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);
                        })->get();

                    $opnamesInRange = StokOpnameDetail::with(['stokOpname.user'])
                        ->where('kode_barang', $kode_barang)
                        ->whereHas('stokOpname', function($q) use ($tanggal_mulai, $tanggal_akhir) {
                            $q->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);
                        })->get();

                    $salesInRange = PenjualanDetail::with(['penjualan.pelanggan.wilayah', 'penjualan.sales', 'barangSatuan'])
                        ->where('kode_barang', $kode_barang)
                        ->whereHas('penjualan', function($q) use ($tanggal_mulai, $tanggal_akhir) {
                            $q->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])->where('batal', 0);
                        })->get();

                    $returSalesInRange = ReturPenjualanDetail::with(['returPenjualan.pelanggan.wilayah', 'returPenjualan.sales', 'barangSatuan'])
                        ->where('kode_barang', $kode_barang)
                        ->whereHas('returPenjualan', function($q) use ($tanggal_mulai, $tanggal_akhir) {
                            $q->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);
                        })->get();

                    // Map movements
                    foreach ($purchasesInRange as $p) {
                        $qty_pcs = (float)$p->qty * (float)($p->barangSatuan->isi ?? 1);
                        $movements->push([
                            'tanggal' => $p->pembelian->tanggal,
                            'no_referensi' => $p->no_faktur,
                            'jenis' => 'Pembelian',
                            'keterangan' => 'Pembelian',
                            'pelanggan' => $p->pembelian->supplier->nama_supplier ?? '-',
                            'wilayah' => '-',
                            'kode_sales' => '-',
                            'nama_sales' => '-',
                            'pembelian_masuk' => $qty_pcs,
                            'retur_jual' => 0,
                            'opname_masuk' => 0,
                            'penjualan_keluar' => 0,
                            'retur_beli' => 0,
                            'opname_keluar' => 0,
                            'masuk' => $qty_pcs,
                            'keluar' => 0,
                            'satuan_qty' => $p->qty . ' ' . $p->satuan
                        ]);
                    }

                    foreach ($returnsInRange as $r) {
                        $qty_pcs = (float)$r->qty * (float)($r->barangSatuan->isi ?? 1);
                        $movements->push([
                            'tanggal' => $r->returPembelian->tanggal,
                            'no_referensi' => $r->no_retur,
                            'jenis' => 'Retur Pembelian',
                            'keterangan' => 'Retur Pembelian',
                            'pelanggan' => $r->returPembelian->supplier->nama_supplier ?? '-',
                            'wilayah' => '-',
                            'kode_sales' => '-',
                            'nama_sales' => '-',
                            'pembelian_masuk' => 0,
                            'retur_jual' => 0,
                            'opname_masuk' => 0,
                            'penjualan_keluar' => 0,
                            'retur_beli' => $qty_pcs,
                            'opname_keluar' => 0,
                            'masuk' => 0,
                            'keluar' => $qty_pcs,
                            'satuan_qty' => $r->qty . ' ' . ($r->barangSatuan->satuan ?? 'PCS')
                        ]);
                    }

                    foreach ($opnamesInRange as $o) {
                        $selisih = (float)$o->selisih;
                        $movements->push([
                            'tanggal' => $o->stokOpname->tanggal,
                            'no_referensi' => $o->no_opname,
                            'jenis' => 'Stok Opname',
                            'keterangan' => 'Stok Opname (' . ($o->stokOpname->keterangan ?? 'Penyesuaian Fisik') . ')',
                            'pelanggan' => $o->stokOpname->user->name ?? '-',
                            'wilayah' => '-',
                            'kode_sales' => '-',
                            'nama_sales' => '-',
                            'pembelian_masuk' => 0,
                            'retur_jual' => 0,
                            'opname_masuk' => $selisih > 0 ? $selisih : 0,
                            'penjualan_keluar' => 0,
                            'retur_beli' => 0,
                            'opname_keluar' => $selisih < 0 ? abs($selisih) : 0,
                            'masuk' => $selisih > 0 ? $selisih : 0,
                            'keluar' => $selisih < 0 ? abs($selisih) : 0,
                            'satuan_qty' => ($selisih > 0 ? '+' : '') . $selisih . ' PCS'
                        ]);
                    }

                    foreach ($salesInRange as $s) {
                        $qty_pcs = (float)$s->qty * (float)($s->barangSatuan->isi ?? 1);
                        $movements->push([
                            'tanggal' => $s->penjualan->tanggal,
                            'no_referensi' => $s->no_faktur,
                            'jenis' => 'Penjualan',
                            'keterangan' => 'Penjualan',
                            'pelanggan' => $s->penjualan->pelanggan->nama_pelanggan ?? '-',
                            'wilayah' => $s->penjualan->pelanggan->wilayah->nama_wilayah ?? '-',
                            'kode_sales' => $s->penjualan->kode_sales ?? '-',
                            'nama_sales' => $s->penjualan->sales->name ?? '-',
                            'pembelian_masuk' => 0,
                            'retur_jual' => 0,
                            'opname_masuk' => 0,
                            'penjualan_keluar' => $qty_pcs,
                            'retur_beli' => 0,
                            'opname_keluar' => 0,
                            'masuk' => 0,
                            'keluar' => $qty_pcs,
                            'satuan_qty' => $s->qty . ' ' . ($s->barangSatuan->satuan ?? 'PCS')
                        ]);
                    }

                    foreach ($returSalesInRange as $rs) {
                        $qty_pcs = (float)$rs->qty * (float)($rs->barangSatuan->isi ?? 1);
                        $movements->push([
                            'tanggal' => $rs->returPenjualan->tanggal,
                            'no_referensi' => $rs->no_retur,
                            'jenis' => 'Retur Penjualan',
                            'keterangan' => 'Retur Penjualan',
                            'pelanggan' => $rs->returPenjualan->pelanggan->nama_pelanggan ?? '-',
                            'wilayah' => $rs->returPenjualan->pelanggan->wilayah->nama_wilayah ?? '-',
                            'kode_sales' => $rs->returPenjualan->kode_sales ?? '-',
                            'nama_sales' => $rs->returPenjualan->sales->name ?? '-',
                            'pembelian_masuk' => 0,
                            'retur_jual' => $qty_pcs,
                            'opname_masuk' => 0,
                            'penjualan_keluar' => 0,
                            'retur_beli' => 0,
                            'opname_keluar' => 0,
                            'masuk' => $qty_pcs,
                            'keluar' => 0,
                            'satuan_qty' => $rs->qty . ' ' . ($rs->barangSatuan->satuan ?? 'PCS')
                        ]);
                    }

                    // Sort chronological
                    $movements = $movements->sortBy(function($m) {
                        return $m['tanggal'] . '_' . $m['no_referensi'];
                    })->values();

                    // Calculate running balance
                    $running = $stokAwal;
                    $movements = $movements->map(function($m) use (&$running) {
                        $running = $running + $m['masuk'] - $m['keluar'];
                        $m['saldo'] = $running;
                        return $m;
                    });

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

        $salesmen = \App\Models\User::where('role', 'sales')->orWhere('role', 'Salesman')->orderBy('name')->get();
        
        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $kode_sales = $request->input('kode_sales');
        $kode_pelanggan = $request->input('kode_pelanggan');
        $jenis_laporan = $request->input('jenis_laporan', 'rekap');

        $pelanggans = collect();
        if ($kode_pelanggan) {
            $pelanggans = \App\Models\Pelanggan::where('kode_pelanggan', $kode_pelanggan)->get();
        }

        $items = collect();
        $isCetak = $request->is('*/cetak');
        $isExcel = $request->is('*/excel');
        $isPrintOrExcel = $isCetak || $isExcel;

        if ($isPrintOrExcel) {
            if ($jenis_laporan === 'rekap') {
                $query = Penjualan::with(['pelanggan.wilayah', 'sales', 'user'])
                    ->where('batal', 0);
                
                if ($tanggal_mulai) $query->where('tanggal', '>=', $tanggal_mulai);
                if ($tanggal_akhir) $query->where('tanggal', '<=', $tanggal_akhir);
                if ($kode_sales) $query->where('kode_sales', $kode_sales);
                if ($kode_pelanggan) $query->where('kode_pelanggan', $kode_pelanggan);

                $items = $query->orderBy('tanggal', 'asc')->orderBy('no_faktur', 'asc')->get();
            } else {
                // detail
                $query = \App\Models\PenjualanDetail::with(['penjualan.pelanggan.wilayah', 'penjualan.sales', 'barang', 'barangSatuan'])
                    ->whereHas('penjualan', function ($q) use ($tanggal_mulai, $tanggal_akhir, $kode_sales, $kode_pelanggan) {
                        $q->where('batal', 0);
                        if ($tanggal_mulai) $q->where('tanggal', '>=', $tanggal_mulai);
                        if ($tanggal_akhir) $q->where('tanggal', '<=', $tanggal_akhir);
                        if ($kode_sales) $q->where('kode_sales', $kode_sales);
                        if ($kode_pelanggan) $q->where('kode_pelanggan', $kode_pelanggan);
                    });

                $items = $query->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
                    ->orderBy('penjualan.tanggal', 'asc')
                    ->orderBy('penjualan.no_faktur', 'asc')
                    ->select('penjualan_detail.*')
                    ->get();
            }
        }

        $view = $isPrintOrExcel ? 'laporan.cetak_penjualan' : 'laporan.penjualan';

        if ($isExcel) {
            $filename = 'laporan_penjualan_' . date('Ymd_His') . '.xls';
            return response(view($view, compact(
                'salesmen', 'pelanggans', 'items', 'tanggal_mulai', 'tanggal_akhir', 
                'kode_sales', 'kode_pelanggan', 'jenis_laporan', 'isExcel'
            )))
            ->header('Content-Type', 'application/vnd-ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        }

        return view($view, compact(
            'salesmen', 'pelanggans', 'items', 'tanggal_mulai', 'tanggal_akhir', 
            'kode_sales', 'kode_pelanggan', 'jenis_laporan'
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
            $pelanggans = \App\Models\Pelanggan::where('kode_pelanggan', $kode_pelanggan)->get();
        }

        $items = collect();
        $isCetak = $request->is('*/cetak');
        $isExcel = $request->is('*/excel');
        $isPrintOrExcel = $isCetak || $isExcel;

        if ($isPrintOrExcel) {
            if ($jenis_laporan === 'rekap') {
                $query = \App\Models\ReturPenjualan::with(['pelanggan', 'sales']);
                
                if ($tanggal_mulai) $query->where('tanggal', '>=', $tanggal_mulai);
                if ($tanggal_akhir) $query->where('tanggal', '<=', $tanggal_akhir);
                if ($kode_pelanggan) $query->where('kode_pelanggan', $kode_pelanggan);

                $items = $query->orderBy('tanggal', 'asc')->orderBy('no_retur', 'asc')->get();
            } else {
                // detail
                $query = \App\Models\ReturPenjualanDetail::with(['returPenjualan.pelanggan', 'barang', 'barangSatuan'])
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
        $jenis_laporan = $request->input('jenis_laporan', 'rekap_sisa_piutang');
        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $wilayah_id = $request->input('wilayah_id');
        $sub_wilayah_id = $request->input('sub_wilayah_id');
        $kode_sales = $request->input('kode_sales');

        // Fetch master data for dropdown filters
        $wilayahs = \App\Models\Wilayah::orderBy('nama_wilayah')->get();
        $subWilayahs = \App\Models\SubWilayah::orderBy('nama_wilayah')->get();
        $salesmen = \App\Models\User::where('role', 'sales')->orWhere('role', 'Salesman')->orderBy('name')->get();

        $pelanggans = collect();
        if ($kode_pelanggan) {
            $pelanggans = \App\Models\Pelanggan::where('kode_pelanggan', $kode_pelanggan)->get();
        }

        $items = collect();
        $isCetak = $request->is('*/cetak');
        $isExcel = $request->is('*/excel');
        $isPrintOrExcel = $isCetak || $isExcel;

        if ($isPrintOrExcel) {
            if ($jenis_laporan === 'rekap_sisa_piutang') {
                $query = \App\Models\Penjualan::with(['pelanggan.wilayah', 'pelanggan.subWilayah', 'sales'])
                    ->whereIn('jenis_transaksi', ['K', 'Kredit'])
                    ->where('batal', 0);
                
                if ($tanggal_mulai) {
                    $query->where('tanggal', '>=', $tanggal_mulai);
                }
                if ($tanggal_akhir) {
                    $query->where('tanggal', '<=', $tanggal_akhir);
                }
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
                    $sisa_piutang = (float)($inv->grand_total - $paid - $returPaid);

                    if ($sisa_piutang > 0.01) {
                        $items->push([
                            'no_faktur' => $inv->no_faktur,
                            'tanggal' => $inv->tanggal,
                            'pelanggan' => $inv->pelanggan,
                            'sales' => $inv->sales,
                            'grand_total' => $inv->grand_total,
                            'total_bayar' => $paid,
                            'total_retur' => $returPaid,
                            'sisa_piutang' => $sisa_piutang,
                        ]);
                    }
                }
            } elseif ($jenis_laporan === 'rekap') {
                $query = \App\Models\Pelanggan::with('wilayah');
                if ($kode_pelanggan) {
                    $query->where('kode_pelanggan', $kode_pelanggan);
                }
                if ($wilayah_id) {
                    $query->where('kode_wilayah', $wilayah_id);
                }
                if ($sub_wilayah_id) {
                    $query->where('sub_wilayah', $sub_wilayah_id);
                }
                $customers = $query->orderBy('nama_pelanggan')->get();
                $customerIds = $customers->pluck('kode_pelanggan')->toArray();

                // Pre-aggregate all unpaid/outstanding invoices and payments
                $invoicesQuery = DB::table('penjualan')
                    ->select('no_faktur', 'tanggal', 'kode_pelanggan', 'grand_total', 'jenis_transaksi')
                    ->where('batal', 0)
                    ->whereIn('kode_pelanggan', $customerIds);
                
                if ($tanggal_mulai) {
                    $invoicesQuery->where('tanggal', '>=', $tanggal_mulai);
                }
                if ($tanggal_akhir) {
                    $invoicesQuery->where('tanggal', '<=', $tanggal_akhir);
                }
                if ($kode_sales) {
                    $invoicesQuery->where('kode_sales', $kode_sales);
                }

                $invoices = $invoicesQuery->get();
                $invoiceIds = $invoices->pluck('no_faktur')->toArray();

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

                $invoicesByCustomer = [];
                foreach ($invoices as $inv) {
                    $paid = ($cashPayments[$inv->no_faktur] ?? 0) + ($transferPayments[$inv->no_faktur] ?? 0) + ($giroPayments[$inv->no_faktur] ?? 0);
                    $returPaid = $returPayments[$inv->no_faktur] ?? 0;
                    $remaining = (float)$inv->grand_total - $paid - $returPaid;

                    if ($remaining > 0.01) {
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
                    $ljt = $c->ljt ?? 14;

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
                $query = \App\Models\Pelanggan::with('wilayah');
                if ($kode_pelanggan) {
                    $query->where('kode_pelanggan', $kode_pelanggan);
                }
                if ($wilayah_id) {
                    $query->where('kode_wilayah', $wilayah_id);
                }
                if ($sub_wilayah_id) {
                    $query->where('sub_wilayah', $sub_wilayah_id);
                }
                $customers = $query->orderBy('nama_pelanggan')->get();
                $customerIds = $customers->pluck('kode_pelanggan')->toArray();

                // Pre-aggregate all unpaid/outstanding invoices and payments
                $invoicesQuery = DB::table('penjualan')
                    ->select('no_faktur', 'tanggal', 'kode_pelanggan', 'grand_total', 'jenis_transaksi')
                    ->where('batal', 0)
                    ->whereIn('kode_pelanggan', $customerIds);
                
                if ($tanggal_mulai) {
                    $invoicesQuery->where('tanggal', '>=', $tanggal_mulai);
                }
                if ($tanggal_akhir) {
                    $invoicesQuery->where('tanggal', '<=', $tanggal_akhir);
                }
                if ($kode_sales) {
                    $invoicesQuery->where('kode_sales', $kode_sales);
                }

                $invoices = $invoicesQuery->get();
                $invoiceIds = $invoices->pluck('no_faktur')->toArray();

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

                $invoicesByCustomer = [];
                foreach ($invoices as $inv) {
                    $paid = ($cashPayments[$inv->no_faktur] ?? 0) + ($transferPayments[$inv->no_faktur] ?? 0) + ($giroPayments[$inv->no_faktur] ?? 0);
                    $returPaid = $returPayments[$inv->no_faktur] ?? 0;
                    $remaining = (float)$inv->grand_total - $paid - $returPaid;

                    if ($remaining > 0.01) {
                        $invoicesByCustomer[$inv->kode_pelanggan][] = [
                            'invoice' => $inv,
                            'remaining' => $remaining
                        ];
                    }
                }

                $today = Carbon::today();
                $todayTs = $today->timestamp;
                foreach ($customers as $c) {
                    $custInvoices = $invoicesByCustomer[$c->kode_pelanggan] ?? [];
                    $total_piutang = 0;
                    $belum_jt = 0;
                    $overdue_1_30 = 0;
                    $overdue_31_60 = 0;
                    $overdue_61_90 = 0;
                    $overdue_90 = 0;
                    $ljt = $c->ljt ?? 14;

                    foreach ($custInvoices as $item) {
                        $rem = $item['remaining'];
                        $total_piutang += $rem;
                        $inv = $item['invoice'];

                        if (in_array(strtolower($inv->jenis_transaksi), ['k', 'kredit'])) {
                            $jatuh_tempo = Carbon::parse($inv->tanggal)->addDays($ljt)->startOfDay();
                            if ($today->greaterThan($jatuh_tempo)) {
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
                $query = \App\Models\Penjualan::with(['pelanggan.wilayah'])
                    ->whereIn('jenis_transaksi', ['K', 'Kredit'])
                    ->where('batal', 0);
                
                if ($kode_pelanggan) {
                    $query->where('kode_pelanggan', $kode_pelanggan);
                }
                if ($tanggal_mulai) {
                    $query->where('tanggal', '>=', $tanggal_mulai);
                }
                if ($tanggal_akhir) {
                    $query->where('tanggal', '<=', $tanggal_akhir);
                }
                if ($kode_sales) {
                    $query->where('kode_sales', $kode_sales);
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
                    $sisa_piutang = (float)($inv->grand_total - $paid - $returPaid);

                    if ($sisa_piutang > 0.01) {
                        $ljt = $inv->pelanggan->ljt ?? 14;
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

        $compactData = compact(
            'pelanggans', 'items', 'kode_pelanggan', 'jenis_laporan',
            'tanggal_mulai', 'tanggal_akhir', 'wilayah_id', 'sub_wilayah_id', 'kode_sales',
            'wilayahs', 'subWilayahs', 'salesmen'
        );

        if ($isExcel) {
            $filename = 'laporan_piutang_' . date('Ymd_His') . '.xls';
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

        $salesmen = \App\Models\User::where('role', 'sales')->orWhere('role', 'Salesman')->orderBy('name')->get();
        
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
        $this->authorizeReport('laporan_laba_rugi');

        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));

        $isCetak = $request->is('*/cetak');
        $isExcel = $request->is('*/excel');
        $isPrintOrExcel = $isCetak || $isExcel;

        $salesGross = 0;
        $salesReturn = 0;
        $salesNet = 0;
        $hppGross = 0;
        $hppReturn = 0;
        $hppNet = 0;
        $profit = 0;
        $marginPercent = 0;

        if ($isPrintOrExcel) {
            // 1. Penjualan Kotor
            $salesGross = (float) \App\Models\Penjualan::where('batal', 0)
                ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
                ->sum('grand_total');

            // 2. Retur Penjualan
            $salesReturn = (float) DB::table('retur_penjualan')
                ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
                ->sum('total');

            // 3. Penjualan Bersih
            $salesNet = $salesGross - $salesReturn;

            // 4. HPP Penjualan (qty * harga_pokok)
            $hppGross = (float) DB::table('penjualan_detail')
                ->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
                ->where('penjualan.batal', 0)
                ->whereBetween('penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                ->select(DB::raw('SUM(penjualan_detail.qty * penjualan_detail.harga_pokok) as total_hpp'))
                ->first()->total_hpp ?? 0;

            // 5. HPP Retur Penjualan (qty * barang_satuan.harga_pokok)
            $hppReturn = (float) DB::table('retur_penjualan_detail')
                ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
                ->join('barang_satuan', 'retur_penjualan_detail.id_satuan', '=', 'barang_satuan.id')
                ->whereBetween('retur_penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
                ->select(DB::raw('SUM(retur_penjualan_detail.qty * barang_satuan.harga_pokok) as total_hpp'))
                ->first()->total_hpp ?? 0;

            if ($hppReturn == 0 && $salesReturn > 0 && $salesGross > 0) {
                $hppReturn = $salesReturn * ($hppGross / $salesGross);
            }

            // 6. HPP Bersih
            $hppNet = $hppGross - $hppReturn;

            // 7. Laba Kotor
            $profit = $salesNet - $hppNet;

            // 8. Margin Laba
            $marginPercent = $salesNet > 0 ? ($profit / $salesNet) * 100 : 0;
        }

        $view = $isPrintOrExcel ? 'laporan.cetak_laba_rugi' : 'laporan.laba_rugi';

        if ($isExcel) {
            $filename = 'laporan_laba_rugi_' . date('Ymd_His') . '.xls';
            return response(view($view, compact(
                'tanggal_mulai', 'tanggal_akhir', 'salesGross', 'salesReturn', 
                'salesNet', 'hppGross', 'hppReturn', 'hppNet', 'profit', 'marginPercent', 'isExcel'
            )))
            ->header('Content-Type', 'application/vnd-ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        }

        return view($view, compact(
            'tanggal_mulai', 'tanggal_akhir', 'salesGross', 'salesReturn', 
            'salesNet', 'hppGross', 'hppReturn', 'hppNet', 'profit', 'marginPercent'
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
