<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenjualanKiriman;
use App\Models\Penjualan;
use App\Models\Wilayah;
use Illuminate\Support\Facades\DB;

class PenjualanKirimanController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->hasRole('Super Admin') || auth()->user()->can('view-penjualan_kiriman')) {
            // Authorized
        } else {
            abort(403, 'Unauthorized action.');
        }

        $wilayahs = Wilayah::orderBy('nama_wilayah')->get();

        $query = DB::table('penjualan_kiriman')
            ->join('wilayah', 'penjualan_kiriman.kode_wilayah', '=', 'wilayah.kode_wilayah')
            ->join('penjualan', 'penjualan_kiriman.no_faktur', '=', 'penjualan.no_faktur')
            ->select(
                'penjualan_kiriman.tanggal',
                'penjualan_kiriman.kode_wilayah',
                'penjualan_kiriman.kirimanke',
                'wilayah.nama_wilayah',
                DB::raw('COUNT(penjualan_kiriman.no_faktur) as total_faktur'),
                DB::raw('SUM(penjualan.grand_total) as total_nominal'),
                DB::raw('MAX(penjualan_kiriman.keterangan) as keterangan'),
                DB::raw('MAX(penjualan_kiriman.driver_name) as driver_name'),
                DB::raw('MAX(penjualan_kiriman.status) as status')
            )
            ->groupBy(
                'penjualan_kiriman.tanggal',
                'penjualan_kiriman.kode_wilayah',
                'penjualan_kiriman.kirimanke',
                'wilayah.nama_wilayah'
            );

        if ($request->filled('tanggal_mulai')) {
            $query->where('penjualan_kiriman.tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('penjualan_kiriman.tanggal', '<=', $request->tanggal_akhir);
        }
        if ($request->filled('kode_wilayah')) {
            $query->where('penjualan_kiriman.kode_wilayah', $request->kode_wilayah);
        }

        $items = $query->orderBy('penjualan_kiriman.tanggal', 'desc')
            ->orderBy('wilayah.nama_wilayah', 'asc')
            ->orderBy('penjualan_kiriman.kirimanke', 'asc')
            ->paginate(15)
            ->appends($request->query());

        return view('penjualan_kiriman.index', compact('items', 'wilayahs'));
    }

    public function create()
    {
        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->can('create-penjualan_kiriman')) {
            abort(403, 'Unauthorized action.');
        }

        $wilayahs = Wilayah::orderBy('nama_wilayah')->get();
        $tanggal = date('Y-m-d');
        $isEdit = false;
        $shipmentInvoices = collect();

        return view('penjualan_kiriman.form', compact('isEdit', 'wilayahs', 'tanggal', 'shipmentInvoices'));
    }

    public function getInvoices(Request $request)
    {
        $kode_wilayah = $request->input('kode_wilayah');
        $is_edit = $request->input('is_edit');
        $current_tanggal = $request->input('current_tanggal');
        $current_kode_wilayah = $request->input('current_kode_wilayah');
        $current_kirimanke = $request->input('current_kirimanke');

        $query = Penjualan::with(['pelanggan.wilayah', 'sales'])
            ->where('batal', 0);

        if ($kode_wilayah) {
            $query->whereHas('pelanggan', function ($q) use ($kode_wilayah) {
                $q->where('kode_wilayah', $kode_wilayah);
            });
        }

        $currentInvoices = [];
        if ($is_edit) {
            $currentInvoices = PenjualanKiriman::where('tanggal', $current_tanggal)
                ->where('kode_wilayah', $current_kode_wilayah)
                ->where('kirimanke', $current_kirimanke)
                ->pluck('no_faktur')
                ->toArray();

            $query->where(function ($q) use ($currentInvoices) {
                $q->whereNotIn('no_faktur', function ($sq) {
                    $sq->select('no_faktur')->from('penjualan_kiriman');
                })->orWhereIn('no_faktur', $currentInvoices);
            });
        } else {
            $query->whereNotIn('no_faktur', function ($q) {
                $q->select('no_faktur')->from('penjualan_kiriman');
            });
        }

        if ($request->filled('tanggal_mulai')) {
            $query->where('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal', '<=', $request->tanggal_akhir);
        }

        $invoices = $query->orderBy('tanggal', 'desc')->get();

        return response()->json([
            'invoices' => $invoices,
            'checked' => $currentInvoices
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->can('create-penjualan_kiriman')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'tanggal' => 'required|date',
            'kode_wilayah' => 'required|exists:wilayah,kode_wilayah',
            'keterangan' => 'nullable|string',
            'driver_name' => 'nullable|string|max:100',
            'no_kendaraan' => 'nullable|string|max:20',
            'status' => 'required|in:proses,kirim,selesai,batal',
            'invoices' => 'required|array|min:1',
            'invoices.*' => 'required|exists:penjualan,no_faktur',
        ]);

        // Calculate kirimanke automatically
        $lastKirim = PenjualanKiriman::where('tanggal', $request->tanggal)
            ->where('kode_wilayah', $request->kode_wilayah)
            ->max('kirimanke');
        $kirimanke = $lastKirim ? ($lastKirim + 1) : 1;

        DB::transaction(function () use ($request, $kirimanke) {
            foreach ($request->invoices as $no_faktur) {
                PenjualanKiriman::create([
                    'tanggal' => $request->tanggal,
                    'kode_wilayah' => $request->kode_wilayah,
                    'no_faktur' => $no_faktur,
                    'keterangan' => $request->keterangan,
                    'kirimanke' => $kirimanke,
                    'driver_name' => $request->driver_name,
                    'no_kendaraan' => $request->no_kendaraan,
                    'status' => $request->status,
                ]);

                Penjualan::where('no_faktur', $no_faktur)->update([
                    'tanggal_kirim' => $request->tanggal
                ]);
            }
        });

        return redirect()->route('penjualan-kiriman.index')->with('success', 'Rekap kiriman berhasil dibuat.');
    }

    public function edit(Request $request)
    {
        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->can('edit-penjualan_kiriman')) {
            abort(403, 'Unauthorized action.');
        }

        $tanggal = $request->input('tanggal');
        $kode_wilayah = $request->input('kode_wilayah');
        $kirimanke = $request->input('kirimanke', 1);

        if (!$tanggal || !$kode_wilayah) {
            return redirect()->route('penjualan-kiriman.index')->with('error', 'Parameter tidak lengkap.');
        }

        $wilayah = Wilayah::where('kode_wilayah', $kode_wilayah)->first();
        if (!$wilayah) {
            return redirect()->route('penjualan-kiriman.index')->with('error', 'Wilayah tidak ditemukan.');
        }

        $currentInvoices = PenjualanKiriman::where('tanggal', $tanggal)
            ->where('kode_wilayah', $kode_wilayah)
            ->where('kirimanke', $kirimanke)
            ->pluck('no_faktur')
            ->toArray();

        $shipmentRow = PenjualanKiriman::where('tanggal', $tanggal)
            ->where('kode_wilayah', $kode_wilayah)
            ->where('kirimanke', $kirimanke)
            ->first();

        $keterangan = $shipmentRow ? $shipmentRow->keterangan : '';
        $driver_name = $shipmentRow ? $shipmentRow->driver_name : '';
        $no_kendaraan = $shipmentRow ? $shipmentRow->no_kendaraan : '';
        $status = $shipmentRow ? $shipmentRow->status : 'proses';
        $nama_penerima = $shipmentRow ? $shipmentRow->nama_penerima : '';
        $foto_penerima = $shipmentRow ? $shipmentRow->foto_penerima : '';

        $allInvoices = Penjualan::with(['pelanggan.wilayah', 'sales'])
            ->where('batal', 0)
            ->whereHas('pelanggan', function ($q) use ($kode_wilayah) {
                $q->where('kode_wilayah', $kode_wilayah);
            })
            ->where(function ($q) use ($currentInvoices) {
                $q->whereNotIn('no_faktur', function ($sq) {
                    $sq->select('no_faktur')->from('penjualan_kiriman');
                })->orWhereIn('no_faktur', $currentInvoices);
            })
            ->orderBy('tanggal', 'desc')
            ->get();

        $shipmentInvoices = Penjualan::with(['pelanggan.wilayah', 'sales'])
            ->whereIn('no_faktur', $currentInvoices)
            ->get();

        $isEdit = true;
        $wilayahs = Wilayah::orderBy('nama_wilayah')->get();

        return view('penjualan_kiriman.form', compact('isEdit', 'tanggal', 'kode_wilayah', 'kirimanke', 'wilayah', 'currentInvoices', 'allInvoices', 'keterangan', 'wilayahs', 'shipmentInvoices', 'driver_name', 'no_kendaraan', 'status', 'nama_penerima', 'foto_penerima'));
    }

    public function update(Request $request)
    {
        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->can('edit-penjualan_kiriman')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'current_tanggal' => 'required|date',
            'current_kode_wilayah' => 'required|integer',
            'current_kirimanke' => 'required|integer',
            'tanggal' => 'required|date',
            'kode_wilayah' => 'required|exists:wilayah,kode_wilayah',
            'keterangan' => 'nullable|string',
            'driver_name' => 'nullable|string|max:100',
            'no_kendaraan' => 'nullable|string|max:20',
            'status' => 'required|in:proses,kirim,selesai,batal',
            'nama_penerima' => 'nullable|string|max:100',
            'foto_penerima' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'invoices' => 'required|array|min:1',
            'invoices.*' => 'required|exists:penjualan,no_faktur',
        ]);

        $fotoPath = $request->input('old_foto_penerima');
        if ($request->hasFile('foto_penerima')) {
            $file = $request->file('foto_penerima');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            // Ensure directory exists
            if (!file_exists(public_path('storage/uploads/kiriman'))) {
                mkdir(public_path('storage/uploads/kiriman'), 0777, true);
            }
            $file->move(public_path('storage/uploads/kiriman'), $filename);
            $fotoPath = 'storage/uploads/kiriman/' . $filename;
        }

        $kirimanke = $request->current_kirimanke;

        // If they change date or wilayah, recalculate the kirimanke for that new target
        if ($request->tanggal != $request->current_tanggal || $request->kode_wilayah != $request->current_kode_wilayah) {
            $lastKirim = PenjualanKiriman::where('tanggal', $request->tanggal)
                ->where('kode_wilayah', $request->kode_wilayah)
                ->max('kirimanke');
            $kirimanke = $lastKirim ? ($lastKirim + 1) : 1;
        }

        DB::transaction(function () use ($request, $kirimanke, $fotoPath) {
            // Set old invoices tanggal_kirim to null
            $oldInvoices = PenjualanKiriman::where('tanggal', $request->current_tanggal)
                ->where('kode_wilayah', $request->current_kode_wilayah)
                ->where('kirimanke', $request->current_kirimanke)
                ->pluck('no_faktur')
                ->toArray();

            Penjualan::whereIn('no_faktur', $oldInvoices)->update(['tanggal_kirim' => null]);

            // Delete old kiriman rows
            PenjualanKiriman::where('tanggal', $request->current_tanggal)
                ->where('kode_wilayah', $request->current_kode_wilayah)
                ->where('kirimanke', $request->current_kirimanke)
                ->delete();

            // Insert new rows
            foreach ($request->invoices as $no_faktur) {
                PenjualanKiriman::create([
                    'tanggal' => $request->tanggal,
                    'kode_wilayah' => $request->kode_wilayah,
                    'no_faktur' => $no_faktur,
                    'keterangan' => $request->keterangan,
                    'kirimanke' => $kirimanke,
                    'driver_name' => $request->driver_name,
                    'no_kendaraan' => $request->no_kendaraan,
                    'status' => $request->status,
                    'nama_penerima' => $request->nama_penerima,
                    'foto_penerima' => $fotoPath,
                ]);

                Penjualan::where('no_faktur', $no_faktur)->update([
                    'tanggal_kirim' => $request->tanggal
                ]);
            }
        });

        return redirect()->route('penjualan-kiriman.index')->with('success', 'Rekap kiriman berhasil diperbarui.');
    }

    public function destroy(Request $request)
    {
        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->can('delete-penjualan_kiriman')) {
            abort(403, 'Unauthorized action.');
        }

        $tanggal = $request->input('tanggal');
        $kode_wilayah = $request->input('kode_wilayah');
        $kirimanke = $request->input('kirimanke', 1);

        if (!$tanggal || !$kode_wilayah) {
            return redirect()->route('penjualan-kiriman.index')->with('error', 'Parameter tidak lengkap.');
        }

        DB::transaction(function () use ($tanggal, $kode_wilayah, $kirimanke) {
            $oldInvoices = PenjualanKiriman::where('tanggal', $tanggal)
                ->where('kode_wilayah', $kode_wilayah)
                ->where('kirimanke', $kirimanke)
                ->pluck('no_faktur')
                ->toArray();

            Penjualan::whereIn('no_faktur', $oldInvoices)->update(['tanggal_kirim' => null]);

            PenjualanKiriman::where('tanggal', $tanggal)
                ->where('kode_wilayah', $kode_wilayah)
                ->where('kirimanke', $kirimanke)
                ->delete();
        });

        return redirect()->route('penjualan-kiriman.index')->with('success', 'Rekap kiriman berhasil dihapus.');
    }

    public function cetakRekap(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $kode_wilayah = $request->input('kode_wilayah');
        $kirimanke = $request->input('kirimanke', 1);

        $wilayah = Wilayah::where('kode_wilayah', $kode_wilayah)->first();
        if (!$wilayah) {
            abort(404, 'Wilayah tidak ditemukan');
        }

        $invoices = DB::table('penjualan_kiriman')
            ->join('penjualan', 'penjualan_kiriman.no_faktur', '=', 'penjualan.no_faktur')
            ->join('pelanggan', 'penjualan.kode_pelanggan', '=', 'pelanggan.kode_pelanggan')
            ->leftJoin('users as sales', 'penjualan.kode_sales', '=', 'sales.nik')
            ->where('penjualan_kiriman.tanggal', $tanggal)
            ->where('penjualan_kiriman.kode_wilayah', $kode_wilayah)
            ->where('penjualan_kiriman.kirimanke', $kirimanke)
            ->select(
                'penjualan_kiriman.no_faktur',
                'penjualan.tanggal',
                'pelanggan.nama_pelanggan',
                'sales.name as nama_sales',
                'penjualan.grand_total',
                'penjualan_kiriman.keterangan'
            )
            ->orderBy('pelanggan.nama_pelanggan', 'asc')
            ->get();

        return view('penjualan_kiriman.print_rekap', compact('tanggal', 'wilayah', 'kirimanke', 'invoices'));
    }

    public function cetakBarang(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $kode_wilayah = $request->input('kode_wilayah');
        $kirimanke = $request->input('kirimanke', 1);

        $wilayah = Wilayah::where('kode_wilayah', $kode_wilayah)->first();
        if (!$wilayah) {
            abort(404, 'Wilayah tidak ditemukan');
        }

        // Left Column: Grouped items/barang
        $details = DB::table('penjualan_kiriman')
            ->join('penjualan_detail', 'penjualan_kiriman.no_faktur', '=', 'penjualan_detail.no_faktur')
            ->join('barang', 'penjualan_detail.kode_barang', '=', 'barang.kode_barang')
            ->join('barang_satuan', 'penjualan_detail.satuan_id', '=', 'barang_satuan.id')
            ->where('penjualan_kiriman.tanggal', $tanggal)
            ->where('penjualan_kiriman.kode_wilayah', $kode_wilayah)
            ->where('penjualan_kiriman.kirimanke', $kirimanke)
            ->select(
                'penjualan_detail.kode_barang',
                'barang.nama_barang',
                'barang_satuan.satuan',
                DB::raw('SUM(penjualan_detail.qty) as total_qty')
            )
            ->groupBy('penjualan_detail.kode_barang', 'barang.nama_barang', 'barang_satuan.satuan')
            ->orderBy('barang.nama_barang', 'asc')
            ->get();

        // Right Column: Rekap Faktur
        $invoices = DB::table('penjualan_kiriman')
            ->join('penjualan', 'penjualan_kiriman.no_faktur', '=', 'penjualan.no_faktur')
            ->join('pelanggan', 'penjualan.kode_pelanggan', '=', 'pelanggan.kode_pelanggan')
            ->where('penjualan_kiriman.tanggal', $tanggal)
            ->where('penjualan_kiriman.kode_wilayah', $kode_wilayah)
            ->where('penjualan_kiriman.kirimanke', $kirimanke)
            ->select(
                'penjualan_kiriman.no_faktur',
                'penjualan.tanggal',
                'pelanggan.nama_pelanggan',
                'penjualan.grand_total'
            )
            ->orderBy('pelanggan.nama_pelanggan', 'asc')
            ->get();

        return view('penjualan_kiriman.print_barang', compact('tanggal', 'wilayah', 'kirimanke', 'details', 'invoices'));
    }
}
