<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StokOpname;
use App\Models\StokOpnameDetail;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Merk;
use Illuminate\Support\Facades\DB;

class StokOpnameController extends Controller
{
    public function index(Request $request)
    {
        $query = StokOpname::with(['user', 'details']);

        if ($request->filled('search')) {
            $query->where('no_opname', 'like', '%' . $request->search . '%')
                  ->orWhere('keterangan', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('tanggal_mulai')) {
            $query->where('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal', '<=', $request->tanggal_akhir);
        }

        $items = $query->orderBy('tanggal', 'desc')->paginate(10)->appends($request->query());
        return view('stok_opname.index', compact('items'));
    }

    public function create()
    {
        $item = new StokOpname();
        $barangs = Barang::where('status', 1)->with('satuans')->orderBy('nama_barang', 'asc')->get();
        $kategoris = Kategori::all();
        $merks = Merk::all();

        // Generate OPNM-YYYYMMDD-0001
        $today = date('Ymd');
        $last = StokOpname::where('no_opname', 'like', 'OPNM-' . $today . '%')
            ->orderBy('no_opname', 'desc')
            ->first();

        if ($last) {
            $lastNumber = intval(substr($last->no_opname, -4));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        $item->no_opname = 'OPNM-' . $today . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return view('stok_opname.form', compact('item', 'barangs', 'kategoris', 'merks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_opname' => 'required|string|unique:stok_opname,no_opname',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|exists:barang,kode_barang',
            'items.*.stok_sistem' => 'required|numeric',
            'items.*.stok_fisik' => 'required|numeric|min:0',
            'items.*.selisih' => 'required|numeric',
            'items.*.keterangan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $details = [];

            foreach ($request->items as $row) {
                // Calculate stock adjustment: add the difference (selisih)
                // e.g. system = 10, physical = 8, difference = -2
                // stok = stok + (-2) = 8
                Barang::where('kode_barang', $row['kode_barang'])->increment('stok', $row['selisih']);

                $details[] = new StokOpnameDetail([
                    'kode_barang' => $row['kode_barang'],
                    'stok_sistem' => $row['stok_sistem'],
                    'stok_fisik' => $row['stok_fisik'],
                    'selisih' => $row['selisih'],
                    'keterangan' => $row['keterangan'] ?? null,
                ]);
            }

            $opname = StokOpname::create([
                'no_opname' => $request->no_opname,
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
                'user_id' => auth()->id() ?? '1',
            ]);

            $opname->details()->saveMany($details);
        });

        return redirect()->route('stok-opname.index')->with('success', 'Transaksi stok opname berhasil disimpan');
    }

    public function show($no_opname)
    {
        $item = StokOpname::with(['user', 'details.barang'])->findOrFail($no_opname);
        return view('stok_opname.show', compact('item'));
    }

    public function edit($no_opname)
    {
        $item = StokOpname::with(['details.barang.satuans'])->findOrFail($no_opname);
        $barangs = Barang::where('status', 1)->with('satuans')->orderBy('nama_barang', 'asc')->get();
        $kategoris = Kategori::all();
        $merks = Merk::all();
        return view('stok_opname.form', compact('item', 'barangs', 'kategoris', 'merks'));
    }

    public function update(Request $request, $no_opname)
    {
        $opname = StokOpname::with('details')->findOrFail($no_opname);

        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|exists:barang,kode_barang',
            'items.*.stok_sistem' => 'required|numeric',
            'items.*.stok_fisik' => 'required|numeric|min:0',
            'items.*.selisih' => 'required|numeric',
            'items.*.keterangan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $opname) {
            // Revert old adjustments (decrement stock by old selisih)
            foreach ($opname->details as $oldDetail) {
                Barang::where('kode_barang', $oldDetail->kode_barang)->decrement('stok', $oldDetail->selisih);
            }

            $details = [];

            foreach ($request->items as $row) {
                // Apply new stock adjustment (increment stock by new selisih)
                Barang::where('kode_barang', $row['kode_barang'])->increment('stok', $row['selisih']);

                $details[] = new StokOpnameDetail([
                    'kode_barang' => $row['kode_barang'],
                    'stok_sistem' => $row['stok_sistem'],
                    'stok_fisik' => $row['stok_fisik'],
                    'selisih' => $row['selisih'],
                    'keterangan' => $row['keterangan'] ?? null,
                ]);
            }

            $opname->update([
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
            ]);

            // Recreate details
            $opname->details()->delete();
            $opname->details()->saveMany($details);
        });

        return redirect()->route('stok-opname.index')->with('success', 'Transaksi stok opname berhasil diperbarui');
    }

    public function destroy($no_opname)
    {
        $opname = StokOpname::with('details')->findOrFail($no_opname);
        DB::transaction(function () use ($opname) {
            // Revert adjustments (decrement stock by selisih)
            foreach ($opname->details as $detail) {
                Barang::where('kode_barang', $detail->kode_barang)->decrement('stok', $detail->selisih);
            }
            $opname->delete();
        });
        return redirect()->route('stok-opname.index')->with('success', 'Transaksi stok opname berhasil dihapus');
    }
}
