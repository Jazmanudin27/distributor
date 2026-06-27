<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\ReturPenjualan;
use App\Models\ReturPenjualanDetail;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\DB;

class LaporanPenjualanController extends Controller
{
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
                // detail (Format 2 & 3) - flat query using Query Builder
                $query = DB::table('penjualan_detail')
                    ->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
                    ->leftJoin('pelanggan', 'penjualan.kode_pelanggan', '=', 'pelanggan.kode_pelanggan')
                    ->leftJoin('wilayah', 'pelanggan.kode_wilayah', '=', 'wilayah.kode_wilayah')
                    ->leftJoin('users as sales', 'penjualan.kode_sales', '=', 'sales.nik')
                    ->leftJoin('users as input_user', 'penjualan.id_user', '=', 'input_user.id')
                    ->leftJoin('barang', 'penjualan_detail.kode_barang', '=', 'barang.kode_barang')
                    ->leftJoin('barang_satuan', 'penjualan_detail.satuan_id', '=', 'barang_satuan.id');

                if ($status_faktur === 'aktif') {
                    $query->where('penjualan.batal', 0);
                } elseif ($status_faktur === 'batal') {
                    $query->where('penjualan.batal', 1);
                }
                
                if ($tanggal_mulai) $query->where('penjualan.tanggal', '>=', $tanggal_mulai);
                if ($tanggal_akhir) $query->where('penjualan.tanggal', '<=', $tanggal_akhir);
                if ($kode_sales) $query->where('penjualan.kode_sales', $kode_sales);
                if ($kode_pelanggan) $query->where('penjualan.kode_pelanggan', $kode_pelanggan);
                if ($jenis_transaksi) $query->where('penjualan.jenis_transaksi', $jenis_transaksi);
                
                if ($kode_supplier) {
                    $query->where('barang.kode_supplier', $kode_supplier);
                }

                $items = $query->orderBy('penjualan.tanggal', 'asc')
                    ->orderBy('penjualan.no_faktur', 'asc')
                    ->select([
                        'penjualan_detail.no_faktur',
                        'penjualan_detail.kode_barang',
                        'penjualan_detail.qty',
                        'penjualan_detail.harga',
                        'penjualan_detail.diskon1_persen',
                        'penjualan_detail.diskon2_persen',
                        'penjualan_detail.diskon3_persen',
                        'penjualan_detail.total as detail_total',
                        
                        'penjualan.tanggal',
                        'penjualan.kode_pelanggan',
                        'penjualan.jenis_transaksi',
                        'penjualan.total as invoice_total',
                        'penjualan.diskon as invoice_diskon',
                        'penjualan.grand_total as invoice_grand_total',
                        'penjualan.created_at',
                        'penjualan.updated_at',
                        
                        'pelanggan.nama_pelanggan',
                        'pelanggan.alamat_pelanggan as alamat',
                        
                        'wilayah.nama_wilayah',
                        'sales.name as sales_name',
                        'input_user.name as input_user_name',
                        
                        'barang.nama_barang',
                        'barang.kategori',
                        'barang.merk',
                        'barang_satuan.satuan'
                    ])
                    ->get();

                // Pre-aggregate payments and returs for these invoices
                $invoiceIds = $items->pluck('no_faktur')->unique()->toArray();

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

                foreach ($items as $row) {
                    $cashPaid = $cashPayments[$row->no_faktur] ?? 0;
                    $transferPaid = $transferPayments[$row->no_faktur] ?? 0;
                    $giroPaid = $giroPayments[$row->no_faktur] ?? 0;
                    $paid = $cashPaid + $transferPaid + $giroPaid;
                    $returPaid = $returs[$row->no_faktur] ?? 0;

                    $row->total_bayar = $paid;
                    $row->total_retur = $returPaid;
                    $sisa = (float)($row->invoice_grand_total - $paid - $returPaid);
                    $row->sisa_bayar = $sisa < 1 ? 0.0 : $sisa;
                    $row->status_pembayaran = $row->sisa_bayar <= 0 ? 'Lunas' : 'Belum Lunas';
                }
            }
        }

        if ($isPrintOrExcel) {
            if ($jenis_laporan === 'rekap') {
                $view = 'laporan.penjualan.cetak_format1';
            } elseif ($jenis_laporan === 'detail_simple') {
                $view = 'laporan.penjualan.cetak_format3';
            } else {
                $view = 'laporan.penjualan.cetak_format2';
            }
        } else {
            $view = 'laporan.penjualan.index';
        }

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
        $kode_supplier = $request->input('kode_supplier');
        $jenis_laporan = $request->input('jenis_laporan', 'rekap');

        $suppliers = \App\Models\Supplier::orderBy('nama_supplier')->get();

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
                if ($kode_supplier) {
                    $query->whereExists(function ($q) use ($kode_supplier) {
                        $q->select(DB::raw(1))
                          ->from('retur_penjualan_detail as rpd')
                          ->join('barang as b', 'b.kode_barang', '=', 'rpd.kode_barang')
                          ->whereColumn('rpd.no_retur', 'retur_penjualan.no_retur')
                          ->where('b.kode_supplier', $kode_supplier);
                    });
                }

                $items = $query->orderBy('tanggal', 'asc')->orderBy('no_retur', 'asc')->get();
            } else {
                // detail
                $query = ReturPenjualanDetail::with(['returPenjualan.pelanggan', 'returPenjualan.sales', 'barang', 'barang.supplier', 'barangSatuan'])
                    ->whereHas('returPenjualan', function ($q) use ($tanggal_mulai, $tanggal_akhir, $kode_pelanggan) {
                        if ($tanggal_mulai) $q->where('tanggal', '>=', $tanggal_mulai);
                        if ($tanggal_akhir) $q->where('tanggal', '<=', $tanggal_akhir);
                        if ($kode_pelanggan) $q->where('kode_pelanggan', $kode_pelanggan);
                    });

                if ($kode_supplier) {
                    $query->whereHas('barang', function ($q) use ($kode_supplier) {
                        $q->where('kode_supplier', $kode_supplier);
                    });
                }

                $items = $query->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
                    ->orderBy('retur_penjualan.tanggal', 'asc')
                    ->orderBy('retur_penjualan.no_retur', 'asc')
                    ->select('retur_penjualan_detail.*')
                    ->get();
            }
        }

        $view = $isPrintOrExcel ? 'laporan.retur_penjualan.cetak' : 'laporan.retur_penjualan.index';

        if ($isExcel) {
            $filename = 'laporan_retur_penjualan_' . date('Ymd_His') . '.xls';
            return response(view($view, compact(
                'pelanggans', 'suppliers', 'items', 'tanggal_mulai', 'tanggal_akhir', 
                'kode_pelanggan', 'kode_supplier', 'jenis_laporan', 'isExcel'
            )))
            ->header('Content-Type', 'application/vnd-ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        }

        return view($view, compact(
            'pelanggans', 'suppliers', 'items', 'tanggal_mulai', 'tanggal_akhir', 
            'kode_pelanggan', 'kode_supplier', 'jenis_laporan'
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
