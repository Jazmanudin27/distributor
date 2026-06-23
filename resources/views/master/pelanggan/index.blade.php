@extends('layouts.app')
@section('title', 'Master Pelanggan')

@push('styles')
    <style>
        /* Tab filters styled as premium SaaS-like pill links with bottom bar active state */
        .filter-tabs-container {
            display: flex;
            gap: 0.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
            overflow-x: auto;
            scrollbar-width: none;
            /* Firefox */
        }

        .filter-tabs-container::-webkit-scrollbar {
            display: none;
            /* Safari and Chrome */
        }

        .filter-tab {
            border: none;
            background: transparent;
            padding: 8px 16px;
            font-size: 0.88rem;
            font-weight: 600;
            color: #64748B;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .filter-tab:hover {
            color: #F1F5F9;
        }

        .filter-tab.active {
            color: #6C63FF;
            /* Violet / Primary */
        }

        .filter-tab.active::after {
            content: '';
            position: absolute;
            bottom: -0.75rem;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #6C63FF;
            border-radius: 3px 3px 0 0;
        }

        .tab-badge {
            font-size: 0.75rem;
            padding: 0.15rem 0.45rem;
            border-radius: 999px;
            font-weight: 700;
            margin-left: 0.25rem;
        }

        /* Filter Row container styling */
        .search-filter-row {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 1.75rem;
        }

        .filter-input-wrapper {
            position: relative;
            flex: 1;
            min-width: 220px;
        }

        .filter-input-wrapper i {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748B;
            font-size: 0.85rem;
        }

        .filter-input-wrapper input {
            padding-left: 2.3rem !important;
        }

        .filter-select {
            min-width: 140px;
            flex: 0 1 auto;
        }

        /* Meta badge pills styling */
        .meta-pill {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 6px;
            padding: 2px 8px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: #94A3B8 !important;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }

        .meta-pill:hover {
            background: rgba(108, 99, 255, 0.08);
            border-color: rgba(108, 99, 255, 0.2);
            color: #a78bfa !important;
        }

        .meta-pill-primary {
            color: #a78bfa !important;
            background: rgba(108, 99, 255, 0.05);
            border-color: rgba(108, 99, 255, 0.1);
        }

        .meta-pill-success {
            color: #34D399 !important;
            background: rgba(52, 211, 153, 0.05);
            border-color: rgba(52, 211, 153, 0.1);
        }

        .meta-pill-warning {
            color: #FBBF24 !important;
            background: rgba(251, 191, 36, 0.05);
            border-color: rgba(251, 191, 36, 0.1);
        }

        .meta-pill-info {
            color: #38BDF8 !important;
            background: rgba(56, 189, 248, 0.05);
            border-color: rgba(56, 189, 248, 0.1);
        }

        @media (max-width: 768px) {
            .filter-select {
                flex: 1 1 120px;
            }

            .search-filter-row {
                flex-direction: column;
                align-items: stretch;
            }
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
                $pendingPelangganCount = \App\Models\Pelanggan::where(function ($q) {
                    $q->whereNull('approve')->orWhere('approve', 0);
                })->count();
            @endphp

            <form action="{{ route('pelanggan.index') }}" method="GET" class="w-100">
                {{-- Row 1: Tab Filters --}}
                <div class="filter-tabs-container">
                    <button type="submit" name="approve" value=""
                        class="filter-tab {{ !request('approve') ? 'active' : '' }}">
                        Semua
                    </button>
                    <button type="submit" name="approve" value="pending"
                        class="filter-tab {{ request('approve') === 'pending' ? 'active' : '' }}">
                        <i class="fa-solid fa-hourglass-half"></i> Menunggu Persetujuan
                        @if ($pendingPelangganCount > 0)
                            <span class="badge bg-warning text-dark tab-badge">{{ $pendingPelangganCount }}</span>
                        @endif
                    </button>
                    <button type="submit" name="approve" value="1"
                        class="filter-tab {{ request('approve') === '1' ? 'active' : '' }}">
                        <i class="fa-solid fa-check"></i> Disetujui
                    </button>
                    <button type="submit" name="approve" value="2"
                        class="filter-tab {{ request('approve') === '2' ? 'active' : '' }}">
                        <i class="fa-solid fa-xmark"></i> Ditolak
                    </button>
                </div>

                {{-- Row 2: Search & Filter Row --}}
                <div class="search-filter-row">
                    <div class="filter-input-wrapper">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Cari pelanggan..." value="{{ request('search') }}">
                    </div>

                    <select name="kode_wilayah" class="form-select form-select-sm filter-select">
                        <option value="">Wilayah</option>
                        @foreach ($wilayahs as $w)
                            <option value="{{ $w->kode_wilayah }}"
                                {{ request('kode_wilayah') == $w->kode_wilayah ? 'selected' : '' }}>
                                {{ $w->nama_wilayah }}</option>
                        @endforeach
                    </select>

                    <select name="sub_wilayah" class="form-select form-select-sm filter-select">
                        <option value="">Sub Wilayah</option>
                        @foreach ($subWilayahs as $sw)
                            <option value="{{ $sw->kode_wilayah }}"
                                {{ request('sub_wilayah') == $sw->kode_wilayah ? 'selected' : '' }}>
                                {{ $sw->nama_wilayah }}</option>
                        @endforeach
                    </select>

                    <select name="status" class="form-select form-select-sm filter-select" style="min-width: 100px">
                        <option value="">Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>

                        @if (request()->hasAny(['approve', 'search', 'kode_wilayah', 'sub_wilayah', 'status']))
                            <a href="{{ route('pelanggan.index') }}"
                                class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1"
                                title="Reset Filter">
                                <i class="fa-solid fa-rotate-left"></i> Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>

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
                                <td class="fw-bold">
                                    <div class="text-white">{{ $item->nama_pelanggan }}</div>
                                    <div class="mt-2 d-flex flex-wrap gap-1.5 align-items-center">
                                        <span class="meta-pill meta-pill-primary">
                                            <i class="fa-solid fa-map-location-dot opacity-75"></i>
                                            <span>{{ $item->wilayah->nama_wilayah ?? '-' }}</span>
                                        </span>

                                        <span class="meta-pill meta-pill-success">
                                            <i class="fa-solid fa-location-crosshairs opacity-75"></i>
                                            <span>{{ $item->subWilayah->nama_wilayah ?? '-' }}</span>
                                        </span>

                                        @if ($item->jenis_pelanggan == '1')
                                            <span class="meta-pill meta-pill-warning">
                                                <i class="fa-solid fa-star opacity-75"></i>
                                                <span>Khusus (Bypass Overdue)</span>
                                            </span>
                                        @else
                                            <span class="meta-pill meta-pill-info">
                                                <i class="fa-solid fa-circle-user opacity-75"></i>
                                                <span>Regular</span>
                                            </span>
                                        @endif

                                        @if ($item->latitude && $item->longitude)
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $item->latitude }},{{ $item->longitude }}"
                                                target="_blank" class="meta-pill">
                                                <i class="fa-solid fa-map-pin text-danger"></i>
                                                <span>Peta</span>
                                            </a>
                                        @endif
                                        @if ($item->foto)
                                            @php
                                                $fotoUrl = Str::contains($item->foto, '/')
                                                    ? asset($item->foto)
                                                    : asset('storage/pelanggan/' . $item->foto);
                                            @endphp
                                            <a href="{{ $fotoUrl }}" target="_blank"
                                                class="meta-pill preview-image-trigger"
                                                data-title="Foto Toko - {{ $item->nama_pelanggan }}"
                                                data-src="{{ $fotoUrl }}">
                                                <i class="fa-solid fa-image text-secondary"></i>
                                                <span>Foto Toko</span>
                                            </a>
                                        @endif
                                        @if ($item->foto_ktp)
                                            @php
                                                $fotoKtpUrl = Str::contains($item->foto_ktp, '/')
                                                    ? asset($item->foto_ktp)
                                                    : asset('storage/pelanggan_ktp/' . $item->foto_ktp);
                                            @endphp
                                            <a href="{{ $fotoKtpUrl }}" target="_blank"
                                                class="meta-pill preview-image-trigger"
                                                data-title="KTP Pelanggan - {{ $item->nama_pelanggan }}"
                                                data-src="{{ $fotoKtpUrl }}">
                                                <i class="fa-solid fa-id-card text-secondary"></i>
                                                <span>KTP</span>
                                            </a>
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
                                    @if ($item->approve === 1)
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 fw-bold fs-8">Disetujui</span>
                                    @elseif($item->approve === 2)
                                        <span
                                            class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 fw-bold fs-8">Ditolak</span>
                                    @else
                                        <span
                                            class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1.5 fw-bold fs-8">Pending</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        @if (!$item->approve || $item->approve == 0 || $item->approve == 2)
                                            @can('edit-pelanggan')
                                                <form action="{{ route('pelanggan.approve', $item->kode_pelanggan) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success text-white px-2 py-1"
                                                        title="Setujui Pelanggan"
                                                        onclick="return confirm('Apakah Anda yakin ingin menyetujui pelanggan ini?')">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                        @if (!$item->approve || $item->approve == 0 || $item->approve == 1)
                                            @can('edit-pelanggan')
                                                <form action="{{ route('pelanggan.reject', $item->kode_pelanggan) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger text-white px-2 py-1"
                                                        title="Tolak Pelanggan"
                                                        onclick="return confirm('Apakah Anda yakin ingin menolak pelanggan ini?')">
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
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger delete rounded px-2 py-1"
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

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: #1A1D27; border: 1px solid rgba(255, 255, 255, 0.1);">
                <div class="modal-header border-bottom border-white-10">
                    <h6 class="modal-title text-white fw-bold" id="imagePreviewModalLabel">Preview Foto</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-3">
                    <img id="previewModalImage" src="" class="img-fluid rounded border border-white-10"
                        style="max-height: 70vh; object-fit: contain; background-color: #0F1117;">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.preview-image-trigger').on('click', function(e) {
                e.preventDefault();
                const title = $(this).data('title');
                const src = $(this).data('src');
                $('#imagePreviewModalLabel').text(title);
                $('#previewModalImage').attr('src', src);
                $('#imagePreviewModal').modal('show');
            });
        });
    </script>
@endpush
