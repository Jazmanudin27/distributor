@extends('layouts.app')
@section('title', 'Master Pelanggan')

@push('styles')
<style>
    .filter-tab {
        border: none;
        background: transparent;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--bs-secondary);
        cursor: pointer;
        transition: all 0.2s;
    }
    .filter-tab.active, .filter-tab:hover {
        background: var(--bs-primary);
        color: #fff;
    }
</style>
@endpush

@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-users me-2"></i> Master Pelanggan
                </h5>
                <small class="text-white-50">Daftar pelanggan / customer terdaftar</small>
            </div>
            @can('create-pelanggan')
                <a href="{{ route('pelanggan.create') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                    <i class="fa-solid fa-circle-plus me-1 text-primary"></i> Tambah Pelanggan
                </a>
            @endcan
        </div>

        <div class="card-body p-4">
            {{-- FILTER SECTION --}}
            @php
                $pendingPelangganCount = \App\Models\Pelanggan::where(function($q) {
                    $q->whereNull('approve')->orWhere('approve', 0);
                })->count();
            @endphp
            <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
                <form action="{{ route('pelanggan.index') }}" method="GET" class="d-flex gap-2 flex-wrap align-items-center w-100">
                    <div class="d-flex gap-1 flex-wrap">
                        <button type="submit" name="approve" value="" class="filter-tab {{ !request('approve') ? 'active' : '' }}">
                            Semua
                        </button>
                        <button type="submit" name="approve" value="pending" class="filter-tab {{ request('approve') === 'pending' ? 'active' : '' }}">
                            <i class="fa-solid fa-hourglass-half me-1"></i> Menunggu Persetujuan
                            @if($pendingPelangganCount > 0)
                                <span class="badge bg-warning text-dark rounded-pill ms-1" style="font-size: 0.7rem; padding: 0.2em 0.5em;">{{ $pendingPelangganCount }}</span>
                            @endif
                        </button>
                        <button type="submit" name="approve" value="1" class="filter-tab {{ request('approve') === '1' ? 'active' : '' }}">
                            <i class="fa-solid fa-check me-1"></i> Disetujui
                        </button>
                        <button type="submit" name="approve" value="2" class="filter-tab {{ request('approve') === '2' ? 'active' : '' }}">
                            <i class="fa-solid fa-xmark me-1"></i> Ditolak
                        </button>
                    </div>
                    
                    {{-- SEARCH & OTHERS ON THE RIGHT --}}
                    <div class="ms-auto d-flex gap-2 align-items-center flex-wrap">
                        <input type="text" name="search" class="form-control form-control-sm" style="width: 180px"
                            placeholder="Cari pelanggan..." value="{{ request('search') }}">
                            
                        <select name="kode_wilayah" class="form-select form-select-sm" style="width: 120px">
                            <option value="">Wilayah</option>
                            @foreach ($wilayahs as $w)
                                <option value="{{ $w->kode_wilayah }}"
                                    {{ request('kode_wilayah') == $w->kode_wilayah ? 'selected' : '' }}>
                                    {{ $w->nama_wilayah }}</option>
                            @endforeach
                        </select>
                        
                        <select name="sub_wilayah" class="form-select form-select-sm" style="width: 120px">
                            <option value="">Sub Wilayah</option>
                            @foreach ($subWilayahs as $sw)
                                <option value="{{ $sw->kode_wilayah }}"
                                    {{ request('sub_wilayah') == $sw->kode_wilayah ? 'selected' : '' }}>
                                    {{ $sw->nama_wilayah }}</option>
                            @endforeach
                        </select>
                        
                        <select name="status" class="form-select form-select-sm" style="width: 100px">
                            <option value="">Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                        
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                        
                        @if(request()->hasAny(['approve','search','kode_wilayah','sub_wilayah','status']))
                            <a href="{{ route('pelanggan.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light text-secondary text-uppercase fs-7 tracking-wider">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th width="150">Kode Pelanggan</th>
                            <th>Nama Pelanggan</th>
                            <th>Alamat</th>
                            <th class="text-end">Limit</th>
                            <th>Status</th>
                            <th>Persetujuan</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pelanggans as $index => $item)
                            <tr class="hover-row">
                                <td class="text-center text-secondary small fw-bold">
                                    {{ $pelanggans->firstItem() + $index }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary font-monospace px-2.5 py-1">
                                        {{ $item->kode_pelanggan }}
                                    </span>
                                </td>
                                <td class="fw-bold text-dark">
                                    <div>{{ $item->nama_pelanggan }}</div>
                                    <div class="text-muted small fw-normal mt-1 d-flex flex-wrap gap-2 align-items-center"
                                        style="font-size: 0.78rem;">
                                        <span>
                                            <i class="fa-solid fa-map-location-dot me-1 text-secondary opacity-75"></i>
                                            <span
                                                class="text-primary fw-semibold">{{ $item->wilayah->nama_wilayah ?? '-' }}</span>
                                        </span>
                                        <span class="text-muted opacity-50">|</span>
                                        <span>
                                            <i class="fa-solid fa-location-crosshairs me-1 text-secondary opacity-75"></i>
                                            <span
                                                class="text-success fw-semibold">{{ $item->subWilayah->nama_wilayah ?? '-' }}</span>
                                        </span>
                                        @if($item->latitude && $item->longitude)
                                            <span class="text-muted opacity-50">|</span>
                                            <span>
                                                <i class="fa-solid fa-map-pin me-1 text-danger"></i>
                                                <a href="https://www.google.com/maps/search/?api=1&query={{ $item->latitude }},{{ $item->longitude }}" target="_blank" class="text-info text-decoration-none fw-semibold">Peta</a>
                                            </span>
                                        @endif
                                        @if($item->foto)
                                            <span class="text-muted opacity-50">|</span>
                                            <span>
                                                <i class="fa-solid fa-image me-1 text-secondary"></i>
                                                <a href="{{ asset($item->foto) }}" target="_blank" class="text-secondary text-decoration-none fw-semibold">Foto Toko</a>
                                            </span>
                                        @endif
                                        @if($item->foto_ktp)
                                            <span class="text-muted opacity-50">|</span>
                                            <span>
                                                <i class="fa-solid fa-id-card me-1 text-secondary"></i>
                                                <a href="{{ asset($item->foto_ktp) }}" target="_blank" class="text-secondary text-decoration-none fw-semibold">KTP</a>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-secondary small">{{ Str::limit($item->alamat_pelanggan, 50) }}</td>
                                <td class="text-end fw-semibold text-success">
                                    Rp {{ number_format((float) $item->limit_pelanggan, 0, ',', '.') }}
                                </td>
                                <td>
                                    <form action="{{ route('pelanggan.toggle-status', $item->kode_pelanggan) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit"
                                            class="badge border-0 bg-{{ $item->status == 1 ? 'success' : 'secondary' }}-subtle text-{{ $item->status == 1 ? 'success' : 'secondary' }} border border-{{ $item->status == 1 ? 'success' : 'secondary' }}-subtle px-2.5 py-1.5 fw-bold fs-8 hover-scale"
                                            style="cursor: pointer;" title="Klik untuk mengubah status">
                                            {{ $item->status == 1 ? 'Aktif' : 'Non-Aktif' }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    @if($item->approve === 1)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 fw-bold fs-8">Disetujui</span>
                                    @elseif($item->approve === 2)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 fw-bold fs-8">Ditolak</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1.5 fw-bold fs-8">Pending</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        @if(!$item->approve || $item->approve == 0 || $item->approve == 2)
                                            @can('edit-pelanggan')
                                                <form action="{{ route('pelanggan.approve', $item->kode_pelanggan) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success text-white px-2 py-1" title="Setujui Pelanggan" onclick="return confirm('Apakah Anda yakin ingin menyetujui pelanggan ini?')">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                        @if(!$item->approve || $item->approve == 0 || $item->approve == 1)
                                            @can('edit-pelanggan')
                                                <form action="{{ route('pelanggan.reject', $item->kode_pelanggan) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger text-white px-2 py-1" title="Tolak Pelanggan" onclick="return confirm('Apakah Anda yakin ingin menolak pelanggan ini?')">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                        @can('edit-pelanggan')
                                            <a href="{{ route('pelanggan.edit', $item->kode_pelanggan) }}"
                                                class="btn btn-sm btn-outline-primary rounded px-2 py-1" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        @endcan
                                        @can('delete-pelanggan')
                                            <form action="{{ route('pelanggan.destroy', $item->kode_pelanggan) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger delete rounded px-2 py-1"
                                                    title="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-users d-block fs-3 mb-2 opacity-50"></i>
                                    Tidak ada data pelanggan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($pelanggans->hasPages())
                <div class="d-flex justify-content-end mt-4">
                    {{ $pelanggans->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
