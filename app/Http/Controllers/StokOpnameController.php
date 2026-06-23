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
            'items.*.stok_fisik' => 'nullable|numeric|min:0',
            'items.*.selisih' => 'nullable|numeric',
            'items.*.keterangan' => 'nullable|string',
        ]);

        $hasActive = false;
        if ($request->has('items')) {
            foreach ($request->items as $row) {
                if (isset($row['selisih']) && $row['selisih'] !== '' && $row['selisih'] !== null && (float)$row['selisih'] != 0.0) {
                    $hasActive = true;
                    break;
                }
            }
        }

        if (!$hasActive) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['items' => 'Minimal harus ada 1 item barang yang memiliki selisih stok (selisih tidak sama dengan 0)!']);
        }

        DB::transaction(function () use ($request) {
            $details = [];

            foreach ($request->items as $row) {
                if (!isset($row['selisih']) || $row['selisih'] === '' || $row['selisih'] === null || (float)$row['selisih'] == 0.0) {
                    continue;
                }

                // Calculate stock adjustment: add the difference (selisih)
                // e.g. system = 10, physical = 8, difference = -2
                // stok = stok + (-2) = 8
                $selisih = (float)$row['selisih'];
                $qtyMasuk = $selisih > 0 ? $selisih : 0;
                $qtyKeluar = $selisih < 0 ? abs($selisih) : 0;

                \App\Models\StokMutasi::log(
                    $row['kode_barang'],
                    $request->tanggal,
                    'Stok Opname',
                    $request->no_opname,
                    $qtyMasuk,
                    $qtyKeluar,
                    auth()->id(),
                    $row['keterangan'] ?? $request->keterangan
                );

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
            'items.*.stok_fisik' => 'nullable|numeric|min:0',
            'items.*.selisih' => 'nullable|numeric',
            'items.*.keterangan' => 'nullable|string',
        ]);

        $hasActive = false;
        if ($request->has('items')) {
            foreach ($request->items as $row) {
                if (isset($row['selisih']) && $row['selisih'] !== '' && $row['selisih'] !== null && (float)$row['selisih'] != 0.0) {
                    $hasActive = true;
                    break;
                }
            }
        }

        if (!$hasActive) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['items' => 'Minimal harus ada 1 item barang yang memiliki selisih stok (selisih tidak sama dengan 0)!']);
        }

        DB::transaction(function () use ($request, $opname) {
            // Revert old adjustments (decrement stock by old selisih)
            foreach ($opname->details as $oldDetail) {
                $oldSelisih = (float)$oldDetail->selisih;
                $qtyMasuk = $oldSelisih < 0 ? abs($oldSelisih) : 0;
                $qtyKeluar = $oldSelisih > 0 ? $oldSelisih : 0;

                \App\Models\StokMutasi::log(
                    $oldDetail->kode_barang,
                    $request->tanggal,
                    'Batal Stok Opname (Edit)',
                    $opname->no_opname,
                    $qtyMasuk,
                    $qtyKeluar,
                    auth()->id(),
                    'Reversi stok opname sebelum edit'
                );
            }

            $details = [];

            foreach ($request->items as $row) {
                if (!isset($row['selisih']) || $row['selisih'] === '' || $row['selisih'] === null || (float)$row['selisih'] == 0.0) {
                    continue;
                }

                // Apply new stock adjustment (increment stock by new selisih)
                $selisih = (float)$row['selisih'];
                $qtyMasuk = $selisih > 0 ? $selisih : 0;
                $qtyKeluar = $selisih < 0 ? abs($selisih) : 0;

                \App\Models\StokMutasi::log(
                    $row['kode_barang'],
                    $request->tanggal,
                    'Stok Opname',
                    $opname->no_opname,
                    $qtyMasuk,
                    $qtyKeluar,
                    auth()->id(),
                    $row['keterangan'] ?? $request->keterangan
                );

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
                $selisih = (float)$detail->selisih;
                $qtyMasuk = $selisih < 0 ? abs($selisih) : 0;
                $qtyKeluar = $selisih > 0 ? $selisih : 0;

                \App\Models\StokMutasi::log(
                    $detail->kode_barang,
                    $opname->tanggal,
                    'Batal Stok Opname',
                    $opname->no_opname,
                    $qtyMasuk,
                    $qtyKeluar,
                    auth()->id(),
                    'Pembatalan/penghapusan stok opname'
                );
            }
            $opname->delete();
        });
        return redirect()->route('stok-opname.index')->with('success', 'Transaksi stok opname berhasil dihapus');
    }
}
