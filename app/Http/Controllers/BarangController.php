<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Merk;
use App\Models\Supplier;
use App\Models\BarangSatuan;


class BarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->search . '%')
                    ->orWhere('kode_barang', 'like', '%' . $request->search . '%')
                    ->orWhere('kode_item', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('merk')) {
            $query->where('merk', $request->merk);
        }

        if ($request->filled('kode_supplier')) {
            $query->where('kode_supplier', $request->kode_supplier);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate(10)->appends($request->query());
        $kategoris = Kategori::all();
        $merks = Merk::all();
        $suppliers = Supplier::where('status', 1)->get();

        return view('master.barang.index', compact('items', 'kategoris', 'merks', 'suppliers'));
    }

    public function editHargaMasal(Request $request)
    {
        $query = Barang::query()->with('satuans');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->search . '%')
                   ->orWhere('kode_barang', 'like', '%' . $request->search . '%')
                   ->orWhere('kode_item', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('merk')) {
            $query->where('merk', $request->merk);
        }

        if ($request->filled('kode_supplier')) {
            $query->where('kode_supplier', $request->kode_supplier);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Show up to 25 products per page (each can contain multiple unit cards)
        $items = $query->orderBy('kode_barang', 'asc')->paginate(25)->appends($request->query());

        $kategoris = Kategori::all();
        $merks = Merk::all();
        $suppliers = Supplier::where('status', 1)->get();

        return view('master.barang.update-harga-masal', compact('items', 'kategoris', 'merks', 'suppliers'));
    }

    public function updateHargaMasal(Request $request)
    {
        $request->validate([
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'exists:barang_satuan,id',
            'harga_pokok' => 'required|array',
            'harga_jual' => 'required|array',
        ]);

        $selectedIds = $request->input('selected_ids');
        $hargaPokok = $request->input('harga_pokok');
        $hargaJual = $request->input('harga_jual');

        $updatedCount = 0;
        foreach ($selectedIds as $id) {
            $satuan = BarangSatuan::find($id);
            if ($satuan) {
                // Ensure inputs are clean numbers by parsing formatted values if needed
                $newPokok = (float)str_replace(['.', ','], ['', '.'], $hargaPokok[$id] ?? 0);
                $newJual = (float)str_replace(['.', ','], ['', '.'], $hargaJual[$id] ?? 0);

                $satuan->update([
                    'harga_pokok' => $newPokok,
                    'harga_jual' => $newJual
                ]);
                $updatedCount++;
            }
        }

        return redirect()->back()->with('success', "Berhasil memperbarui harga $updatedCount satuan barang secara masal.");
    }

    public function create()
    {
        $item = new Barang();

        // Auto-generate kode_barang (Format: BRG000001)
        $lastBarang = Barang::where('kode_barang', 'like', 'BRG%')->orderBy('kode_barang', 'desc')->first();
        if ($lastBarang) {
            $lastNumber = intval(substr($lastBarang->kode_barang, 3));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        $item->kode_barang = 'BRG' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        $kategoris = Kategori::all();
        $merks = Merk::all();
        $suppliers = Supplier::where('status', 1)->get();

        return view('master.barang.form', compact('item', 'kategoris', 'merks', 'suppliers'));
    }

    public function store(Request $request)
    {
        // Auto-generate kode_barang on save to prevent race conditions
        $lastBarang = Barang::where('kode_barang', 'like', 'BRG%')->orderBy('kode_barang', 'desc')->first();
        if ($lastBarang) {
            $lastNumber = intval(substr($lastBarang->kode_barang, 3));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        $kode_barang = 'BRG' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        $request->merge(['kode_barang' => $kode_barang]);

        Barang::create($request->validate([
            'kode_barang' => 'required|unique:barang,kode_barang',
            'nama_barang' => 'required',
            'kategori' => 'required',
            'merk' => 'required',
            'kode_supplier' => 'required',
            'keterangan' => 'nullable',
            'stok_min' => 'required|numeric',
            'status' => 'required',
            'kode_item' => 'nullable|string'
        ]));

        return redirect()->route('barang.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function show($id)
    {
        $item = Barang::with(['supplier', 'satuans'])->findOrFail($id);
        return view('master.barang.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Barang::findOrFail($id);
        $kategoris = Kategori::all();
        $merks = Merk::all();
        $suppliers = Supplier::where('status', 1)->get();

        return view('master.barang.form', compact('item', 'kategoris', 'merks', 'suppliers'));
    }

    public function update(Request $request, $id)
    {
        $row = Barang::findOrFail($id);

        $row->update($request->validate([
            'nama_barang' => 'required',
            'kategori' => 'required',
            'merk' => 'required',
            'kode_supplier' => 'required',
            'keterangan' => 'nullable',
            'stok_min' => 'required|numeric',
            'status' => 'required',
            'kode_item' => 'nullable|string'
        ]));

        return redirect()->route('barang.index')->with('success', 'Data berhasil diubah');
    }

    public function destroy($id)
    {
        Barang::findOrFail($id)->delete();
        return redirect()->route('barang.index')->with('success', 'Data berhasil dihapus');
    }

    public function search(Request $request)
    {
        $search = $request->input('q');

        $query = Barang::where('status', 1)
            ->with('satuans');

        // Apply sales product restriction
        $user = auth()->user();
        $salesmanNik = $request->input('kode_sales') ?: ($user ? $user->nik : null);
        $tanggal = $request->input('tanggal');
        $isCanvas = false;
        $activeCanvasDetails = collect();
        if ($salesmanNik && \App\Services\CanvasService::isCanvasSalesman($salesmanNik)) {
            $isCanvas = true;
            $session = \App\Services\CanvasService::getActiveSession($salesmanNik, $tanggal);
            if ($session) {
                $activeCanvasDetails = $session->details->keyBy('kode_barang');
                $query->whereIn('kode_barang', $activeCanvasDetails->keys()->toArray());
            } else {
                $query->whereIn('kode_barang', []);
            }
        }

        if ($request->input('has_stock') == 1 && !$isCanvas) {
            $query->where('stok', '>', 0);
        }

        if ($user) {
            if ($user->jenis_sales === 'kategori' && $user->jenis_barang) {
                $categories = array_map('trim', explode(',', $user->jenis_barang));
                $query->whereIn('kategori', $categories);
            } elseif ($user->jenis_sales === 'merk' && $user->jenis_barang) {
                $brands = array_map('trim', explode(',', $user->jenis_barang));
                $query->whereIn('merk', $brands);
            }
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%")
                  ->orWhere('kode_item', 'like', "%{$search}%");
            });
        }

        $barangs = $query->orderBy('nama_barang')
            ->limit(30)
            ->get();

        $results = [];
        foreach ($barangs as $b) {
            $satuansList = [];
            foreach ($b->satuans as $s) {
                $satuansList[] = [
                    'id' => $s->id,
                    'satuan' => $s->satuan,
                    'isi' => $s->isi,
                    'harga_jual' => (float)$s->harga_jual,
                    'harga_pokok' => (float)$s->harga_pokok
                ];
            }

            $stokVal = (float)$b->stok;
            if ($isCanvas && isset($activeCanvasDetails[$b->kode_barang])) {
                $detail = $activeCanvasDetails[$b->kode_barang];
                $stokVal = \App\Services\CanvasService::convertQuantity(
                    (float)$detail->qty_ambil - (float)$detail->qty_terjual,
                    $detail->satuan_id,
                    null,
                    $b->kode_barang
                );
            }

            if ($request->input('has_stock') == 1 && $stokVal <= 0) {
                continue; // skip items with no stock in canvas
            }

            $results[] = [
                'id' => $b->kode_barang,
                'text' => $b->nama_barang . ' (Stok ' . $b->formatStok($stokVal) . ')',
                'kode_barang' => $b->kode_barang,
                'nama_barang' => $b->nama_barang,
                'kategori' => $b->kategori,
                'merk' => $b->merk,
                'kode_supplier' => $b->kode_supplier,
                'stok' => $stokVal,
                'satuans' => $satuansList
            ];
        }

        return response()->json($results);
    }
}
