<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;

class MobileBarangController extends Controller
{
    /**
     * Tampilkan daftar barang dan stok untuk Sales/SPV Sales
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filterMerk = $request->input('merk');

        $query = Barang::with(['satuans'])->where('status', 1);

        // Filter based on salesman restriction
        $user = auth()->user();
        if ($user && ($user->jenis_sales === 'kategori' || $user->jenis_sales === 'merk')) {
            $allowedItems = array_map('trim', explode(',', $user->jenis_barang ?? ''));
            if ($user->jenis_sales === 'kategori') {
                $query->whereIn('kategori', $allowedItems);
            } elseif ($user->jenis_sales === 'merk') {
                $query->whereIn('merk', $allowedItems);
            }
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%");
            });
        }

        if ($filterMerk) {
            $query->where('merk', $filterMerk);
        }

        $barangs = $query->orderBy('nama_barang', 'asc')->limit(20)->get();

        $merksQuery = \App\Models\Merk::query();
        if ($user && $user->jenis_sales === 'merk') {
            $allowedItems = array_map('trim', explode(',', $user->jenis_barang ?? ''));
            $merksQuery->whereIn('nama_merk', $allowedItems);
        }
        $merks = $merksQuery->orderBy('nama_merk', 'asc')->get();

        return view('mobile.barang.index', compact('barangs', 'search', 'merks', 'filterMerk'));
    }
}
