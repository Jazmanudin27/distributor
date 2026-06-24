<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\User;
use App\Models\Pelanggan;
use App\Models\Wilayah;
use App\Models\SubWilayah;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanKeuanganController extends Controller
{
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

        $view = $isPrintOrExcel ? 'laporan.piutang.cetak' : 'laporan.piutang.index';

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

        $view = $isPrintOrExcel ? 'laporan.piutang.cetak_rekap_sisa' : 'laporan.piutang.rekap_sisa';

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

        $view = $isPrintOrExcel ? 'laporan.setoran.cetak' : 'laporan.setoran.index';

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
                        ->where(function($q) {
                            $q->whereNull('penjualan_detail.is_promo')
                              ->orWhere('penjualan_detail.is_promo', '!=', 1);
                        })
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
                        ->where(function($q) {
                            $q->whereNull('penjualan_detail.is_promo')
                              ->orWhere('penjualan_detail.is_promo', '!=', 1);
                        })
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
                        ->where(function($q) {
                            $q->whereNull('penjualan_detail.is_promo')
                              ->orWhere('penjualan_detail.is_promo', '!=', 1);
                        })
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
                        ->where(function($q) {
                            $q->whereNull('penjualan_detail.is_promo')
                              ->orWhere('penjualan_detail.is_promo', '!=', 1);
                        })
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
                    ->where(function($q) {
                        $q->whereNull('penjualan_detail.is_promo')
                          ->orWhere('penjualan_detail.is_promo', '!=', 1);
                    })
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
                    ->where(function($q) {
                        $q->whereNull('penjualan_detail.is_promo')
                          ->orWhere('penjualan_detail.is_promo', '!=', 1);
                    })
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
                    ->where(function($q) {
                        $q->whereNull('penjualan_detail.is_promo')
                          ->orWhere('penjualan_detail.is_promo', '!=', 1);
                    })
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
                    ->where(function($q) {
                        $q->whereNull('penjualan_detail.is_promo')
                          ->orWhere('penjualan_detail.is_promo', '!=', 1);
                    })
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
                    ->where(function($q) {
                        $q->whereNull('penjualan_detail.is_promo')
                          ->orWhere('penjualan_detail.is_promo', '!=', 1);
                    })
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

        $view = $isPrintOrExcel ? 'laporan.laba_rugi.cetak' : 'laporan.laba_rugi.index';

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

    public function laporanPembayaranPiutang(Request $request)
    {
        $this->authorizeReport('piutang');

        $salesmen = User::where(function ($q) {
            $q->where('role', 'sales')->orWhere('role', 'Salesman');
        })->where('status', '1')->orderBy('name')->get();

        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $kode_sales = $request->input('kode_sales');
        $kode_pelanggan = $request->input('kode_pelanggan');
        $jenis_laporan = $request->input('jenis_laporan', 'detail'); // detail
        $status_pembayaran = $request->input('status_pembayaran', 'semua'); // lunas, belum_lunas, semua
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
            // Build queries for each payment type to get approved payments
            $cashQuery = DB::table('penjualan_pembayaran')
                ->select([
                    'id as payment_id',
                    'no_bukti as no_bukti',
                    'tanggal as tgl_bayar',
                    'no_faktur',
                    'kode_pelanggan',
                    'kode_sales',
                    DB::raw("'Cash' as metode_pembayaran"),
                    'jumlah as jml_bayar'
                ])
                ->where('status', 'disetujui');

            $transferQuery = DB::table('penjualan_pembayaran_transfer')
                ->select([
                    'kode_transfer as payment_id',
                    'kode_transfer as no_bukti',
                    'tanggal as tgl_bayar',
                    'no_faktur',
                    'kode_pelanggan',
                    'kode_sales',
                    DB::raw("COALESCE(jenis_bayar, 'Transfer') as metode_pembayaran"),
                    'jumlah as jml_bayar'
                ])
                ->where('status', 'disetujui');

            $giroQuery = DB::table('penjualan_pembayaran_giro')
                ->select([
                    'kode_giro as payment_id',
                    'kode_giro as no_bukti',
                    'tanggal as tgl_bayar',
                    'no_faktur',
                    'kode_pelanggan',
                    'kode_sales',
                    DB::raw("COALESCE(jenis_bayar, 'Giro') as metode_pembayaran"),
                    'jumlah as jml_bayar'
                ])
                ->where('status', 'disetujui');

            // Apply date filters to the payment tables directly
            if ($tanggal_mulai) {
                $cashQuery->where('tanggal', '>=', $tanggal_mulai);
                $transferQuery->where('tanggal', '>=', $tanggal_mulai);
                $giroQuery->where('tanggal', '>=', $tanggal_mulai);
            }
            if ($tanggal_akhir) {
                $cashQuery->where('tanggal', '<=', $tanggal_akhir);
                $transferQuery->where('tanggal', '<=', $tanggal_akhir);
                $giroQuery->where('tanggal', '<=', $tanggal_akhir);
            }

            // Apply salesman and customer filters
            if ($kode_sales) {
                $cashQuery->where('kode_sales', $kode_sales);
                $transferQuery->where('kode_sales', $kode_sales);
                $giroQuery->where('kode_sales', $kode_sales);
            }
            if ($kode_pelanggan) {
                $cashQuery->where('kode_pelanggan', $kode_pelanggan);
                $transferQuery->where('kode_pelanggan', $kode_pelanggan);
                $giroQuery->where('kode_pelanggan', $kode_pelanggan);
            }

            // Fetch records
            $cashPayments = $cashQuery->get();
            $transferPayments = $transferQuery->get();
            $giroPayments = $giroQuery->get();

            // Combine all payments
            $allPayments = collect();
            foreach ($cashPayments as $p) {
                $allPayments->push([
                    'tgl_bayar' => $p->tgl_bayar,
                    'no_bukti' => $p->no_bukti,
                    'no_faktur' => $p->no_faktur,
                    'kode_pelanggan' => $p->kode_pelanggan,
                    'kode_sales' => $p->kode_sales,
                    'metode_pembayaran' => $p->metode_pembayaran,
                    'jml_bayar' => (float)$p->jml_bayar,
                ]);
            }
            foreach ($transferPayments as $p) {
                $allPayments->push([
                    'tgl_bayar' => $p->tgl_bayar,
                    'no_bukti' => $p->no_bukti,
                    'no_faktur' => $p->no_faktur,
                    'kode_pelanggan' => $p->kode_pelanggan,
                    'kode_sales' => $p->kode_sales,
                    'metode_pembayaran' => $p->metode_pembayaran,
                    'jml_bayar' => (float)$p->jml_bayar,
                ]);
            }
            foreach ($giroPayments as $p) {
                $allPayments->push([
                    'tgl_bayar' => $p->tgl_bayar,
                    'no_bukti' => $p->no_bukti,
                    'no_faktur' => $p->no_faktur,
                    'kode_pelanggan' => $p->kode_pelanggan,
                    'kode_sales' => $p->kode_sales,
                    'metode_pembayaran' => $p->metode_pembayaran,
                    'jml_bayar' => (float)$p->jml_bayar,
                ]);
            }

            // Sort payments by date and invoice number
            $allPayments = $allPayments->sortBy(function ($item) {
                return $item['tgl_bayar'] . '_' . $item['no_faktur'];
            });

            if ($allPayments->isNotEmpty()) {
                $noFakturs = $allPayments->pluck('no_faktur')->unique()->filter()->toArray();
                $kodePelanggans = $allPayments->pluck('kode_pelanggan')->unique()->filter()->toArray();
                $salesniks = $allPayments->pluck('kode_sales')->unique()->filter()->toArray();

                // Batch fetch related info
                $penjualans = DB::table('penjualan')
                    ->whereIn('no_faktur', $noFakturs)
                    ->get()
                    ->keyBy('no_faktur');

                $pelanggansMap = DB::table('pelanggan')
                    ->whereIn('kode_pelanggan', $kodePelanggans)
                    ->get()
                    ->keyBy('kode_pelanggan');

                $salesMap = DB::table('users')
                    ->whereIn('nik', $salesniks)
                    ->get()
                    ->keyBy('nik');

                $kodeWilayahs = $pelanggansMap->pluck('kode_wilayah')->unique()->filter()->toArray();
                $wilayahMap = DB::table('wilayah')
                    ->whereIn('kode_wilayah', $kodeWilayahs)
                    ->get()
                    ->keyBy('kode_wilayah');

                // Pre-aggregate payment totals for status calculation
                $cashTotals = DB::table('penjualan_pembayaran')
                    ->whereIn('no_faktur', $noFakturs)
                    ->where('status', 'disetujui')
                    ->groupBy('no_faktur')
                    ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                $transferTotals = DB::table('penjualan_pembayaran_transfer')
                    ->whereIn('no_faktur', $noFakturs)
                    ->where('status', 'disetujui')
                    ->groupBy('no_faktur')
                    ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                $giroTotals = DB::table('penjualan_pembayaran_giro')
                    ->whereIn('no_faktur', $noFakturs)
                    ->where('status', 'disetujui')
                    ->groupBy('no_faktur')
                    ->select('no_faktur', DB::raw('SUM(jumlah) as total'))
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                $returTotals = DB::table('retur_penjualan')
                    ->whereIn('no_faktur', $noFakturs)
                    ->groupBy('no_faktur')
                    ->select('no_faktur', DB::raw('SUM(total) as total'))
                    ->pluck('total', 'no_faktur')
                    ->toArray();

                // Map payments into final report objects
                foreach ($allPayments as $p) {
                    $no_faktur = $p['no_faktur'];
                    $inv = $penjualans->get($no_faktur);

                    // Skip if invoice not found
                    if (!$inv) {
                        continue;
                    }

                    // Apply status_faktur filter
                    if ($status_faktur === 'aktif' && $inv->batal != 0) {
                        continue;
                    }
                    if ($status_faktur === 'batal' && $inv->batal == 0) {
                        continue;
                    }

                    $cust = $pelanggansMap->get($p['kode_pelanggan']);
                    $sales = $salesMap->get($p['kode_sales']);
                    $wil = $cust ? $wilayahMap->get($cust->kode_wilayah) : null;

                    $cTot = $cashTotals[$no_faktur] ?? 0;
                    $tTot = $transferTotals[$no_faktur] ?? 0;
                    $gTot = $giroTotals[$no_faktur] ?? 0;
                    $rTot = $returTotals[$no_faktur] ?? 0;

                    $total_bayar = (float)($cTot + $tTot + $gTot);
                    $grand_total = (float)$inv->grand_total;
                    $sisa_bayar = $grand_total - $total_bayar - $rTot;
                    if ($sisa_bayar < 1) {
                        $sisa_bayar = 0.0;
                    }

                    $status_lunas = $sisa_bayar <= 0 ? 'Lunas' : 'Belum Lunas';

                    // Apply status_pembayaran filter
                    if ($status_pembayaran === 'lunas' && $status_lunas !== 'Lunas') {
                        continue;
                    }
                    if ($status_pembayaran === 'belum_lunas' && $status_lunas !== 'Belum Lunas') {
                        continue;
                    }

                    $item = new \stdClass();
                    $item->tgl_bayar = $p['tgl_bayar'];
                    $item->tgl_faktur = $inv->tanggal;
                    $item->no_bukti = $p['no_bukti'];
                    $item->no_faktur = $no_faktur;
                    $item->kode_pelanggan = $p['kode_pelanggan'];
                    $item->nama_pelanggan = $cust ? $cust->nama_pelanggan : '-';
                    $item->sales_name = $sales ? $sales->name : '-';
                    $item->nama_wilayah = $wil ? $wil->nama_wilayah : '-';
                    $item->total_bruto = (float)$inv->total;
                    $item->total_diskon = (float)$inv->diskon;
                    $item->total_subtotal = $grand_total;
                    $item->jml_bayar = $p['jml_bayar'];
                    $item->total_bayar = $total_bayar;
                    $item->sisa_bayar = $sisa_bayar;
                    $item->status_lunas = $status_lunas;

                    $items->push($item);
                }
            }
        }

        $view = $isPrintOrExcel ? 'laporan.pembayaran_piutang.cetak' : 'laporan.pembayaran_piutang.index';

        if ($isExcel) {
            $filename = 'laporan_pembayaran_piutang_' . date('Ymd_His') . '.xls';
            return response(view($view, compact(
                'salesmen', 'items', 'tanggal_mulai', 'tanggal_akhir', 
                'kode_sales', 'kode_pelanggan', 'jenis_laporan', 'status_pembayaran', 'status_faktur', 'isExcel', 'pelanggans'
            )))
            ->header('Content-Type', 'application/vnd-ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        }

        return view($view, compact(
            'salesmen', 'items', 'tanggal_mulai', 'tanggal_akhir', 
            'kode_sales', 'kode_pelanggan', 'jenis_laporan', 'status_pembayaran', 'status_faktur', 'pelanggans'
        ));
    }

    public function laporanKasBank(Request $request)
    {
        $this->authorizeReport('kas_bank');

        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $kode_bank = $request->input('kode_bank');

        $isCetak = $request->is('*/cetak');
        $isExcel = $request->is('*/excel');
        $isPrintOrExcel = $isCetak || $isExcel;

        $banks = \App\Models\Bank::orderBy('nama_bank', 'asc')->get();

        $items = collect();
        $saldoAwal = 0;
        $totalDebet = 0;
        $totalKredit = 0;
        $saldoAkhir = 0;

        if ($isPrintOrExcel) {
            // Calculate Saldo Awal (before start date)
            $saldoAwal = (float) DB::table('keuangan_mutasi')
                ->where('tanggal', '<', $tanggal_mulai)
                ->when($kode_bank, function($q) use ($kode_bank) {
                    return $q->where('kode_bank', $kode_bank);
                })
                ->selectRaw("SUM(CASE WHEN tipe = 'debet' THEN jumlah ELSE -jumlah END) as saldo")
                ->value('saldo');

            // Fetch transactions in period
            $items = \App\Models\KeuanganMutasi::with(['bank', 'user'])
                ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
                ->when($kode_bank, function($q) use ($kode_bank) {
                    return $q->where('kode_bank', $kode_bank);
                })
                ->orderBy('tanggal', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            // Calculate running balances
            $runningBalance = $saldoAwal;
            foreach ($items as $item) {
                if ($item->tipe === 'debet') {
                    $runningBalance += (float)$item->jumlah;
                    $totalDebet += (float)$item->jumlah;
                } else {
                    $runningBalance -= (float)$item->jumlah;
                    $totalKredit += (float)$item->jumlah;
                }
                $item->saldo_berjalan = $runningBalance;
            }
            $saldoAkhir = $runningBalance;
        }

        $view = $isPrintOrExcel ? 'laporan.kas_bank.cetak' : 'laporan.kas_bank.index';

        if ($isExcel) {
            $filename = 'laporan_kas_bank_' . date('Ymd_His') . '.xls';
            return response(view($view, compact(
                'banks', 'items', 'tanggal_mulai', 'tanggal_akhir', 
                'kode_bank', 'saldoAwal', 'totalDebet', 'totalKredit', 'saldoAkhir', 'isExcel'
            )))
            ->header('Content-Type', 'application/vnd-ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        }

        return view($view, compact(
            'banks', 'items', 'tanggal_mulai', 'tanggal_akhir', 
            'kode_bank', 'saldoAwal', 'totalDebet', 'totalKredit', 'saldoAkhir'
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
