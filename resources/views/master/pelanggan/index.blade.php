@extends('layouts.app')
@section('title', 'Master Pelanggan')

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
            @php
                $pendingPelangganCount = \App\Models\Pelanggan::where(function ($q) {
                    $q->whereNull('approve')->orWhere('approve', 0);
                })->count();
            @endphp

            <form action="{{ route('pelanggan.index') }}" method="GET">

                {{-- Tab Filters --}}
                <div class="d-flex gap-2 flex-wrap pb-3 mb-3 border-bottom border-secondary-subtle">
                    <button type="submit" name="approve" value=""
                        class="btn btn-sm {{ !request('approve') ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Semua
                    </button>
                    <button type="submit" name="approve" value="pending"
                        class="btn btn-sm {{ request('approve') === 'pending' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">
                        <i class="fa-solid fa-hourglass-half me-1"></i> Menunggu Persetujuan
                        @if ($pendingPelangganCount > 0)
                            <span class="badge bg-dark ms-1">{{ $pendingPelangganCount }}</span>
                        @endif
                    </button>
                    <button type="submit" name="approve" value="1"
                        class="btn btn-sm {{ request('approve') === '1' ? 'btn-success' : 'btn-outline-secondary' }}">
                        <i class="fa-solid fa-check me-1"></i> Disetujui
                    </button>
                    <button type="submit" name="approve" value="2"
                        class="btn btn-sm {{ request('approve') === '2' ? 'btn-danger' : 'btn-outline-secondary' }}">
                        <i class="fa-solid fa-xmark me-1"></i> Ditolak
                    </button>
                </div>

                {{-- Search & Filter Row --}}
                <div class="row g-2 align-items-center mb-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari pelanggan..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <select name="kode_wilayah" class="form-select form-select-sm">
                            <option value="">Wilayah</option>
                            @foreach ($wilayahs as $w)
                                <option value="{{ $w->kode_wilayah }}"
                                    {{ request('kode_wilayah') == $w->kode_wilayah ? 'selected' : '' }}>
                                    {{ $w->nama_wilayah }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <select name="sub_wilayah" class="form-select form-select-sm">
                            <option value="">Sub Wilayah</option>
                            @foreach ($subWilayahs as $sw)
                                <option value="{{ $sw->kode_wilayah }}"
                                    {{ request('sub_wilayah') == $sw->kode_wilayah ? 'selected' : '' }}>
                                    {{ $sw->nama_wilayah }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <select name="kode_sales" class="form-select form-select-sm">
                            <option value="">Sales</option>
                            @foreach ($salesmen as $s)
                                <option value="{{ $s->nik }}"
                                    {{ request('kode_sales') == $s->nik ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 col-sm-6">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['approve', 'search', 'kode_wilayah', 'sub_wilayah', 'status', 'kode_sales']))
                            <a href="{{ route('pelanggan.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light small text-uppercase text-secondary">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th width="140">Kode</th>
                            <th>Nama Pelanggan</th>
                            <th>Alamat</th>
                            <th class="text-end">Limit</th>
                            <th class="text-end">Sisa Limit</th>
                            <th>Status</th>
                            <th>Persetujuan</th>
                            <th width="130" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pelanggans as $index => $item)
                            @php
                                $sisaLimit    = $item->getSisaLimitKredit();
                                $pctSisa      = $item->limit_pelanggan > 0 ? ($sisaLimit / $item->limit_pelanggan) : 1;
                                $sisaColor    = $pctSisa >= 0.5 ? 'success' : ($pctSisa > 0 ? 'warning' : 'danger');
                                $sisaIcon     = $pctSisa >= 0.5 ? 'fa-circle-check' : ($pctSisa > 0 ? 'fa-triangle-exclamation' : 'fa-circle-xmark');
                            @endphp
                            <tr>
                                <td class="text-center text-secondary small fw-bold">
                                    {{ $pelanggans->firstItem() + $index }}
                                </td>
                                <td>
                                    <code class="text-secondary small">{{ $item->kode_pelanggan }}</code>
                                </td>
                                <td>
                                    <div class="fw-semibold text-white">{{ $item->nama_pelanggan }}</div>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            <i class="fa-solid fa-map-location-dot me-1 opacity-75"></i>{{ $item->wilayah->nama_wilayah ?? '-' }}
                                        </span>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="fa-solid fa-location-crosshairs me-1 opacity-75"></i>{{ $item->subWilayah->nama_wilayah ?? '-' }}
                                        </span>
                                        @if ($item->jenis_pelanggan == '1')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                                <i class="fa-solid fa-star me-1 opacity-75"></i>Khusus
                                            </span>
                                        @else
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                                <i class="fa-solid fa-circle-user me-1 opacity-75"></i>Regular
                                            </span>
                                        @endif
                                        @if ($item->kode_sales)
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                <i class="fa-solid fa-user-tie me-1 opacity-75"></i>Sales: {{ $item->sales->name ?? $item->kode_sales }}
                                            </span>
                                        @endif
                                        @if ($item->latitude && $item->longitude)
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $item->latitude }},{{ $item->longitude }}"
                                                target="_blank"
                                                class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-decoration-none">
                                                <i class="fa-solid fa-map-pin me-1 text-danger"></i>Peta
                                            </a>
                                        @endif
                                        @if ($item->foto)
                                            @php
                                                $fotoUrl = Str::contains($item->foto, '/')
                                                    ? asset($item->foto)
                                                    : asset('storage/pelanggan/' . $item->foto);
                                            @endphp
                                            <a href="{{ $fotoUrl }}" target="_blank"
                                                class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-decoration-none preview-image-trigger"
                                                data-title="Foto Toko - {{ $item->nama_pelanggan }}"
                                                data-src="{{ $fotoUrl }}">
                                                <i class="fa-solid fa-image me-1"></i>Foto Toko
                                            </a>
                                        @endif
                                        @if ($item->foto_ktp)
                                            @php
                                                $fotoKtpUrl = Str::contains($item->foto_ktp, '/')
                                                    ? asset($item->foto_ktp)
                                                    : asset('storage/pelanggan_ktp/' . $item->foto_ktp);
                                            @endphp
                                            <a href="{{ $fotoKtpUrl }}" target="_blank"
                                                class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-decoration-none preview-image-trigger"
                                                data-title="KTP Pelanggan - {{ $item->nama_pelanggan }}"
                                                data-src="{{ $fotoKtpUrl }}">
                                                <i class="fa-solid fa-id-card me-1"></i>KTP
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-secondary small">{{ Str::limit($item->alamat_pelanggan, 50) }}</td>
                                <td class="text-end fw-semibold text-success small">
                                    Rp {{ number_format((float) $item->limit_pelanggan, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    <button type="button"
                                        class="btn btn-sm bg-{{ $sisaColor }}-subtle text-{{ $sisaColor }} border border-{{ $sisaColor }}-subtle fw-bold btn-sisa-limit"
                                        data-url="{{ route('pelanggan.sisa-limit', $item->kode_pelanggan) }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#sisaLimitModal"
                                        title="Klik untuk lihat detail">
                                        <i class="fas {{ $sisaIcon }} me-1" style="font-size:.7rem;"></i>
                                        Rp {{ number_format($sisaLimit, 0, ',', '.') }}
                                    </button>
                                </td>
                                <td>
                                    <form action="{{ route('pelanggan.toggle-status', $item->kode_pelanggan) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit"
                                            class="badge border-0 bg-{{ $item->status == 1 ? 'success' : 'secondary' }}-subtle text-{{ $item->status == 1 ? 'success' : 'secondary' }} border border-{{ $item->status == 1 ? 'success' : 'secondary' }}-subtle fw-bold"
                                            style="cursor:pointer;" title="Klik untuk ubah status">
                                            {{ $item->status == 1 ? 'Aktif' : 'Non-Aktif' }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    @if ($item->approve === 1)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">Disetujui</span>
                                    @elseif ($item->approve === 2)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold">Ditolak</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-bold">Pending</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        @if (!$item->approve || $item->approve == 0 || $item->approve == 2)
                                            @can('edit-pelanggan')
                                                <form action="{{ route('pelanggan.approve', $item->kode_pelanggan) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success px-2 py-1"
                                                        title="Setujui Pelanggan"
                                                        onclick="return confirm('Setujui pelanggan ini?')">
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
                                                    <button type="submit" class="btn btn-sm btn-danger px-2 py-1"
                                                        title="Tolak Pelanggan"
                                                        onclick="return confirm('Tolak pelanggan ini?')">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                        @can('edit-pelanggan')
                                            <a href="{{ route('pelanggan.edit', $item->kode_pelanggan) }}"
                                                class="btn btn-sm btn-outline-primary px-2 py-1" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <form action="{{ route('pelanggan.toggle-jenis', $item->kode_pelanggan) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-{{ $item->jenis_pelanggan == '1' ? 'warning' : 'secondary' }} px-2 py-1"
                                                    title="Ubah ke {{ $item->jenis_pelanggan == '1' ? 'Regular' : 'Khusus (Bypass Overdue)' }}"
                                                    onclick="return confirm('Ubah tipe pelanggan \'{{ $item->nama_pelanggan }}\'?')">
                                                    <i class="{{ $item->jenis_pelanggan == '1' ? 'fa-solid fa-star text-warning' : 'fa-regular fa-star' }}"></i>
                                                </button>
                                            </form>
                                        @endcan
                                        @can('delete-pelanggan')
                                            <form action="{{ route('pelanggan.destroy', $item->kode_pelanggan) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger delete px-2 py-1" title="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-users d-block fs-3 mb-2 opacity-50"></i>
                                    Tidak ada data pelanggan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($pelanggans->hasPages())
                <div class="d-flex justify-content-end mt-3">
                    {{ $pelanggans->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ─── Sisa Limit Modal ─── --}}
    <div class="modal fade" id="sisaLimitModal" tabindex="-1" aria-labelledby="sisaLimitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color:#1A1D27;border:1px solid rgba(255,255,255,.1);color:#E2E8F0;">
                <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,.08);">
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="sisaLimitModalLabel">
                            <i class="fa-solid fa-wallet me-2 text-primary"></i> Detail Limit Kredit
                        </h6>
                        <small class="text-secondary" id="sisaLimitSubtitle"></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">

                    {{-- Spinner --}}
                    <div id="sisaLimitLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" style="width:2rem;height:2rem;"></div>
                        <div class="text-secondary small mt-2">Memuat data...</div>
                    </div>

                    {{-- Content --}}
                    <div id="sisaLimitContent" class="d-none">

                        {{-- Stats cards --}}
                        <div class="row g-2 mb-3" id="sisaLimitStats"></div>

                        {{-- Progress bar --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small text-secondary mb-1">
                                <span>Utilisasi Limit</span>
                                <span id="sisaLimitPctText">-</span>
                            </div>
                            <div class="progress" style="height:8px;">
                                <div id="sisaLimitProgressBar" class="progress-bar"
                                    role="progressbar" style="transition:width .5s ease;"></div>
                            </div>
                        </div>

                        {{-- Faktur belum lunas --}}
                        <p class="text-secondary small fw-bold text-uppercase mb-2">
                            <i class="fas fa-file-invoice me-1"></i> Faktur Belum Lunas
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0" id="sisaLimitTable">
                                <thead class="table-secondary small text-uppercase">
                                    <tr>
                                        <th>No Faktur</th>
                                        <th>Tanggal</th>
                                        <th>Jenis</th>
                                        <th class="text-end">Grand Total</th>
                                        <th class="text-end">Sudah Bayar</th>
                                        <th class="text-end">Sisa</th>
                                    </tr>
                                </thead>
                                <tbody id="sisaLimitTableBody"></tbody>
                            </table>
                        </div>
                        <div id="sisaLimitEmpty" class="text-center py-4 text-secondary d-none">
                            <i class="fas fa-circle-check text-success d-block fs-3 mb-2"></i>
                            Semua faktur sudah lunas!
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Image Preview Modal ─── --}}
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color:#1A1D27;border:1px solid rgba(255,255,255,.1);">
                <div class="modal-header border-bottom border-secondary">
                    <h6 class="modal-title text-white fw-bold" id="imagePreviewModalLabel">Preview Foto</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-3">
                    <img id="previewModalImage" src="" class="img-fluid rounded"
                        style="max-height:70vh;object-fit:contain;">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function rupiahFmt(n) {
            return 'Rp ' + Math.round(n).toLocaleString('id-ID');
        }

        $(function () {

            // ── Image preview ──────────────────────────────────────────
            $('.preview-image-trigger').on('click', function (e) {
                e.preventDefault();
                $('#imagePreviewModalLabel').text($(this).data('title'));
                $('#previewModalImage').attr('src', $(this).data('src'));
                $('#imagePreviewModal').modal('show');
            });

            // ── Sisa Limit modal – load via AJAX on open ───────────────
            $('#sisaLimitModal').on('show.bs.modal', function (e) {
                const url = $(e.relatedTarget).data('url');

                // Reset
                $('#sisaLimitLoading').show();
                $('#sisaLimitContent').addClass('d-none');
                $('#sisaLimitModalLabel').html('<i class="fa-solid fa-wallet me-2 text-primary"></i> Detail Limit Kredit');
                $('#sisaLimitSubtitle').text('');

                $.getJSON(url)
                    .done(function (d) {
                        const usedPct = d.limit > 0 ? Math.round((d.outstanding / d.limit) * 100) : 0;
                        const sisaPct = 100 - usedPct;
                        const barCls  = sisaPct >= 50 ? 'bg-success' : (sisaPct > 0 ? 'bg-warning' : 'bg-danger');
                        const txtCls  = sisaPct >= 50 ? 'text-success' : (sisaPct > 0 ? 'text-warning' : 'text-danger');

                        // Header
                        $('#sisaLimitModalLabel').html('<i class="fa-solid fa-wallet me-2 text-primary"></i>' + d.nama_pelanggan);
                        $('#sisaLimitSubtitle').text(d.kode_pelanggan);

                        // Progress bar
                        $('#sisaLimitProgressBar').attr('class', 'progress-bar ' + barCls).css('width', usedPct + '%');
                        $('#sisaLimitPctText').text(usedPct + '% digunakan');

                        // Stats
                        $('#sisaLimitStats').html(`
                            <div class="col-4">
                                <div class="card border-0 text-center py-2" style="background:rgba(255,255,255,.05);">
                                    <div class="text-secondary" style="font-size:.7rem;font-weight:700;text-transform:uppercase;">Limit Total</div>
                                    <div class="fw-bold text-white small">${rupiahFmt(d.limit)}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card border-0 text-center py-2" style="background:rgba(255,255,255,.05);">
                                    <div class="text-secondary" style="font-size:.7rem;font-weight:700;text-transform:uppercase;">Outstanding</div>
                                    <div class="fw-bold text-warning small">${rupiahFmt(d.outstanding)}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card border-0 text-center py-2" style="background:rgba(255,255,255,.05);">
                                    <div class="text-secondary" style="font-size:.7rem;font-weight:700;text-transform:uppercase;">Sisa Limit</div>
                                    <div class="fw-bold ${txtCls} small">${rupiahFmt(d.sisa_limit)}</div>
                                </div>
                            </div>
                        `);

                        // Faktur table
                        const $tbody = $('#sisaLimitTableBody').empty();

                        if (d.faktur.length === 0) {
                            $('#sisaLimitTable').addClass('d-none');
                            $('#sisaLimitEmpty').removeClass('d-none');
                        } else {
                            $('#sisaLimitTable').removeClass('d-none');
                            $('#sisaLimitEmpty').addClass('d-none');
                            $.each(d.faktur, function (i, f) {
                                const isKredit = f.jenis === 'Kredit' || f.jenis === 'K';
                                const jenisBadge = isKredit
                                    ? 'bg-warning-subtle text-warning border border-warning-subtle'
                                    : 'bg-info-subtle text-info border border-info-subtle';
                                $tbody.append(`
                                    <tr>
                                        <td><code class="text-secondary small">${f.no_faktur}</code></td>
                                        <td class="text-secondary small">${f.tanggal}</td>
                                        <td><span class="badge ${jenisBadge}">${f.jenis}</span></td>
                                        <td class="text-end small">${rupiahFmt(f.grand_total)}</td>
                                        <td class="text-end text-success small">${rupiahFmt(f.total_bayar)}</td>
                                        <td class="text-end fw-bold text-danger small">${rupiahFmt(f.sisa_hutang)}</td>
                                    </tr>
                                `);
                            });
                        }

                        $('#sisaLimitLoading').hide();
                        $('#sisaLimitContent').removeClass('d-none');
                    })
                    .fail(function () {
                        $('#sisaLimitLoading').html(
                            '<div class="text-danger text-center py-4"><i class="fas fa-circle-xmark me-1"></i> Gagal memuat data.</div>'
                        );
                    });
            });

        });
    </script>
@endpush
