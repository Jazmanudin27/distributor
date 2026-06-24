@extends('layouts.app')
@section('title', 'DPB (Data Pengambilan Barang)')
@section('content')
    <div class="row justify-content-start">
        <div class="col-lg-10 col-md-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div
                    class="card-header card-premium-header text-white py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="me-3 bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                            style="width: 45px; height: 45px;">
                            <i class="fa-solid fa-truck-ramp-box fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Mulai DPB Baru (Data Pengambilan Barang)</h5>
                            <small class="text-white-50 font-12">Catat pengambilan barang dari gudang ke sales kanvas</small>
                        </div>
                    </div>
                    <a href="{{ route('canvas.index') }}" class="btn btn-primary btn-sm fw-bold hover-scale">
                        <i class="fa-solid fa-arrow-left me-1 text-white"></i> Kembali
                    </a>
                </div>
                <div class="card-body p-4">
                    {{-- Error display --}}
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('canvas.store') }}" method="POST">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label for="kode_sales" class="form-label fs-7 fw-bold text-secondary">Pilih Salesman
                                    Kanvas</label>
                                <select name="kode_sales" id="kode_sales"
                                    class="form-select form-select-sm select2-salesman" required>
                                    <option value="">-- Pilih Salesman --</option>
                                    @foreach ($salesmen as $s)
                                        <option value="{{ $s->nik }}"
                                            {{ old('kode_sales') == $s->nik ? 'selected' : '' }}>
                                            {{ $s->name }} (NIK: {{ $s->nik }})
                                            {{ $s->is_kanvas ? '[Kanvas]' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted font-11 mt-1 d-block">Pastikan salesman memiliki status "Kanvas"
                                    agar pembayaran/penjualan sinkron otomatis.</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="tanggal" class="form-label fs-7 fw-bold text-secondary">Tanggal
                                    Pengambilan</label>
                                <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm"
                                    value="{{ old('tanggal', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="keterangan" class="form-label fs-7 fw-bold text-secondary">Keterangan /
                                    Catatan</label>
                                <textarea name="keterangan" id="keterangan" rows="1" class="form-control form-control-sm"
                                    placeholder="Catatan tambahan (opsional)...">{{ old('keterangan') }}</textarea>
                            </div>
                        </div>

                        {{-- QUICK INPUT BAR --}}
                        <div class="card border border-light p-3 rounded mb-4 bg-light shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                <h6 class="fw-bold text-dark mb-0">
                                    <i class="fa-solid fa-barcode text-primary me-1"></i> Input Barang & Satuan
                                </h6>
                                <span id="quick_stock_display" class="badge bg-info-subtle text-info d-none"
                                    style="font-size: 11px; font-weight: 600;"></span>
                            </div>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-5 col-sm-12">
                                    <label class="form-label fs-7 fw-bold text-secondary mb-1">Pilih Barang</label>
                                    <select id="quick_barang" class="form-select form-select-sm" style="width: 100%;">
                                        <option value="">-- Cari / Pilih Barang --</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label fs-7 fw-bold text-secondary mb-1">Satuan</label>
                                    <select id="quick_satuan" class="form-select form-select-sm" disabled>
                                        <option value="">-- Pilih Satuan --</option>
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-6">
                                    <label class="form-label fs-7 fw-bold text-secondary mb-1">Qty Muat</label>
                                    <input type="number" id="quick_qty"
                                        class="form-control form-control-sm text-center fw-bold text-primary" value="1"
                                        min="0.01" step="any" placeholder="0.00">
                                </div>
                                <div class="col-md-2 col-sm-12">
                                    <button type="button" class="btn btn-primary btn-sm w-100 fw-bold hover-scale py-1.5"
                                        id="btn-add-quick">
                                        <i class="fa-solid fa-plus me-1 text-white"></i> Tambah
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- TABLE BARANG --}}
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-list me-1 text-primary"></i> Daftar
                                    Barang yang Dimuat</h6>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle" id="items-table">
                                    <thead class="table-light text-secondary text-uppercase fs-7 font-11">
                                        <tr>
                                            <th width="50" class="text-center">No</th>
                                            <th>Nama Barang</th>
                                            <th width="200" class="text-center">Stok Gudang</th>
                                            <th width="150" class="text-center">Satuan</th>
                                            <th width="150" class="text-center">Qty Muat</th>
                                            <th width="80" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-tbody">
                                        {{-- Row templates appended dynamically --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-4">
                            <a href="{{ route('canvas.index') }}"
                                class="btn btn-light px-4 fw-semibold border hover-scale">
                                <i class="fa-solid fa-arrow-left me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold hover-scale">
                                <i class="fa-solid fa-truck-moving me-1"></i> Simpan & Berangkat
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
            const barangsCache = {};
            let rowIdx = 0;

            // Initialize salesman select2
            $('.select2-salesman').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Initialize quick search select2
            $('#quick_barang').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Cari barang...',
                ajax: {
                    url: '{{ route('barang.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            has_stock: 1
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                templateResult: formatBarangResult,
                templateSelection: formatBarangSelection
            });

            function formatBarangResult(barang) {
                if (barang.loading) {
                    return barang.text;
                }
                const formattedStok = formatStokJS(barang.stok, barang.satuans);
                const $container = $(
                    `<div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-dark fs-7">${barang.nama_barang}</div>
                        <div class="text-muted font-monospace fs-8">${barang.kode_barang}</div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1" style="font-size: 11px;">
                            <i class="fa-solid fa-box me-1"></i> ${formattedStok}
                        </span>
                    </div>
                </div>`
                );
                return $container;
            }

            function formatBarangSelection(barang) {
                if (!barang.id) {
                    return barang.text;
                }
                return `${barang.nama_barang || barang.text} (${barang.kode_barang || barang.id})`;
            }

            // On selection of quick barang
            $('#quick_barang').on('select2:select', function(e) {
                const data = e.params.data;
                barangsCache[data.kode_barang] = data;

                // Populate Satuan Dropdown
                const $satuanSelect = $('#quick_satuan');
                $satuanSelect.empty().prop('disabled', false);

                if (data.satuans && data.satuans.length > 0) {
                    data.satuans.forEach(satuan => {
                        $satuanSelect.append(new Option(satuan.satuan + ' (Isi ' + satuan.isi + ')',
                            satuan.id));
                    });
                } else {
                    $satuanSelect.append(new Option('PCS', ''));
                }

                // Display Stock
                const formattedStok = formatStokJS(data.stok, data.satuans);
                $('#quick_stock_display').html(
                    `<i class="fa-solid fa-box me-1"></i> Stok Gudang: ${formattedStok}`
                ).removeClass('d-none');
            });

            // Clear quick selection details when search is empty
            $('#quick_barang').on('change', function() {
                if (!$(this).val()) {
                    $('#quick_satuan').empty().append('<option value="">-- Pilih Satuan --</option>').prop(
                        'disabled', true);
                    $('#quick_stock_display').addClass('d-none').text('');
                }
            });

            // Add item from quick bar
            $('#btn-add-quick').click(function() {
                const barangCode = $('#quick_barang').val();
                const satuanId = $('#quick_satuan').val();
                const qty = parseFloat($('#quick_qty').val()) || 0;

                if (!barangCode) return Swal.fire('Peringatan', 'Pilih barang terlebih dahulu!', 'warning');
                if (!satuanId) return Swal.fire('Peringatan', 'Pilih satuan terlebih dahulu!', 'warning');
                if (qty <= 0) return Swal.fire('Peringatan', 'Qty harus lebih dari 0!', 'warning');

                // Prevent duplicate combination of code and unit
                let exist = false;
                $('#items-tbody tr').each(function() {
                    if ($(this).find('input[name*="[kode_barang]"]').val() === barangCode &&
                        $(this).find('input[name*="[satuan_id]"]').val() === satuanId) {
                        exist = true;
                    }
                });

                if (exist) {
                    return Swal.fire('Peringatan', 'Barang dengan satuan ini sudah ada di daftar!',
                        'warning');
                }

                const barang = barangsCache[barangCode];
                if (!barang) return;

                // Find conversion factor for selected unit
                let isi = 1;
                if (barang.satuans) {
                    const sat = barang.satuans.find(s => s.id == satuanId);
                    if (sat) {
                        isi = parseFloat(sat.isi) || 1;
                    }
                }
                const newQtySmallest = qty * isi;

                // Sum existing quantities in the table for this product
                let existingQtySmallest = 0;
                $('#items-tbody tr').each(function() {
                    const r = $(this);
                    if (r.find('input[name*="[kode_barang]"]').val() === barangCode) {
                        const rQty = parseFloat(r.find('.input-qty').val()) || 0;
                        const rSatuanId = r.find('input[name*="[satuan_id]"]').val();
                        let rIsi = 1;
                        if (barang.satuans) {
                            const sat = barang.satuans.find(s => s.id == rSatuanId);
                            if (sat) {
                                rIsi = parseFloat(sat.isi) || 1;
                            }
                        }
                        existingQtySmallest += rQty * rIsi;
                    }
                });

                const totalRequestedSmallest = newQtySmallest + existingQtySmallest;
                const totalStock = parseFloat(barang.stok) || 0;

                if (totalRequestedSmallest > totalStock) {
                    return Swal.fire({
                        title: 'Stok Tidak Mencukupi',
                        html: `Stok gudang barang <b>${barang.nama_barang}</b> tidak mencukupi!<br><br>` +
                            `Stok tersedia: <b>${formatStokJS(totalStock, barang.satuans)}</b><br>` +
                            `Jumlah diminta: <b>${formatStokJS(totalRequestedSmallest, barang.satuans)}</b>`,
                        icon: 'error'
                    });
                }

                const satuanName = $('#quick_satuan').find(':selected').text().split(' (Isi')[0].trim();
                const formattedStok = formatStokJS(barang.stok, barang.satuans);

                appendRow(barangCode, barang.nama_barang, satuanId, satuanName, qty, formattedStok);

                // Reset Quick Inputs
                $('#quick_barang').val('').trigger('change');
                $('#quick_qty').val(1);
                $('#quick_barang').select2('open');
            });

            // Add on Enter key press in Qty and Satuan fields
            $('#quick_qty, #quick_satuan').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#btn-add-quick').click();
                }
            });

            // Store previous value on focus for validation rollback
            $(document).on('focus', '.input-qty', function() {
                $(this).data('prev-val', $(this).val());
            });

            // Validate edited quantity in table row
            $(document).on('input change', '.input-qty', function() {
                const $input = $(this);
                const prevVal = parseFloat($input.data('prev-val')) || 0;
                const $row = $input.closest('tr');
                const barangCode = $row.find('input[name*="[kode_barang]"]').val();
                const barang = barangsCache[barangCode];
                if (!barang) return;

                let totalRequestedSmallest = 0;
                $('#items-tbody tr').each(function() {
                    const r = $(this);
                    if (r.find('input[name*="[kode_barang]"]').val() === barangCode) {
                        const rQtyInput = r.find('.input-qty');
                        const rQty = parseFloat(rQtyInput.val()) || 0;
                        const rSatuanId = r.find('input[name*="[satuan_id]"]').val();

                        let rIsi = 1;
                        if (barang.satuans) {
                            const sat = barang.satuans.find(s => s.id == rSatuanId);
                            if (sat) {
                                rIsi = parseFloat(sat.isi) || 1;
                            }
                        }
                        totalRequestedSmallest += rQty * rIsi;
                    }
                });

                const totalStock = parseFloat(barang.stok) || 0;
                if (totalRequestedSmallest > totalStock) {
                    Swal.fire({
                        title: 'Stok Tidak Mencukupi',
                        html: `Stok gudang barang <b>${barang.nama_barang}</b> tidak mencukupi!<br><br>` +
                            `Stok tersedia: <b>${formatStokJS(totalStock, barang.satuans)}</b><br>` +
                            `Jumlah diminta: <b>${formatStokJS(totalRequestedSmallest, barang.satuans)}</b>`,
                        icon: 'error'
                    });

                    // Rollback to previous quantity
                    $input.val(prevVal);
                } else {
                    $input.data('prev-val', $input.val());
                }
            });

            $(document).on('click', '.remove-row-btn', function() {
                $(this).closest('tr').remove();
                reindexRows();
            });

            function appendRow(barangCode, barangName, satuanId, satuanName, qty, formattedStok) {
                const template = `
                <tr class="item-row" data-index="${rowIdx}">
                    <td class="row-num text-center fw-bold text-secondary font-12"></td>
                    <td>
                        <div class="fw-bold text-dark">${barangName}</div>
                        <span class="text-secondary font-monospace font-11">${barangCode}</span>
                        <input type="hidden" name="items[${rowIdx}][kode_barang]" value="${barangCode}">
                    </td>
                    <td class="text-center font-12 text-dark stok-display fw-semibold">
                        ${formattedStok}
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light border text-dark font-12">
                            ${satuanName}
                        </span>
                        <input type="hidden" name="items[${rowIdx}][satuan_id]" value="${satuanId}">
                    </td>
                    <td style="width: 150px;">
                        <input type="number" name="items[${rowIdx}][qty_ambil]" class="form-control form-control-sm text-center fw-bold text-primary input-qty" value="${qty}" min="0.01" step="any" placeholder="0.00" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
            `;

                $('#items-tbody').append(template);
                rowIdx++;
                reindexRows();
            }

            function reindexRows() {
                $('#items-tbody tr').each(function(idx) {
                    $(this).find('.row-num').text(idx + 1);
                });
            }

            function formatStokJS(stok, satuans) {
                let qtyFloat = parseFloat(stok) || 0;
                let isNegative = qtyFloat < 0;
                let remaining = Math.round(Math.abs(qtyFloat) * 10000) / 10000;
                let breakdowns = [];
                if (satuans && satuans.length > 0) {
                    let sorted = [...satuans].sort((a, b) => b.isi - a.isi);
                    let count = sorted.length;
                    sorted.forEach((sat, index) => {
                        let factor = parseFloat(sat.isi) || 1;
                        if (index === count - 1) {
                            let unitQty = Math.round((remaining / factor) * 10000) / 10000;
                            if (unitQty > 0) {
                                breakdowns.push(`${unitQty} ${sat.satuan}`);
                            }
                        } else {
                            let unitQty = Math.floor(Math.round((remaining / factor) * 100000000) /
                                100000000);
                            if (unitQty > 0) {
                                breakdowns.push(`${unitQty} ${sat.satuan}`);
                                remaining = Math.round((remaining - (unitQty * factor)) * 10000) / 10000;
                            }
                        }
                    });
                } else {
                    breakdowns.push(`${remaining} PCS`);
                }
                let formatted = breakdowns.join(', ') || '0 PCS';
                return isNegative ? '-' + formatted : formatted;
            }
        });
    </script>
@endpush
