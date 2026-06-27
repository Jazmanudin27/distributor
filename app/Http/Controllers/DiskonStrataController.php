<?php

namespace App\Http\Controllers;

use App\Models\DiskonStrata;
use App\Models\DiskonStrataDetail;
use App\Models\Kategori;
use App\Models\Merk;
use App\Models\Supplier;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiskonStrataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DiskonStrata::with(['kategori', 'merk', 'supplier']);

        if ($request->filled('search')) {
            $query->where('nama_diskon', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());

        return view('master.diskon_strata.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $item = new DiskonStrata();
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        $merks = Merk::orderBy('nama_merk')->get();
        $suppliers = Supplier::where('status', 1)->orderBy('nama_supplier')->get();
        $barangs = Barang::with('satuans')->where('status', 1)->orderBy('nama_barang')->get();

        return view('master.diskon_strata.form', compact('item', 'kategoris', 'merks', 'suppliers', 'barangs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_diskon' => 'required|string|max:150',
            'tipe' => 'required|in:barang,beberapa_barang,kategori,merk,supplier',
            'kategori_id' => 'required_if:tipe,kategori|nullable|exists:kategori,id',
            'merk_id' => 'required_if:tipe,merk|nullable|exists:merk,id',
            'kode_supplier' => 'required_if:tipe,supplier|nullable|exists:supplier,kode_supplier',
            'berlaku_dari' => 'nullable|date',
            'berlaku_sampai' => 'nullable|date',
            'barang_ids' => 'required_if:tipe,barang,beberapa_barang|array',
            'details' => 'required|array|min:1',
            'details.*.satuan_id' => 'nullable|integer|exists:barang_satuan,id',
            'details.*.min_qty' => 'nullable|integer|min:0',
            'details.*.max_qty' => 'nullable|integer|min:0',
            'details.*.min_nominal' => 'nullable|numeric|min:0',
            'details.*.max_nominal' => 'nullable|numeric|min:0',
            'details.*.tipe_nilai' => 'required|in:persen,nominal',
            'details.*.dis1' => 'required|numeric|min:0',
            'details.*.dis2' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $header = DiskonStrata::create([
                'nama_diskon' => $request->nama_diskon,
                'tipe' => $request->tipe,
                'kategori_id' => $request->tipe === 'kategori' ? $request->kategori_id : null,
                'merk_id' => $request->tipe === 'merk' ? $request->merk_id : null,
                'kode_supplier' => $request->tipe === 'supplier' ? $request->kode_supplier : null,
                'berlaku_dari' => $request->berlaku_dari,
                'berlaku_sampai' => $request->berlaku_sampai,
                'is_active' => $request->has('is_active') ? 1 : 0,
            ]);

            // Save items if applicable
            if (in_array($request->tipe, ['barang', 'beberapa_barang']) && $request->has('barang_ids')) {
                // If it is single 'barang', we only take the first element to ensure schema integrity
                $barangIds = $request->tipe === 'barang' ? array_slice($request->barang_ids, 0, 1) : $request->barang_ids;
                $header->barangs()->sync($barangIds);
            }

            // Save tiers details
            foreach ($request->details as $detail) {
                $header->details()->create([
                    'satuan_id' => in_array($request->tipe, ['barang', 'beberapa_barang']) ? ($detail['satuan_id'] ?? null) : null,
                    'min_qty' => $request->tipe !== 'supplier' ? ($detail['min_qty'] ?? null) : null,
                    'max_qty' => $request->tipe !== 'supplier' ? ($detail['max_qty'] ?? null) : null,
                    'min_nominal' => $request->tipe === 'supplier' ? ($detail['min_nominal'] ?? null) : null,
                    'max_nominal' => $request->tipe === 'supplier' ? ($detail['max_nominal'] ?? null) : null,
                    'tipe_nilai' => $detail['tipe_nilai'],
                    'dis1' => $detail['dis1'],
                    'dis2' => $detail['dis2'],
                ]);
            }
        });

        return redirect()->route('diskon-strata.index')->with('success', 'Diskon strata berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = DiskonStrata::with(['kategori', 'merk', 'supplier', 'barangs', 'details'])->findOrFail($id);
        return view('master.diskon_strata.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $item = DiskonStrata::with(['barangs', 'details.satuan'])->findOrFail($id);
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        $merks = Merk::orderBy('nama_merk')->get();
        $suppliers = Supplier::where('status', 1)->orderBy('nama_supplier')->get();
        $barangs = Barang::with('satuans')->where('status', 1)->orderBy('nama_barang')->get();

        return view('master.diskon_strata.form', compact('item', 'kategoris', 'merks', 'suppliers', 'barangs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_diskon' => 'required|string|max:150',
            'tipe' => 'required|in:barang,beberapa_barang,kategori,merk,supplier',
            'kategori_id' => 'required_if:tipe,kategori|nullable|exists:kategori,id',
            'merk_id' => 'required_if:tipe,merk|nullable|exists:merk,id',
            'kode_supplier' => 'required_if:tipe,supplier|nullable|exists:supplier,kode_supplier',
            'berlaku_dari' => 'nullable|date',
            'berlaku_sampai' => 'nullable|date',
            'barang_ids' => 'required_if:tipe,barang,beberapa_barang|array',
            'details' => 'required|array|min:1',
            'details.*.satuan_id' => 'nullable|integer|exists:barang_satuan,id',
            'details.*.min_qty' => 'nullable|integer|min:0',
            'details.*.max_qty' => 'nullable|integer|min:0',
            'details.*.min_nominal' => 'nullable|numeric|min:0',
            'details.*.max_nominal' => 'nullable|numeric|min:0',
            'details.*.tipe_nilai' => 'required|in:persen,nominal',
            'details.*.dis1' => 'required|numeric|min:0',
            'details.*.dis2' => 'required|numeric|min:0',
        ]);

        $header = DiskonStrata::findOrFail($id);

        DB::transaction(function () use ($request, $header) {
            $header->update([
                'nama_diskon' => $request->nama_diskon,
                'tipe' => $request->tipe,
                'kategori_id' => $request->tipe === 'kategori' ? $request->kategori_id : null,
                'merk_id' => $request->tipe === 'merk' ? $request->merk_id : null,
                'kode_supplier' => $request->tipe === 'supplier' ? $request->kode_supplier : null,
                'berlaku_dari' => $request->berlaku_dari,
                'berlaku_sampai' => $request->berlaku_sampai,
                'is_active' => $request->has('is_active') ? 1 : 0,
            ]);

            // Sync items
            if (in_array($request->tipe, ['barang', 'beberapa_barang']) && $request->has('barang_ids')) {
                $barangIds = $request->tipe === 'barang' ? array_slice($request->barang_ids, 0, 1) : $request->barang_ids;
                $header->barangs()->sync($barangIds);
            } else {
                $header->barangs()->detach();
            }

            // Replace details
            $header->details()->delete();
            foreach ($request->details as $detail) {
                $header->details()->create([
                    'satuan_id' => in_array($request->tipe, ['barang', 'beberapa_barang']) ? ($detail['satuan_id'] ?? null) : null,
                    'min_qty' => $request->tipe !== 'supplier' ? ($detail['min_qty'] ?? null) : null,
                    'max_qty' => $request->tipe !== 'supplier' ? ($detail['max_qty'] ?? null) : null,
                    'min_nominal' => $request->tipe === 'supplier' ? ($detail['min_nominal'] ?? null) : null,
                    'max_nominal' => $request->tipe === 'supplier' ? ($detail['max_nominal'] ?? null) : null,
                    'tipe_nilai' => $detail['tipe_nilai'],
                    'dis1' => $detail['dis1'],
                    'dis2' => $detail['dis2'],
                ]);
            }
        });

        return redirect()->route('diskon-strata.index')->with('success', 'Diskon strata berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = DiskonStrata::findOrFail($id);
        $item->delete(); // Cascading delete will handle details and pivot

        return redirect()->route('diskon-strata.index')->with('success', 'Diskon strata berhasil dihapus.');
    }

    public function toggleStatus($id)
    {
        $item = DiskonStrata::findOrFail($id);
        $item->is_active = $item->is_active == 1 ? 0 : 1;
        $item->save();

        return redirect()->back()->with('success', 'Status diskon strata berhasil diubah.');
    }
}
