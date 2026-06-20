@extends('layouts.app')
@section('title', 'Buat Ajuan Limit Kredit')

@push('styles')
<style>
    .pelanggan-info-card {
        border-radius: 14px;
        border: 1.5px dashed var(--bs-primary);
        background: rgba(99,102,241,0.04);
        transition: all 0.3s;
    }
    .limit-preview {
        font-size: 1.5rem;
        font-weight: 700;
        color: #6366f1;
    }
    .limit-diff {
        font-size: 0.85rem;
        padding: 3px 10px;
        border-radius: 20px;
    }
    .form-section-label {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: var(--bs-secondary);
        margin-bottom: 0.6rem;
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header card-premium-header text-white d-flex align-items-center gap-3 py-3">
                <a href="{{ route('ajuan-limit-kredit.index') }}" class="btn btn-sm btn-light btn-outline-light opacity-75">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h5 class="mb-0 fw-bold">
                        <i class="fa-solid fa-hand-holding-dollar me-2"></i> Buat Ajuan Limit Kredit
                    </h5>
                    <small class="text-white-50">Isi form di bawah untuk mengajukan perubahan limit kredit</small>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('ajuan-limit-kredit.store') }}" method="POST" id="formAjuan">
                    @csrf

                    {{-- Pilih Pelanggan --}}
                    <div class="mb-4">
                        <p class="form-section-label"><i class="fa-solid fa-user me-1"></i> Data Pelanggan</p>
                        <label class="form-label fw-semibold">Pilih Pelanggan <span class="text-danger">*</span></label>
                        <select name="kode_pelanggan" id="kode_pelanggan" class="form-select @error('kode_pelanggan') is-invalid @enderror" required>
                            <option value="">-- Pilih Pelanggan --</option>
                            @foreach($pelanggans as $p)
                                <option value="{{ $p->kode_pelanggan }}"
                                    data-limit="{{ $p->limit_pelanggan }}"
                                    data-nama="{{ $p->nama_pelanggan }}"
                                    data-alamat="{{ $p->alamat_pelanggan }}"
                                    {{ (old('kode_pelanggan', $selectedPelanggan?->kode_pelanggan) == $p->kode_pelanggan) ? 'selected' : '' }}>
                                    {{ $p->kode_pelanggan }} — {{ $p->nama_pelanggan }}
                                </option>
                            @endforeach
                        </select>
                        @error('kode_pelanggan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Info Pelanggan --}}
                    <div id="pelanggan-info" class="pelanggan-info-card p-4 mb-4 {{ $selectedPelanggan ? '' : 'd-none' }}">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-7">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary bg-opacity-15 d-flex align-items-center justify-content-center"
                                        style="width:50px;height:50px;flex-shrink:0">
                                        <i class="fa-solid fa-user text-primary fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-5" id="info-nama">-</div>
                                        <small class="text-muted" id="info-alamat">-</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5 text-md-end">
                                <small class="text-secondary d-block mb-1">Limit Kredit Saat Ini</small>
                                <div class="fw-bold text-primary fs-5" id="info-limit">Rp 0</div>
                            </div>
                        </div>
                    </div>

                    {{-- Limit Baru --}}
                    <div class="mb-4">
                        <p class="form-section-label"><i class="fa-solid fa-sliders me-1"></i> Perubahan Limit</p>
                        <label class="form-label fw-semibold">Limit Kredit Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text fw-semibold text-secondary">Rp</span>
                            <input type="number" name="limit_baru" id="limit_baru" min="0" step="1000"
                                class="form-control form-control-lg @error('limit_baru') is-invalid @enderror"
                                placeholder="0"
                                value="{{ old('limit_baru') }}" required>
                            @error('limit_baru')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- Preview Perubahan --}}
                        <div id="limit-preview" class="mt-3 d-none">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="text-center">
                                    <div class="text-muted small mb-1">Limit Lama</div>
                                    <div class="fw-semibold text-secondary" id="preview-lama">Rp 0</div>
                                </div>
                                <i class="fa-solid fa-arrow-right text-primary fs-5"></i>
                                <div class="text-center">
                                    <div class="text-muted small mb-1">Limit Baru</div>
                                    <div class="fw-bold text-primary" id="preview-baru">Rp 0</div>
                                </div>
                                <div id="preview-diff-badge" class="ms-2"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Alasan --}}
                    <div class="mb-4">
                        <p class="form-section-label"><i class="fa-solid fa-comment-dots me-1"></i> Keterangan Ajuan</p>
                        <label class="form-label fw-semibold">Alasan Pengajuan <span class="text-danger">*</span></label>
                        <textarea name="alasan" id="alasan" rows="4"
                            class="form-control @error('alasan') is-invalid @enderror"
                            placeholder="Tuliskan alasan pengajuan perubahan limit kredit secara jelas..."
                            maxlength="1000" required>{{ old('alasan') }}</textarea>
                        <div class="d-flex justify-content-between mt-1">
                            @error('alasan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted ms-auto">
                                <span id="alasan-count">0</span>/1000
                            </small>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2 justify-content-end pt-2 border-top">
                        <a href="{{ route('ajuan-limit-kredit.index') }}" class="btn btn-outline-secondary px-4">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary fw-bold px-5">
                            <i class="fa-solid fa-paper-plane me-2"></i> Submit Ajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function formatRupiah(angka) {
        return 'Rp ' + parseInt(angka || 0).toLocaleString('id-ID');
    }

    let limitLama = 0;

    function updatePelangganInfo(select) {
        if (!select || select.selectedIndex < 0) return;
        const opt = select.options[select.selectedIndex];
        if (!opt || !opt.value) {
            document.getElementById('pelanggan-info').classList.add('d-none');
            document.getElementById('limit-preview').classList.add('d-none');
            return;
        }
        limitLama = parseFloat(opt.getAttribute('data-limit') || opt.dataset.limit) || 0;
        document.getElementById('info-nama').textContent = opt.getAttribute('data-nama') || opt.dataset.nama || '';
        document.getElementById('info-alamat').textContent = opt.getAttribute('data-alamat') || opt.dataset.alamat || '';
        document.getElementById('info-limit').textContent = formatRupiah(limitLama);
        document.getElementById('pelanggan-info').classList.remove('d-none');
        updatePreview();
    }

    function updatePreview() {
        const limitBaru = parseFloat(document.getElementById('limit_baru').value) || 0;
        if (limitBaru <= 0) {
            document.getElementById('limit-preview').classList.add('d-none');
            return;
        }
        document.getElementById('limit-preview').classList.remove('d-none');
        document.getElementById('preview-lama').textContent = formatRupiah(limitLama);
        document.getElementById('preview-baru').textContent = formatRupiah(limitBaru);

        const diff = limitBaru - limitLama;
        const badge = document.getElementById('preview-diff-badge');
        if (diff > 0) {
            badge.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                <i class="fa-solid fa-arrow-up me-1"></i>+${formatRupiah(diff)}
            </span>`;
        } else if (diff < 0) {
            badge.innerHTML = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">
                <i class="fa-solid fa-arrow-down me-1"></i>${formatRupiah(diff)}
            </span>`;
        } else {
            badge.innerHTML = `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">
                <i class="fa-solid fa-equals me-1"></i>Tidak Berubah
            </span>`;
        }
    }

    $(document).ready(function() {
        $('#kode_pelanggan').select2({
            theme: 'bootstrap-5',
            width: '100%',
            ajax: {
                url: '{{ route('pelanggan.search') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                cache: true
            },
            minimumInputLength: 0
        });

        $('#kode_pelanggan').on('select2:select', function(e) {
            const data = e.params.data;
            const opt = $(this).find(':selected');
            opt.attr('data-limit', data.limit);
            opt.attr('data-nama', data.nama);
            opt.attr('data-alamat', data.alamat);

            updatePelangganInfo(this);
        });

        $('#kode_pelanggan').on('change', function () {
            updatePelangganInfo(this);
        });

        // Init if selected
        const sel = document.getElementById('kode_pelanggan');
        if (sel && sel.value) updatePelangganInfo(sel);
    });

    document.getElementById('limit_baru').addEventListener('input', updatePreview);

    // Char counter alasan
    const alasanEl = document.getElementById('alasan');
    const alasanCount = document.getElementById('alasan-count');
    alasanEl.addEventListener('input', function () {
        alasanCount.textContent = this.value.length;
    });
    alasanCount.textContent = alasanEl.value.length;
</script>
@endpush
