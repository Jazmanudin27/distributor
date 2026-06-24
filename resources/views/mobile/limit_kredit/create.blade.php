@extends('layouts.mobile')

@section('title', 'Pengajuan Limit Baru')

@section('content')
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('mobile.limit-kredit.index') }}" class="btn btn-sm btn-link text-white-50 p-0 me-3">
            <i class="fa-solid fa-arrow-left fs-5"></i>
        </a>
        <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">Pengajuan Limit Baru</h5>
    </div>

    <!-- Info/Guidance Alert -->
    <div class="alert alert-info border-0 rounded-4 mb-4 p-3"
        style="background-color: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2) !important; color: #a5b4fc;">
        <div class="d-flex">
            <i class="fa-solid fa-circle-info mt-1 me-2" style="font-size: 1rem;"></i>
            <span style="font-size: 0.78rem; line-height: 1.4;">
                Gunakan menu ini untuk mengajukan kenaikan limit kredit pelanggan. Ajuan Anda akan dikirim ke Admin/Manajer
                untuk ditinjau dan disetujui.
            </span>
        </div>
    </div>

    <!-- Form Card -->
    <div class="mobile-card">
        <form action="{{ route('mobile.limit-kredit.store') }}" method="POST" id="form-ajuan-limit">
            @csrf

            <!-- Region Filter -->
            <div class="mb-3">
                <label class="form-label text-secondary small fw-semibold" style="letter-spacing: 0.5px;">FILTER WILAYAH (OPSIONAL)</label>
                <select id="search-wilayah-select" class="form-select form-control-mobile text-white" style="font-size: 0.85rem; background-color: #121824;">
                    <option value="">-- Semua Wilayah --</option>
                    @foreach($wilayahs as $w)
                        <option value="{{ $w->kode_wilayah }}">{{ $w->nama_wilayah }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Pelanggan Autocomplete Search -->
            <div class="mb-3 position-relative">
                <label class="form-label text-secondary small fw-semibold" style="letter-spacing: 0.5px;">PELANGGAN</label>
                <div class="input-group">
                    <span class="input-group-text"
                        style="background-color: #121824; border-color: rgba(255, 255, 255, 0.08); color: var(--text-secondary); border-top-left-radius: 12px; border-bottom-left-radius: 12px;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" id="customer-search-input"
                        class="form-control form-control-mobile @error('kode_pelanggan') is-invalid @enderror"
                        placeholder="Ketik nama atau kode toko..." autocomplete="off" required
                        value="{{ $selectedPelanggan ? $selectedPelanggan->nama_pelanggan . ' (' . $selectedPelanggan->kode_pelanggan . ')' : '' }}"
                        style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
                </div>
                <input type="hidden" name="kode_pelanggan" id="hidden-kode-pelanggan"
                    value="{{ old('kode_pelanggan') ?? ($selectedPelanggan->kode_pelanggan ?? '') }}"
                    data-limit="{{ $selectedPelanggan ? (float) $selectedPelanggan->limit_pelanggan : 0 }}">

                <!-- Results dropdown list -->
                <div id="search-results-list" class="list-group position-absolute w-100 shadow-lg mt-1 d-none"
                    style="z-index: 1000; max-height: 220px; overflow-y: auto; background-color: #161e31; border: 1px solid var(--border-color); border-radius: 12px;">
                    <!-- list-items will be appended by JS -->
                </div>

                @error('kode_pelanggan')
                    <div class="text-danger small mt-1">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Current Limit Display Card -->
            <div id="current-limit-card" class="p-3 rounded-4 mb-4 d-none"
                style="background-color: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-secondary small">Limit Kredit Saat Ini:</span>
                    <span class="fw-bold text-white fs-6" id="lbl-limit-lama">Rp 0</span>
                </div>
            </div>

            <!-- New Credit Limit Input -->
            <div class="mb-3">
                <label for="limit_baru" class="form-label text-secondary small fw-semibold"
                    style="letter-spacing: 0.5px;">LIMIT KREDIT BARU (RP)</label>
                <div class="input-group">
                    <span class="input-group-text"
                        style="background-color: #121824; border-color: rgba(255, 255, 255, 0.08); color: var(--text-secondary); border-top-left-radius: 12px; border-bottom-left-radius: 12px;">
                        Rp
                    </span>
                    <input type="text" name="limit_baru" id="limit_baru"
                        class="form-control form-control-mobile rupiah-input @error('limit_baru') is-invalid @enderror"
                        value="{{ old('limit_baru') }}" placeholder="Contoh: 15.000.000" min="0" required
                        style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
                </div>
                <!-- Dynamic Rupiah Formatting Helper -->
                <div class="text-info small mt-1" id="limit-baru-helper" style="font-size: 0.72rem; min-height: 18px;">
                </div>

                @error('limit_baru')
                    <div class="text-danger small mt-1">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Reason/Justification Textarea -->
            <div class="mb-4">
                <label for="alasan" class="form-label text-secondary small fw-semibold"
                    style="letter-spacing: 0.5px;">ALASAN PENGAJUAN</label>
                <textarea name="alasan" id="alasan" rows="4"
                    class="form-control form-control-mobile @error('alasan') is-invalid @enderror"
                    placeholder="Jelaskan mengapa toko ini membutuhkan kenaikan limit kredit (misal: volume pembelian meningkat, perluasan toko, rekam jejak bayar baik)..."
                    required>{{ old('alasan') }}</textarea>
                @error('alasan')
                    <div class="text-danger small mt-1">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-mobile btn-mobile-primary w-100 py-3">
                <i class="fa-solid fa-paper-plane me-2"></i> Kirim Pengajuan
            </button>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('customer-search-input');
            const resultsList = document.getElementById('search-results-list');
            const hiddenKode = document.getElementById('hidden-kode-pelanggan');
            const currentLimitCard = document.getElementById('current-limit-card');
            const lblLimitLama = document.getElementById('lbl-limit-lama');
            const inputLimitBaru = document.getElementById('limit_baru');
            const limitBaruHelper = document.getElementById('limit-baru-helper');
            const form = document.getElementById('form-ajuan-limit');
            const searchWilayah = document.getElementById('search-wilayah-select');

            let debounceTimer;

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    const query = this.value.trim();

                    if (query.length < 2) {
                        resultsList.classList.add('d-none');
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        const wilayahVal = searchWilayah ? searchWilayah.value : '';
                        fetch(`{{ route('pelanggan.search') }}?q=${encodeURIComponent(query)}&kode_wilayah=${encodeURIComponent(wilayahVal)}`)
                            .then(response => response.json())
                            .then(data => {
                                resultsList.innerHTML = '';
                                if (data.length === 0) {
                                    resultsList.innerHTML =
                                        '<div class="p-3 text-secondary text-center" style="font-size: 0.8rem;">Toko tidak ditemukan.</div>';
                                    resultsList.classList.remove('d-none');
                                    return;
                                }

                                data.forEach(item => {
                                    const btn = document.createElement('button');
                                    btn.type = 'button';
                                    btn.className =
                                        'list-group-item list-group-item-action text-white border-0 py-3 px-3 d-flex flex-column';
                                    btn.style.backgroundColor = 'transparent';
                                    btn.style.borderBottom =
                                        '1px solid rgba(255,255,255,0.05) !important';
                                    btn.innerHTML = `
                                        <span class="fw-semibold text-white" style="font-size: 0.85rem;">${item.text}</span>
                                        <span class="text-secondary mt-1" style="font-size: 0.75rem;">${item.alamat}</span>
                                    `;
                                    btn.addEventListener('click', () => {
                                        selectCustomer(item);
                                    });
                                    resultsList.appendChild(btn);
                                });
                                resultsList.classList.remove('d-none');
                            })
                            .catch(err => {
                                console.error('Error searching customers:', err);
                            });
                    }, 300);
                });

                // Clear hidden value if searchInput is emptied
                searchInput.addEventListener('input', function() {
                    if (this.value.trim() === '') {
                        hiddenKode.value = '';
                        hiddenKode.setAttribute('data-limit', 0);
                        updateCurrentLimit();
                    }
                });

                if (searchWilayah) {
                    $(searchWilayah).select2({
                        placeholder: "-- Semua Wilayah --",
                        allowClear: true
                    }).on('change', function() {
                        if (searchInput.value.trim().length >= 2) {
                            searchInput.dispatchEvent(new Event('input'));
                        }
                    });
                }

                // Close search dropdown on click outside
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !resultsList.contains(e.target)) {
                        resultsList.classList.add('d-none');
                    }
                });
            }

            function selectCustomer(customer) {
                resultsList.classList.add('d-none');
                searchInput.value = customer.text;
                hiddenKode.value = customer.id;
                hiddenKode.setAttribute('data-limit', customer.limit);
                searchInput.classList.remove('is-invalid');
                updateCurrentLimit();
            }

            // Function to update current limit info card
            function updateCurrentLimit() {
                const limitVal = parseFloat(hiddenKode.getAttribute('data-limit')) || 0;
                if (hiddenKode.value) {
                    lblLimitLama.innerText = 'Rp ' + limitVal.toLocaleString('id-ID');
                    currentLimitCard.classList.remove('d-none');
                } else {
                    currentLimitCard.classList.add('d-none');
                }
            }

            // Function to update formatted currency preview helper
            function updateLimitHelper() {
                const rawVal = inputLimitBaru.value.replace(/\./g, '').replace(/,/g, '.').trim();
                if (rawVal && !isNaN(rawVal)) {
                    const parsed = parseFloat(rawVal);
                    limitBaruHelper.innerText = 'Format Terbaca: Rp ' + parsed.toLocaleString('id-ID');
                } else {
                    limitBaruHelper.innerText = '';
                }
            }

            // Form validation before submit
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!hiddenKode.value) {
                        e.preventDefault();
                        searchInput.classList.add('is-invalid');
                        searchInput.focus();
                        return false;
                    }
                });
            }

            // Listen to input type event
            inputLimitBaru.addEventListener('input', updateLimitHelper);

            // Run on initial load in case of old values or pre-selected pelanggan
            updateCurrentLimit();
            updateLimitHelper();
        });
    </script>
@endpush
