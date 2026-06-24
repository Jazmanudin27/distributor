<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\ReturPembelian;
use App\Models\ReturPembelianDetail;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class LaporanPembelianController extends Controller
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
                $query = Pembelian::with(['supplier', 'details', 'pembayarans']);
                
                if ($tanggal_mulai) {
                    $query->where('tanggal', '>=', $tanggal_mulai);
                }
                if ($tanggal_akhir) {
                    $query->where('tanggal', '<=', $tanggal_akhir);
                }
                if ($kode_supplier) {
                    $query->where('kode_supplier', $kode_supplier);
                }

                $rawItems = $query->orderBy('tanggal', 'asc')->orderBy('no_faktur', 'asc')->get();

                // Compute payments & returs
                $invoiceIds = $rawItems->pluck('no_faktur')->toArray();

                $returs = DB::table('retur_pembelian')
                    ->select('no_faktur', DB::raw('SUM(total) as total'))
                    ->whereIn('no_faktur', $invoiceIds)
                    ->where('jenis_retur', 'PF')
                    ->groupBy('no_faktur')
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                foreach ($rawItems as $invoice) {
                    $bruto = (float)$invoice->details->sum('total');
                    $paid = (float)$invoice->pembayarans->sum('jumlah');
                    $returPaid = (float)($returs[$invoice->no_faktur] ?? 0);

                    $invoice->bruto = $bruto;
                    $invoice->total_bayar = $paid;
                    $invoice->total_retur = $returPaid;
                    $sisa = (float)($invoice->grand_total - $paid - $returPaid);
                    $invoice->sisa_bayar = $sisa < 1 ? 0.0 : $sisa;
                    $invoice->status_pembayaran = $invoice->sisa_bayar <= 0 ? 'Lunas' : 'Belum Lunas';
                }

                if ($group_by_supplier === '1') {
                    $items = $rawItems->groupBy('kode_supplier');
                } else {
                    $items = $rawItems;
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

        $view = $isPrintOrExcel ? 'laporan.pembelian.cetak' : 'laporan.pembelian.index';

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

        $view = $isPrintOrExcel ? 'laporan.retur_pembelian.cetak' : 'laporan.retur_pembelian.index';

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

    private function authorizeReport($type)
    {
        $permission = 'view-laporan_' . $type;
        if (!auth()->user()->can($permission)) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }
    }
}
