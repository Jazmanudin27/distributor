@extends('layouts.app')

@section('title', 'Update Harga Barang Masal')

@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <!-- Header Card -->
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-tags me-2"></i> Update Harga Masal
                </h5>
                <small class="text-white-50">Sesuaikan harga pokok dan harga jual produk secara serentak</small>
            </div>
            <a href="{{ route('barang.index') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                <i class="fa-solid fa-arrow-left me-1 text-primary"></i> Kembali ke Barang
            </a>
        </div>

        <div class="card-body p-4">
            <!-- FILTER SECTION -->
            <div class="bg-light p-3 rounded mb-4 border">
                <form action="{{ route('barang.edit-harga-masal') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Cari Barang</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Nama atau Kode..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Kategori</label>
                        <select name="kategori" class="form-select form-select-sm">
                            <option value="">Semua Kategori</option>
                            @foreach ($kategoris as $k)
                                <option value="{{ $k->nama_kategori }}"
                                    {{ request('kategori') == $k->nama_kategori ? 'selected' : '' }}>
                                    {{ $k->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Merk</label>
                        <select name="merk" class="form-select form-select-sm">
                            <option value="">Semua Merk</option>
                            @foreach ($merks as $m)
                                <option value="{{ $m->nama_merk }}"
                                    {{ request('merk') == $m->nama_merk ? 'selected' : '' }}>
                                    {{ $m->nama_merk }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Supplier</label>
                        <select name="kode_supplier" class="form-select form-select-sm">
                            <option value="">Semua Supplier</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->kode_supplier }}"
                                    {{ request('kode_supplier') == $s->kode_supplier ? 'selected' : '' }}>
                                    {{ $s->nama_supplier }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold" title="Filter Data">
                            <i class="fa-solid fa-filter"></i> Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- BULK ADJUSTMENT TOOL -->
            <div class="card border-primary-subtle bg-light mb-4">
                <div class="card-header bg-primary text-white py-2 fs-7 fw-bold">
                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Alat Penyesuaian Harga Masal
                </div>
                <div class="card-body p-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label fs-7 fw-bold text-secondary mb-1">Target Penyesuaian</label>
                            <select id="bulk_target" class="form-select form-select-sm">
                                <option value="jual">Harga Jual</option>
                                <option value="pokok">Harga Pokok</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-7 fw-bold text-secondary mb-1">Metode Operasi</label>
                            <select id="bulk_operator" class="form-select form-select-sm">
                                <option value="add_percent">Tambah Persentase (+%)</option>
                                <option value="sub_percent">Kurang Persentase (-%)</option>
                                <option value="add_nominal">Tambah Rupiah (+Rp)</option>
                                <option value="sub_nominal">Kurang Rupiah (-Rp)</option>
                                <option value="set_value">Atur Nilai Baru (=Rp)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-7 fw-bold text-secondary mb-1">Nilai Perubahan</label>
                            <input type="text" id="bulk_value" class="form-control form-control-sm text-end"
                                placeholder="Contoh: 10 atau 5.000">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-7 fw-bold text-secondary mb-1">Pembulatan Hasil</label>
                            <select id="bulk_rounding" class="form-select form-select-sm">
                                <option value="none">Tanpa Pembulatan</option>
                                <option value="round_up_100">Pembulatan Ke Atas Kelipatan 100</option>
                                <option value="round_down_100">Pembulatan Ke Bawah Kelipatan 100</option>
                                <option value="round_nearest_100">Pembulatan Terdekat Kelipatan 100</option>
                                <option value="round_nearest_500">Kelipatan 500</option>
                                <option value="round_nearest_1000">Kelipatan 1.000</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="button" id="btn-apply-bulk" class="btn btn-warning btn-sm fw-bold w-100"
                                title="Terapkan rumus ke item yang dipilih">
                                <i class="fa-solid fa-calculator me-1"></i> Hitung
                            </button>
                            <button type="button" id="btn-reset-bulk" class="btn btn-secondary btn-sm fw-bold"
                                title="Reset ke harga asli">
                                <i class="fa-solid fa-undo"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORM UPDATE MASAL -->
            <form id="form-update-masal" action="{{ route('barang.update-harga-masal') }}" method="POST">
                @csrf

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="badge bg-secondary font-monospace" id="checked-counter">0 item dipilih</span>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm fw-bold" id="btn-submit-prices" disabled>
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan Harga
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle" id="table-prices">
                        <thead class="table-light text-secondary text-uppercase fs-7 tracking-wider">
                            <tr>
                                <th width="40" class="text-center">
                                    <input type="checkbox" class="form-check-input" id="check-all">
                                </th>
                                <th width="120">Kode Barang</th>
                                <th>Nama Barang</th>
                                <th width="180">Kategori / Merk</th>
                                <th width="230">Satuan & Harga Pokok/Jual (Ke Samping)</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($items as $item)
                                <tr class="product-row">
                                    <!-- Checkbox Master Row -->
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input product-group-checkbox">
                                    </td>
                                    <!-- Kode Barang -->
                                    <td>
                                        <span class="badge bg-secondary font-monospace py-1.5 px-2">
                                            {{ $item->kode_barang }}
                                        </span>
                                    </td>
                                    <!-- Nama Barang -->
                                    <td class="fw-semibold text-dark">
                                        {{ $item->nama_barang }}
                                    </td>
                                    <!-- Kategori / Merk -->
                                    <td class="text-secondary small fw-semibold">
                                        <div>{{ $item->kategori ?? '-' }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $item->merk ?? '-' }}
                                        </div>
                                    </td>
                                    <!-- Units (Grouped side-by-side) -->
                                    <td class="py-2">
                                        <div class="d-flex flex-wrap gap-3">
                                            @foreach ($item->satuans as $satuan)
                                                @php
                                                    $hargaPokok = (float) $satuan->harga_pokok;
                                                    $hargaJual = (float) $satuan->harga_jual;
                                                    $marginRp = $hargaJual - $hargaPokok;
                                                    $marginPct = $hargaPokok > 0 ? ($marginRp / $hargaPokok) * 100 : 0;
                                                    $colorClass =
                                                        $marginRp < 0
                                                            ? 'text-danger'
                                                            : ($marginRp > 0
                                                                ? 'text-success'
                                                                : 'text-dark');
                                                @endphp
                                                <div class="price-row border rounded-3 p-2 bg-white shadow-xs d-flex align-items-center gap-2"
                                                    data-id="{{ $satuan->id }}"
                                                    style="border-color: #dee2e6 !important;">
                                                    <input type="checkbox" name="selected_ids[]"
                                                        value="{{ $satuan->id }}"
                                                        class="form-check-input row-checkbox me-1">
                                                    <div class="me-1">
                                                        <span
                                                            class="badge bg-info-subtle text-info border border-info-subtle font-monospace fw-bold mb-1"
                                                            style="display:inline-block;">
                                                            {{ $satuan->satuan }}
                                                        </span>
                                                        <div class="text-muted" style="font-size: 0.68rem;">Isi:
                                                            {{ $satuan->isi }}</div>
                                                    </div>

                                                    <!-- Harga Pokok Input -->
                                                    <div style="width: 125px;">
                                                        <div class="input-group input-group-sm">
                                                            <span
                                                                class="input-group-text bg-light text-secondary fs-8 px-1">P</span>
                                                            <input type="text" name="harga_pokok[{{ $satuan->id }}]"
                                                                class="form-control form-control-sm input-pokok text-success fw-semibold text-end px-1.5"
                                                                value="{{ number_format($satuan->harga_pokok, 0, ',', '.') }}"
                                                                data-original="{{ (int) $satuan->harga_pokok }}" disabled>
                                                        </div>
                                                        <div
                                                            class="text-muted text-end fs-9 mt-0.5 original-label text-end">
                                                            Asli:
                                                            {{ number_format($satuan->harga_pokok, 0, ',', '.') }}</div>
                                                    </div>

                                                    <!-- Harga Jual Input -->
                                                    <div style="width: 125px;">
                                                        <div class="input-group input-group-sm">
                                                            <span
                                                                class="input-group-text bg-light text-secondary fs-8 px-1">J</span>
                                                            <input type="text" name="harga_jual[{{ $satuan->id }}]"
                                                                class="form-control form-control-sm input-jual text-primary fw-bold text-end px-1.5"
                                                                value="{{ number_format($satuan->harga_jual, 0, ',', '.') }}"
                                                                data-original="{{ (int) $satuan->harga_jual }}" disabled>
                                                        </div>
                                                        <div
                                                            class="text-muted text-end fs-9 mt-0.5 original-label text-end">
                                                            Asli:
                                                            {{ number_format($satuan->harga_jual, 0, ',', '.') }}</div>
                                                    </div>

                                                    <!-- Margin Display -->
                                                    <div class="fs-8 text-end ps-2 border-start"
                                                        style="min-width: 80px; line-height: 1.25;">
                                                        <div class="fw-semibold margin-rp {{ $colorClass }}">
                                                            Rp {{ number_format($marginRp, 0, ',', '.') }}
                                                        </div>
                                                        <div class="fw-bold margin-pct {{ $colorClass }}">
                                                            {{ number_format($marginPct, 2, ',', '.') }}%
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-tags d-block fs-3 mb-2 opacity-50"></i>
                                        Tidak ada data barang yang ditemukan. Silakan sesuaikan filter pencarian
                                        Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($items->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted small">
                            Menampilkan {{ $items->firstItem() }} sampai {{ $items->lastItem() }} dari
                            {{ $items->total() }} data
                        </div>
                        <div>
                            {{ $items->links() }}
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Format format rupiah helper
            function formatRupiah(number) {
                return 'Rp ' + new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(number);
            }

            // Number format helpers
            function formatNumber(num) {
                return num.toString().replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function cleanNumber(str) {
                let s = str.toString();
                if (s.includes('e') || s.includes('E')) {
                    let num = parseFloat(s);
                    return isNaN(num) ? "0" : Math.round(num).toString();
                }
                return s.replace(/\./g, "").replace(/\D/g, "") || "0";
            }

            // Hitung Margin untuk satu baris
            function calculateMargin(row) {
                let pokokInput = row.find('.input-pokok');
                let jualInput = row.find('.input-jual');
                let marginRpSpan = row.find('.margin-rp');
                let marginPctSpan = row.find('.margin-pct');

                let pokok = parseFloat(cleanNumber(pokokInput.val())) || 0;
                let jual = parseFloat(cleanNumber(jualInput.val())) || 0;

                let marginRp = jual - pokok;
                let marginPct = pokok > 0 ? (marginRp / pokok) * 100 : 0;

                marginRpSpan.text(formatRupiah(marginRp));
                marginPctSpan.text(marginPct.toFixed(2) + '%');

                // Color coding
                if (marginRp < 0) {
                    marginRpSpan.addClass('text-danger').removeClass('text-success text-dark');
                    marginPctSpan.addClass('text-danger').removeClass('text-success text-dark');
                } else if (marginRp > 0) {
                    marginRpSpan.addClass('text-success').removeClass('text-danger text-dark');
                    marginPctSpan.addClass('text-success').removeClass('text-danger text-dark');
                } else {
                    marginRpSpan.addClass('text-dark').removeClass('text-danger text-success');
                    marginPctSpan.addClass('text-dark').removeClass('text-danger text-success');
                }
            }

            // Hitung semua margin saat load
            $('.price-row').each(function() {
                calculateMargin($(this));
            });

            // Update Counter dan Enable/Disable Submit Button
            function updateSelectionState() {
                let checkedCount = $('.row-checkbox:checked').length;
                $('#checked-counter').text(checkedCount + ' item dipilih');

                if (checkedCount > 0) {
                    $('#btn-submit-prices').prop('disabled', false);
                } else {
                    $('#btn-submit-prices').prop('disabled', true);
                }
            }

            // Toggling row checkbox
            $('.row-checkbox').on('change', function() {
                let row = $(this).closest('.price-row');
                let isChecked = $(this).is(':checked');

                // Enable/disable inputs
                row.find('.input-pokok, .input-jual').prop('disabled', !isChecked);

                if (isChecked) {
                    row.addClass('table-primary-subtle');
                } else {
                    row.removeClass('table-primary-subtle');
                }

                updateSelectionState();
            });

            // Toggle check all
            $('#check-all').on('change', function() {
                let isChecked = $(this).is(':checked');
                $('.row-checkbox').prop('checked', isChecked).trigger('change');
            });

            // Manual inputs change event with number formatting
            $(document).on('input', '.input-pokok, .input-jual', function() {
                const start = this.selectionStart;
                const prev = this.value.length;
                const raw = cleanNumber($(this).val());
                $(this).val(raw === "0" && $(this).val() === "" ? "" : formatNumber(raw));
                const diff = this.value.length - prev;
                this.setSelectionRange(start + diff, start + diff);

                let row = $(this).closest('.price-row');
                calculateMargin(row);

                // Add indicator if price is changed from original
                let pokok = parseFloat(cleanNumber(row.find('.input-pokok').val())) || 0;
                let originalPokok = parseFloat(row.find('.input-pokok').data('original')) || 0;
                let jual = parseFloat(cleanNumber(row.find('.input-jual').val())) || 0;
                let originalJual = parseFloat(row.find('.input-jual').data('original')) || 0;

                if (pokok !== originalPokok || jual !== originalJual) {
                    row.addClass('table-warning-subtle');
                } else {
                    row.removeClass('table-warning-subtle');
                }
            });

            // Formatting bulk_value field as user types
            $('#bulk_value').on('input', function() {
                let operator = $('#bulk_operator').val();
                if (operator.includes('percent')) {
                    // Allow decimal digits with dot
                    let val = $(this).val().replace(/,/g, '.').replace(/[^0-9.]/g, '');
                    const parts = val.split('.');
                    if (parts.length > 2) {
                        val = parts[0] + '.' + parts.slice(1).join('');
                    }
                    $(this).val(val);
                } else {
                    const start = this.selectionStart;
                    const prev = this.value.length;
                    const raw = cleanNumber($(this).val());
                    $(this).val(raw === "0" && $(this).val() === "" ? "" : formatNumber(raw));
                    const diff = this.value.length - prev;
                    this.setSelectionRange(start + diff, start + diff);
                }
            });

            $('#bulk_operator').on('change', function() {
                $('#bulk_value').val('').trigger('input');
            });

            // Apply bulk adjustment
            $('#btn-apply-bulk').on('click', function() {
                let target = $('#bulk_target').val(); // 'jual' or 'pokok'
                let operator = $('#bulk_operator')
                    .val(); // add_percent, sub_percent, add_nominal, sub_nominal, set_value
                let changeValStr = $('#bulk_value').val();
                let changeVal = 0;
                let rounding = $('#bulk_rounding').val();

                if (operator.includes('percent')) {
                    changeValStr = changeValStr.replace(/,/g, '.').replace(/[^0-9.]/g, '');
                    changeVal = parseFloat(changeValStr) || 0;
                } else {
                    changeVal = parseFloat(cleanNumber(changeValStr)) || 0;
                }

                if (isNaN(changeVal) || changeVal < 0 || changeValStr === '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nilai Tidak Valid',
                        text: 'Silakan masukkan nilai perubahan yang valid (positif).'
                    });
                    return;
                }

                let checkedRows = $('.row-checkbox:checked').closest('.price-row');
                if (checkedRows.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Pilih Item Terlebih Dahulu',
                        text: 'Silakan centang item pada tabel sebelum menerapkan penyesuaian harga.'
                    });
                    return;
                }

                checkedRows.each(function() {
                    let row = $(this);
                    let input = target === 'jual' ? row.find('.input-jual') : row.find(
                        '.input-pokok');
                    let currentVal = parseFloat(input.data('original')) || 0;
                    let newVal = currentVal;

                    // Hitung berdasarkan metode
                    switch (operator) {
                        case 'add_percent':
                            newVal = currentVal + (currentVal * (changeVal / 100));
                            break;
                        case 'sub_percent':
                            newVal = currentVal - (currentVal * (changeVal / 100));
                            break;
                        case 'add_nominal':
                            newVal = currentVal + changeVal;
                            break;
                        case 'sub_nominal':
                            newVal = currentVal - changeVal;
                            break;
                        case 'set_value':
                            newVal = changeVal;
                            break;
                    }

                    if (newVal < 0) newVal = 0;

                    // Terapkan Pembulatan
                    switch (rounding) {
                        case 'round_up_100':
                            newVal = Math.ceil(newVal / 100) * 100;
                            break;
                        case 'round_down_100':
                            newVal = Math.floor(newVal / 100) * 100;
                            break;
                        case 'round_nearest_100':
                            newVal = Math.round(newVal / 100) * 100;
                            break;
                        case 'round_nearest_500':
                            newVal = Math.round(newVal / 500) * 500;
                            break;
                        case 'round_nearest_1000':
                            newVal = Math.round(newVal / 1000) * 1000;
                            break;
                    }

                    // Tulis hasil ke input
                    input.val(formatNumber(Math.round(newVal))).trigger('input');
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Perhitungan Selesai',
                    text: 'Pratinjau harga telah berhasil dihitung. Silakan review kolom tabel sebelum menyimpan.',
                    timer: 2000,
                    showConfirmButton: false
                });
            });

            // Reset bulk tool
            $('#btn-reset-bulk').on('click', function() {
                let checkedRows = $('.row-checkbox:checked').closest('.price-row');
                if (checkedRows.length === 0) return;

                checkedRows.each(function() {
                    let row = $(this);
                    let pokokInput = row.find('.input-pokok');
                    let jualInput = row.find('.input-jual');

                    pokokInput.val(formatNumber(pokokInput.data('original'))).trigger('input');
                    jualInput.val(formatNumber(jualInput.data('original'))).trigger('input');
                    row.removeClass('table-warning-subtle');
                });

                $('#bulk_value').val('');
                $('#bulk_rounding').val('none');
            });

            // Submit handler with SweetAlert2 confirmation
            $('#form-update-masal').on('submit', function(e) {
                e.preventDefault();
                let form = this;
                let checkedCount = $('.row-checkbox:checked').length;

                Swal.fire({
                    title: 'Simpan Perubahan Harga?',
                    text: 'Apakah Anda yakin ingin memperbarui harga untuk ' + checkedCount +
                        ' satuan barang ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Simpan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
