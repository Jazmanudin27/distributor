@extends('layouts.app')
@section('title', $item->exists ? 'Edit Diskon Strata' : 'Tambah Diskon Strata Baru')
@section('content')
    <div class="row justify-content-start">
        <div class="col-lg-10 col-md-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header card-premium-header text-white py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="me-3 bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                            style="width: 45px; height: 45px;">
                            <i class="fa-solid {{ $item->exists ? 'fa-pen-to-square' : 'fa-plus' }} fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $item->exists ? 'Edit Aturan Diskon Strata' : 'Tambah Diskon Strata Baru' }}</h5>
                            <small class="text-white-50">{{ $item->exists ? 'Perbarui kriteria dan tingkatan/strata diskon' : 'Buat aturan diskon berjenjang baru' }}</small>
                        </div>
                    </div>
                    <a href="{{ route('diskon-strata.index') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                        <i class="fa-solid fa-arrow-left me-1 text-primary"></i> Kembali
                    </a>
                </div>

                <div class="card-body p-4 bg-light">
                    <form action="{{ $item->exists ? route('diskon-strata.update', $item->id) : route('diskon-strata.store') }}"
                        method="POST" id="diskonStrataForm">
                        @csrf
                        @if ($item->exists)
                            @method('PUT')
                        @endif

                        <div class="row g-3">
                            {{-- Header Data --}}
                            <div class="col-md-6">
                                <div class="card p-3 border rounded bg-white h-100 mb-0 shadow-sm">
                                    <h6 class="fw-bold text-secondary text-uppercase fs-8 mb-3 border-bottom pb-2">
                                        <i class="fa-solid fa-circle-info text-primary me-1"></i> Data Aturan
                                    </h6>
                                    
                                    <div class="mb-2">
                                        <label for="nama_diskon" class="form-label fs-7 fw-bold text-secondary">Nama Promo/Diskon <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_diskon" id="nama_diskon"
                                            class="form-control form-control-sm @error('nama_diskon') is-invalid @enderror"
                                            placeholder="Contoh: Diskon Strata Barang ABC"
                                            value="{{ old('nama_diskon', $item->nama_diskon) }}" required>
                                        @error('nama_diskon')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-2">
                                        <label for="tipe" class="form-label fs-7 fw-bold text-secondary">Tipe Aturan Diskon <span class="text-danger">*</span></label>
                                        <select name="tipe" id="tipe" class="form-select form-select-sm" required {{ $item->exists ? 'disabled' : '' }}>
                                            <option value="">-- Pilih Tipe --</option>
                                            <option value="barang" {{ old('tipe', $item->tipe) === 'barang' ? 'selected' : '' }}>Per Barang</option>
                                            <option value="beberapa_barang" {{ old('tipe', $item->tipe) === 'beberapa_barang' ? 'selected' : '' }}>Per Beberapa Barang</option>
                                            <option value="kategori" {{ old('tipe', $item->tipe) === 'kategori' ? 'selected' : '' }}>Per Kategori</option>
                                            <option value="merk" {{ old('tipe', $item->tipe) === 'merk' ? 'selected' : '' }}>Per Merk</option>
                                            <option value="supplier" {{ old('tipe', $item->tipe) === 'supplier' ? 'selected' : '' }}>Per Supplier (Nominal)</option>
                                        </select>
                                        @if ($item->exists)
                                            <input type="hidden" name="tipe" value="{{ $item->tipe }}">
                                        @endif
                                    </div>

                                    <div class="mb-0">
                                        <div class="form-check form-switch mt-3">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" 
                                                {{ old('is_active', $item->exists ? $item->is_active : 1) ? 'checked' : '' }}>
                                            <label class="form-check-label fs-7 fw-bold text-secondary" for="is_active">Aktifkan Aturan Diskon</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Target & Periode --}}
                            <div class="col-md-6">
                                <div class="card p-3 border rounded bg-white h-100 mb-0 shadow-sm">
                                    <h6 class="fw-bold text-secondary text-uppercase fs-8 mb-3 border-bottom pb-2">
                                        <i class="fa-solid fa-bullseye text-success me-1"></i> Target & Periode
                                    </h6>

                                    {{-- Scope Target Containers --}}
                                    <div id="target_barang_wrapper" class="target-section mb-2 d-none">
                                        <label for="barang_ids" class="form-label fs-7 fw-bold text-secondary" id="label_barang_ids">Pilih Barang <span class="text-danger">*</span></label>
                                        <select id="barang_ids" name="barang_ids[]" class="form-select form-select-sm select2-multiple" style="width: 100%;" multiple>
                                            @foreach ($barangs as $b)
                                                <option value="{{ $b->kode_barang }}" 
                                                    {{ in_array($b->kode_barang, old('barang_ids', $item->barangs->pluck('kode_barang')->toArray())) ? 'selected' : '' }}>
                                                    {{ $b->nama_barang }} ({{ $b->kode_barang }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div id="target_kategori_wrapper" class="target-section mb-2 d-none">
                                        <label for="kategori_id" class="form-label fs-7 fw-bold text-secondary">Pilih Kategori <span class="text-danger">*</span></label>
                                        <select name="kategori_id" id="kategori_id" class="form-select form-select-sm">
                                            <option value="">-- Pilih Kategori --</option>
                                            @foreach ($kategoris as $k)
                                                <option value="{{ $k->id }}" {{ old('kategori_id', $item->kategori_id) == $k->id ? 'selected' : '' }}>
                                                    {{ $k->nama_kategori }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div id="target_merk_wrapper" class="target-section mb-2 d-none">
                                        <label for="merk_id" class="form-label fs-7 fw-bold text-secondary">Pilih Merk <span class="text-danger">*</span></label>
                                        <select name="merk_id" id="merk_id" class="form-select form-select-sm">
                                            <option value="">-- Pilih Merk --</option>
                                            @foreach ($merks as $m)
                                                <option value="{{ $m->id }}" {{ old('merk_id', $item->merk_id) == $m->id ? 'selected' : '' }}>
                                                    {{ $m->nama_merk }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div id="target_supplier_wrapper" class="target-section mb-2 d-none">
                                        <label for="kode_supplier" class="form-label fs-7 fw-bold text-secondary">Pilih Supplier <span class="text-danger">*</span></label>
                                        <select name="kode_supplier" id="kode_supplier" class="form-select form-select-sm">
                                            <option value="">-- Pilih Supplier --</option>
                                            @foreach ($suppliers as $s)
                                                <option value="{{ $s->kode_supplier }}" {{ old('kode_supplier', $item->kode_supplier) == $s->kode_supplier ? 'selected' : '' }}>
                                                    {{ $s->nama_supplier }} ({{ $s->kode_supplier }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label for="berlaku_dari" class="form-label fs-7 fw-bold text-secondary">Mulai Berlaku</label>
                                            <input type="datetime-local" name="berlaku_dari" id="berlaku_dari" class="form-control form-control-sm"
                                                value="{{ old('berlaku_dari', $item->berlaku_dari ? $item->berlaku_dari->format('Y-m-d\TH:i') : '') }}">
                                        </div>
                                        <div class="col-6">
                                            <label for="berlaku_sampai" class="form-label fs-7 fw-bold text-secondary">Selesai Berlaku</label>
                                            <input type="datetime-local" name="berlaku_sampai" id="berlaku_sampai" class="form-control form-control-sm"
                                                value="{{ old('berlaku_sampai', $item->berlaku_sampai ? $item->berlaku_sampai->format('Y-m-d\TH:i') : '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tiers / Strata Details Table --}}
                        <div class="card border-0 shadow-sm p-4 rounded bg-white mt-4">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                <h6 class="fw-bold text-secondary text-uppercase fs-8 mb-0">
                                    <i class="fa-solid fa-list-ol text-primary me-1"></i> Tingkatan / Strata Diskon
                                </h6>
                                <button type="button" class="btn btn-outline-primary btn-sm fw-bold" id="btn-add-tier">
                                    <i class="fa-solid fa-plus me-1"></i> Tambah Strata / Tier
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle fs-7" id="tiersTable">
                                    <thead class="table-light text-secondary text-uppercase tracking-wider">
                                        <tr>
                                            <th class="text-center" width="50">No</th>
                                            <th class="text-center col-range-1" width="180">Min Qty</th>
                                            <th class="text-center col-range-2" width="180">Max Qty (Kosongkan jika ∞)</th>
                                            <th class="text-center" width="130">Tipe Nilai</th>
                                            <th class="text-center">Diskon Reguler (dis1)</th>
                                            <th class="text-center">Diskon Cash (dis2)</th>
                                            <th class="text-center" width="50">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Rows added dynamically --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Action buttons --}}
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('diskon-strata.index') }}" class="btn btn-light px-4 fw-semibold border hover-scale">
                                <i class="fa-solid fa-arrow-left me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold hover-scale">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Aturan
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
        $(document).ready(function() {
            let detailIndex = 0;
            const existingDetails = {!! json_encode($item->details ?? []) !!};

            // Initialize select2 multiple
            $('#barang_ids').select2({
                theme: 'bootstrap-5',
                placeholder: 'Cari / pilih barang...',
                allowClear: true,
                width: '100%'
            });

            // Handle type changes
            $('#tipe').on('change', function() {
                const type = $(this).val();
                $('.target-section').addClass('d-none').find('input, select').removeAttr('required');

                if (type === 'barang' || type === 'beberapa_barang') {
                    $('#target_barang_wrapper').removeClass('d-none');
                    $('#barang_ids').attr('required', 'required');
                    
                    if (type === 'barang') {
                        $('#label_barang_ids').html('Pilih Barang <span class="text-danger">*</span>');
                        $('#barang_ids').attr('multiple', false).select2({
                            theme: 'bootstrap-5',
                            placeholder: 'Pilih satu barang...',
                            maximumSelectionLength: 1,
                            width: '100%'
                        });
                    } else {
                        $('#label_barang_ids').html('Pilih Beberapa Barang <span class="text-danger">*</span>');
                        $('#barang_ids').attr('multiple', 'multiple').select2({
                            theme: 'bootstrap-5',
                            placeholder: 'Pilih beberapa barang...',
                            width: '100%'
                        });
                    }
                } else if (type === 'kategori') {
                    $('#target_kategori_wrapper').removeClass('d-none');
                    $('#kategori_id').attr('required', 'required');
                } else if (type === 'merk') {
                    $('#target_merk_wrapper').removeClass('d-none');
                    $('#merk_id').attr('required', 'required');
                } else if (type === 'supplier') {
                    $('#target_supplier_wrapper').removeClass('d-none');
                    $('#kode_supplier').attr('required', 'required');
                }

                // Adjust Table Headers for ranges
                if (type === 'supplier') {
                    $('.col-range-1').text('Min Nominal Pembelian');
                    $('.col-range-2').text('Max Nominal Pembelian');
                } else {
                    $('.col-range-1').text('Min Qty');
                    $('.col-range-2').text('Max Qty (Kosongkan jika ∞)');
                }

                // If not editing, clear rows on type change to reset fields format
                if (!{!! json_encode($item->exists) !!} || detailIndex === 0) {
                    $('#tiersTable tbody').empty();
                    addTierRow();
                } else {
                    adjustRowInputsFormat(type);
                }
            });

            // Add new tier row
            $('#btn-add-tier').on('click', function() {
                addTierRow();
            });

            // Remove row
            $(document).on('click', '.btn-remove-tier', function() {
                if ($('#tiersTable tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    reindexRows();
                } else {
                    Swal.fire('Informasi', 'Aturan diskon minimal harus memiliki 1 tingkatan (strata)!', 'info');
                }
            });

            function addTierRow(data = null) {
                const type = $('#tipe').val() || 'barang';
                const isSupplier = type === 'supplier';
                
                const minVal = data ? (isSupplier ? data.min_nominal : data.min_qty) : '';
                const maxVal = data ? (isSupplier ? data.max_nominal : data.max_qty) : '';
                const tipeNilai = data ? data.tipe_nilai : (isSupplier ? 'nominal' : 'persen');
                const dis1 = data ? data.dis1 : 0;
                const dis2 = data ? data.dis2 : 0;

                const colRange1Html = isSupplier 
                    ? `<div class="input-group input-group-sm">
                           <span class="input-group-text">Rp</span>
                           <input type="number" name="details[${detailIndex}][min_nominal]" class="form-control text-end input-min" value="${minVal || 0}" min="0" required>
                       </div>`
                    : `<input type="number" name="details[${detailIndex}][min_qty]" class="form-control form-control-sm text-end input-min" value="${minVal || 1}" min="1" required>`;

                const colRange2Html = isSupplier 
                    ? `<div class="input-group input-group-sm">
                           <span class="input-group-text">Rp</span>
                           <input type="number" name="details[${detailIndex}][max_nominal]" class="form-control text-end input-max" value="${maxVal || ''}" min="0">
                       </div>`
                    : `<input type="number" name="details[${detailIndex}][max_qty]" class="form-control form-control-sm text-end input-max" value="${maxVal || ''}" min="1">`;

                const html = `
                    <tr class="tier-row">
                        <td class="text-center row-num fw-bold text-secondary"></td>
                        <td>${colRange1Html}</td>
                        <td>${colRange2Html}</td>
                        <td>
                            <select name="details[${detailIndex}][tipe_nilai]" class="form-select form-select-sm select-tipe-nilai">
                                <option value="persen" ${tipeNilai === 'persen' ? 'selected' : ''}>Persen (%)</option>
                                <option value="nominal" ${tipeNilai === 'nominal' ? 'selected' : ''}>Nominal (Rp)</option>
                            </select>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text addon-dis1">${tipeNilai === 'persen' ? '%' : 'Rp'}</span>
                                <input type="number" name="details[${detailIndex}][dis1]" class="form-control text-end" step="any" min="0" value="${parseFloat(dis1)}" required>
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text addon-dis2">${tipeNilai === 'persen' ? '%' : 'Rp'}</span>
                                <input type="number" name="details[${detailIndex}][dis2]" class="form-control text-end" step="any" min="0" value="${parseFloat(dis2)}" required>
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-tier rounded-circle" style="width: 28px; height: 28px; padding: 2px;">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>`;

                $('#tiersTable tbody').append(html);
                detailIndex++;
                reindexRows();
            }

            function reindexRows() {
                let num = 1;
                $('#tiersTable tbody tr').each(function() {
                    $(this).find('.row-num').text(num++);
                });
            }

            function adjustRowInputsFormat(type) {
                const isSupplier = type === 'supplier';
                $('#tiersTable tbody tr').each(function() {
                    const row = $(this);
                    const minInput = row.find('.input-min');
                    const maxInput = row.find('.input-max');

                    // If supplier, wrap with Rp addon, else make simple number
                    if (isSupplier) {
                        if (!minInput.parent().hasClass('input-group')) {
                            const minVal = minInput.val();
                            minInput.replaceWith(`
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="${minInput.attr('name').replace('min_qty', 'min_nominal')}" class="form-control text-end input-min" value="${minVal || 0}" min="0" required>
                                </div>
                            `);
                            const maxVal = maxInput.val();
                            maxInput.replaceWith(`
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="${maxInput.attr('name').replace('max_qty', 'max_nominal')}" class="form-control text-end input-max" value="${maxVal || ''}" min="0">
                                </div>
                            `);
                            row.find('.select-tipe-nilai').val('nominal').trigger('change');
                        }
                    } else {
                        if (minInput.parent().hasClass('input-group')) {
                            // Extract input from group
                            const unwrappedMin = row.find('input.input-min');
                            const minVal = unwrappedMin.val();
                            row.find('td:nth-child(2)').html(`
                                <input type="number" name="${unwrappedMin.attr('name').replace('min_nominal', 'min_qty')}" class="form-control form-control-sm text-end input-min" value="${minVal || 1}" min="1" required>
                            `);

                            const unwrappedMax = row.find('input.input-max');
                            const maxVal = unwrappedMax.val();
                            row.find('td:nth-child(3)').html(`
                                <input type="number" name="${unwrappedMax.attr('name').replace('max_nominal', 'max_qty')}" class="form-control form-control-sm text-end input-max" value="${maxVal || ''}" min="1">
                            `);
                            row.find('.select-tipe-nilai').val('persen').trigger('change');
                        }
                    }
                });
            }

            // Tipe nilai dropdown changes (change % or Rp label addon)
            $(document).on('change', '.select-tipe-nilai', function() {
                const val = $(this).val();
                const tr = $(this).closest('tr');
                if (val === 'persen') {
                    tr.find('.addon-dis1, .addon-dis2').text('%');
                } else {
                    tr.find('.addon-dis1, .addon-dis2').text('Rp');
                }
            });

            // Trigger initial state
            $('#tipe').trigger('change');

            // Load existing details
            if (existingDetails.length > 0) {
                $('#tiersTable tbody').empty();
                existingDetails.forEach(d => {
                    addTierRow(d);
                });
            }

            // Client-side validations before submit
            $('#diskonStrataForm').on('submit', function(e) {
                const type = $('#tipe').val();
                if (!type) {
                    e.preventDefault();
                    return Swal.fire('Peringatan', 'Silakan pilih Tipe Aturan Diskon!', 'warning');
                }

                // Check ranges validation
                let isValid = true;
                $('#tiersTable tbody tr').each(function() {
                    const row = $(this);
                    const min = parseFloat(row.find('.input-min').val()) || 0;
                    const maxVal = row.find('.input-max').val();
                    const max = parseFloat(maxVal);

                    if (maxVal !== '' && max < min) {
                        isValid = false;
                        row.find('.input-max').addClass('is-invalid');
                    } else {
                        row.find('.input-max').removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    return Swal.fire('Error Validasi', 'Batas Maksimum strata tidak boleh lebih kecil dari batas Minimum!', 'error');
                }
            });
        });
    </script>
@endpush
