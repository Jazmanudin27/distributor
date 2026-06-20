@extends('layouts.app')
@section('title', $isEdit ? 'Edit Kiriman Penjualan' : 'Tambah Kiriman Penjualan')

@push('styles')
    @if (!$isEdit)
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    @endif
    <style>
        .fs-7 {
            font-size: 0.85rem !important;
        }
        .fs-8 {
            font-size: 0.75rem !important;
        }
        .cart-badge-item {
            transition: all 0.2s ease-in-out;
        }
        .cart-badge-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        }
    </style>
@endpush

@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid {{ $isEdit ? 'fa-pen-to-square' : 'fa-circle-plus' }} me-2"></i>
                    {{ $isEdit ? 'Edit Kiriman Penjualan' : 'Tambah Kiriman Penjualan' }}
                </h5>
                <small class="text-white-50">
                    {{ $isEdit ? 'Ubah rekapitulasi pengiriman sales' : 'Buat rekapitulasi pengiriman baru' }}
                </small>
            </div>
            <a href="{{ route('penjualan-kiriman.index') }}" class="btn btn-outline-light btn-sm fw-bold">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card-body p-4">
            <form action="{{ $isEdit ? route('penjualan-kiriman.update') : route('penjualan-kiriman.store') }}"
                method="POST" id="form-kiriman" enctype="multipart/form-data">
                @csrf
                {{-- Hidden input container for selected invoices --}}
                <div id="hidden-invoices-container"></div>
                @if ($isEdit)
                    <input type="hidden" name="current_tanggal" value="{{ $tanggal }}">
                    <input type="hidden" name="current_kode_wilayah" value="{{ $kode_wilayah }}">
                    <input type="hidden" name="current_kirimanke" value="{{ $kirimanke }}">
                    <input type="hidden" name="kode_wilayah" value="{{ $kode_wilayah }}">
                @endif

                {{-- PILIH FAKTUR PENJUALAN --}}
                <div class="mb-4">
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-md-3">
                            <h5 class="fw-bold mb-0 text-primary">
                                <i class="fa-solid fa-file-invoice-dollar me-2"></i> Pilih Faktur Penjualan
                            </h5>
                        </div>
                        <div class="col-md-9">
                            <div class="row g-2 align-items-center justify-content-md-end">
                                <div class="col-auto">
                                    <span class="fs-7 fw-semibold text-secondary"><i class="fa-solid fa-filter me-1"></i>
                                        Filter Faktur:</span>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <select id="filter_kode_wilayah" class="form-select form-select-sm">
                                        <option value="">-- Semua Wilayah --</option>
                                        @foreach ($wilayahs as $w)
                                            <option value="{{ $w->kode_wilayah }}">{{ $w->nama_wilayah }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <input type="date" id="filter_tanggal_mulai" class="form-control form-control-sm"
                                        title="Tanggal Mulai">
                                </div>
                                <div class="col-auto text-secondary small">s/d</div>
                                <div class="col-auto">
                                    <input type="date" id="filter_tanggal_akhir" class="form-control form-control-sm"
                                        title="Tanggal Akhir">
                                </div>
                                <div class="col-auto">
                                    <div class="btn-group">
                                        <button type="button" id="btn-filter-invoices"
                                            class="btn btn-primary btn-sm fw-bold px-3">
                                            Filter
                                        </button>
                                        <button type="button" id="btn-reset-filter-invoices"
                                            class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                                            <i class="fa-solid fa-rotate-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle border" id="table-invoices">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th width="40" class="text-center">
                                        <input type="checkbox" id="check-all" class="form-check-input">
                                    </th>
                                    <th width="160">No Faktur</th>
                                    <th width="110">Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th width="130">Wilayah</th>
                                    <th>Sales</th>
                                    <th class="text-end" width="160">Total Faktur</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        @if ($isEdit)
                                            <i class="fa-solid fa-spinner fa-spin me-2"></i> Memuat daftar faktur...
                                        @else
                                            Silakan pilih wilayah filter terlebih dahulu untuk menampilkan daftar faktur.
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- SELECTED INVOICES CART --}}
                <div class="card border shadow-sm rounded-3 mb-4" id="cart-card">
                    <div class="card-header bg-light py-2 px-3 fw-bold text-secondary d-flex justify-content-between align-items-center">
                        <span class="fs-7"><i class="fa-solid fa-cart-shopping text-primary me-2"></i> Keranjang Faktur Terpilih (<span id="cart-count" class="text-primary fw-bold">0</span>)</span>
                        <button type="button" id="btn-clear-cart" class="btn btn-link btn-sm text-danger text-decoration-none p-0 fw-bold fs-8" disabled>
                            <i class="fa-solid fa-trash-can me-1"></i> Bersihkan Semua
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <div id="cart-empty-state" class="text-center text-muted py-2">
                            <i class="fa-solid fa-cart-plus me-1 opacity-75"></i> Belum ada faktur yang terpilih.
                        </div>
                        <div id="cart-badges" class="d-flex flex-wrap gap-2"></div>
                        <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                            <span class="fw-semibold text-secondary fs-7">Total Nominal:</span>
                            <span class="fw-bold text-success fs-6" id="cart-total">Rp 0</span>
                        </div>
                    </div>
                </div>

                {{-- INFORMASI PENGIRIMAN --}}
                <div class="border-top pt-4 mb-4">
                    <h5 class="fw-bold mb-3 text-primary">
                        <i class="fa-solid fa-truck-fast me-2"></i> Informasi Pengiriman
                    </h5>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-secondary">Tanggal Pengiriman <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control"
                                value="{{ $tanggal }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary">Wilayah Pengiriman <span
                                    class="text-danger">*</span></label>
                            @if ($isEdit)
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light"
                                        value="{{ $wilayah->nama_wilayah }}" readonly>
                                    <span
                                        class="input-group-text bg-primary-subtle text-primary border-primary-subtle fw-semibold">
                                        Kirim Ke-{{ $kirimanke }}
                                    </span>
                                </div>
                            @else
                                <select name="kode_wilayah" id="kode_wilayah" class="form-select select2-basic" required>
                                    <option value="">-- Pilih Wilayah --</option>
                                    @foreach ($wilayahs as $w)
                                        <option value="{{ $w->kode_wilayah }}">{{ $w->nama_wilayah }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-secondary">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" rows="1" class="form-control"
                                placeholder="Catatan pengiriman (misal: Rute Driver)...">{{ $keterangan ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary">Nama Driver (Sopir)</label>
                            <input type="text" name="driver_name" class="form-control"
                                value="{{ $driver_name ?? '' }}" placeholder="Nama Sopir">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary">No. Plat Kendaraan</label>
                            <input type="text" name="no_kendaraan" class="form-control"
                                value="{{ $no_kendaraan ?? '' }}" placeholder="Plat Nomor (misal: B 1234 XY)">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary">Status Pengiriman</label>
                            <select name="status" id="shipment-status" class="form-select">
                                <option value="proses" {{ (isset($status) && $status === 'proses') ? 'selected' : '' }}>Proses (Draft)</option>
                                <option value="kirim" {{ (isset($status) && $status === 'kirim') ? 'selected' : '' }}>Dikirim</option>
                                <option value="selesai" {{ (isset($status) && $status === 'selesai') ? 'selected' : '' }}>Selesai (Sampai)</option>
                                <option value="batal" {{ (isset($status) && $status === 'batal') ? 'selected' : '' }}>Batal</option>
                            </select>
                        </div>
                    </div>

                    <!-- POD Fields, only shown when status is 'selesai' -->
                    <div class="row g-3 d-none" id="pod-section">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary">Nama Penerima</label>
                            <input type="text" name="nama_penerima" id="nama_penerima" class="form-control"
                                value="{{ $nama_penerima ?? '' }}" placeholder="Nama Penerima Barang">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold text-secondary">Upload Foto POD (Bukti Terima)</label>
                            <input type="file" name="foto_penerima" class="form-control" accept="image/*">
                            @if(isset($foto_penerima) && $foto_penerima)
                                <input type="hidden" name="old_foto_penerima" value="{{ $foto_penerima }}">
                                <div class="mt-2">
                                    <small class="text-secondary d-block mb-1">Bukti Foto Saat Ini:</small>
                                    <a href="{{ asset($foto_penerima) }}" target="_blank">
                                        <img src="{{ asset($foto_penerima) }}" alt="POD" class="rounded border" style="max-height: 80px;">
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- BUTTONS ACTION --}}
                <div class="d-flex justify-content-end gap-2 mt-4 border-top pt-3">
                    <a href="{{ route('penjualan-kiriman.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold" id="btn-submit" disabled>
                        <i class="fa-solid fa-save me-1"></i> {{ $isEdit ? 'Perbarui Kiriman' : 'Simpan Kiriman' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Select2 CSS & JS -->
    @if (!$isEdit)
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @endif
    <script>
        $(document).ready(function() {
            // Map to track globally selected invoices: { no_faktur: invoiceObject }
            var selectedInvoices = {};
            
            // Map to track loaded invoices on current table display
            var loadedInvoicesMap = {};

            // Initialize from php collection
            var initialInvoices = {!! json_encode($shipmentInvoices ?? []) !!};
            if (initialInvoices && initialInvoices.length > 0) {
                $.each(initialInvoices, function(idx, inv) {
                    selectedInvoices[inv.no_faktur] = {
                        no_faktur: inv.no_faktur,
                        tanggal: inv.tanggal,
                        grand_total: parseFloat(inv.grand_total),
                        nama_pelanggan: inv.pelanggan ? inv.pelanggan.nama_pelanggan : '',
                        kode_pelanggan: inv.pelanggan ? inv.pelanggan.kode_pelanggan : '',
                        nama_wilayah: (inv.pelanggan && inv.pelanggan.wilayah) ? inv.pelanggan.wilayah.nama_wilayah : '-',
                        nama_sales: inv.sales ? inv.sales.name : '-',
                        nik_sales: inv.kode_sales ? inv.kode_sales : ''
                    };
                });
            }

            // Draw initial cart items
            updateCartUI();

            @if (!$isEdit)
                // Initialize Select2
                $('.select2-basic').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });

                // Fetch Invoices when Wilayah is selected
                $('#kode_wilayah').on('change', function() {
                    var val = $(this).val();
                    $('#filter_kode_wilayah').val(val);
                    loadInvoices();
                });
            @else
                // Load invoices immediately on edit mode page load
                var mainWilayah = $('[name="kode_wilayah"]').val();
                $('#filter_kode_wilayah').val(mainWilayah);
                loadInvoices();
            @endif

            // Load invoices on filter wilayah change
            $('#filter_kode_wilayah').on('change', function() {
                loadInvoices();
            });

            // Filter click handler
            $('#btn-filter-invoices').on('click', function(e) {
                e.preventDefault();
                loadInvoices();
            });

            // Reset filter click handler
            $('#btn-reset-filter-invoices').on('click', function(e) {
                e.preventDefault();
                var mainWilayah = $('[name="kode_wilayah"]').val();
                $('#filter_kode_wilayah').val(mainWilayah);
                $('#filter_tanggal_mulai').val('');
                $('#filter_tanggal_akhir').val('');
                loadInvoices();
            });

            function loadInvoices() {
                var filter_kode_wilayah = $('#filter_kode_wilayah').val();
                var tbody = $('#table-invoices tbody');

                tbody.empty();
                $('#check-all').prop('checked', false);
                loadedInvoicesMap = {};

                tbody.append(
                    '<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i> Memuat daftar faktur...</td></tr>'
                );

                $.ajax({
                    url: "{{ route('penjualan-kiriman.get-invoices') }}",
                    type: "GET",
                    data: {
                        kode_wilayah: filter_kode_wilayah,
                        is_edit: "{{ $isEdit ? 1 : 0 }}",
                        current_tanggal: "{{ $isEdit ? $tanggal : '' }}",
                        current_kode_wilayah: "{{ $isEdit ? $kode_wilayah : '' }}",
                        current_kirimanke: "{{ $isEdit ? $kirimanke : '' }}",
                        tanggal_mulai: $('#filter_tanggal_mulai').val(),
                        tanggal_akhir: $('#filter_tanggal_akhir').val()
                    },
                    dataType: "json",
                    success: function(response) {
                        tbody.empty();
                        var data = response.invoices;

                        if (data.length === 0) {
                            tbody.append(
                                '<tr><td colspan="7" class="text-center py-4 text-danger fw-semibold"><i class="fa-solid fa-circle-exclamation me-1"></i> Tidak ada faktur penjualan aktif yang cocok dengan filter di wilayah ini.</td></tr>'
                            );
                            syncTableCheckboxes();
                            return;
                        }

                        $.each(data, function(index, invoice) {
                            loadedInvoicesMap[invoice.no_faktur] = invoice;

                            var tanggalFaktur = new Date(invoice.tanggal);
                            var formattedDate = String(tanggalFaktur.getDate()).padStart(2,
                                    '0') + '-' +
                                String(tanggalFaktur.getMonth() + 1).padStart(2, '0') + '-' +
                                tanggalFaktur.getFullYear();

                            var totalFaktur = parseFloat(invoice.grand_total);
                            var formattedTotal = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0
                            }).format(totalFaktur);

                            var salesName = invoice.sales ? invoice.sales.name : '-';
                            var salesNIK = invoice.kode_sales ? invoice.kode_sales : '';
                            var wilayahName = (invoice.pelanggan && invoice.pelanggan.wilayah) ? invoice.pelanggan.wilayah.nama_wilayah : '-';

                            // Check if this invoice is in selectedInvoices
                            var isChecked = !!selectedInvoices[invoice.no_faktur];

                            var row = `<tr>
                                <td class="text-center">
                                    <input type="checkbox" value="${invoice.no_faktur}" class="form-check-input invoice-checkbox" ${isChecked ? 'checked' : ''}>
                                </td>
                                <td>
                                    <span class="badge bg-secondary font-monospace px-2 py-1">${invoice.no_faktur}</span>
                                </td>
                                <td class="small">${formattedDate}</td>
                                <td class="fw-semibold text-dark">
                                    <div>${invoice.pelanggan.nama_pelanggan}</div>
                                    <div class="text-muted small fw-normal" style="font-size: 0.78rem;">${invoice.kode_pelanggan}</div>
                                </td>
                                <td class="small text-secondary">${wilayahName}</td>
                                <td>
                                    <div>${salesName}</div>
                                    <small class="text-secondary font-monospace">${salesNIK}</small>
                                </td>
                                <td class="text-end fw-bold text-success">${formattedTotal}</td>
                            </tr>`;
                            tbody.append(row);
                        });

                        syncTableCheckboxes();
                    },
                    error: function() {
                        tbody.empty();
                        tbody.append(
                            '<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Terjadi kesalahan saat memuat data faktur.</td></tr>'
                        );
                    }
                });
            }

            // Sync checkboxes in the list table with the selectedInvoices map
            function syncTableCheckboxes() {
                var checkboxes = $('.invoice-checkbox');
                var total = checkboxes.length;
                var checkedCount = 0;

                checkboxes.each(function() {
                    var noFaktur = $(this).val();
                    var isChecked = !!selectedInvoices[noFaktur];
                    $(this).prop('checked', isChecked);
                    if (isChecked) {
                        checkedCount++;
                    }
                });

                $('#check-all').prop('checked', total > 0 && total === checkedCount);
            }

            // Update Selected Invoices Cart UI
            function updateCartUI() {
                var cartBadges = $('#cart-badges');
                var emptyState = $('#cart-empty-state');
                var btnClear = $('#btn-clear-cart');
                var cartCount = $('#cart-count');
                var cartTotal = $('#cart-total');
                var hiddenContainer = $('#hidden-invoices-container');

                cartBadges.empty();
                hiddenContainer.empty();

                var keys = Object.keys(selectedInvoices);
                var totalCount = keys.length;
                var totalNominal = 0;

                if (totalCount === 0) {
                    emptyState.show();
                    btnClear.prop('disabled', true);
                    cartCount.text(0);
                    cartTotal.text('Rp 0');
                    $('#btn-submit').prop('disabled', true);
                } else {
                    emptyState.hide();
                    btnClear.prop('disabled', false);
                    cartCount.text(totalCount);

                    $.each(selectedInvoices, function(noFaktur, inv) {
                        totalNominal += inv.grand_total;

                        var badge = `
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle d-flex align-items-center gap-2 p-2 fs-7 cart-badge-item mb-2" data-faktur="${inv.no_faktur}">
                                <span><strong>${inv.no_faktur}</strong> - ${inv.nama_pelanggan} (${inv.nama_wilayah})</span>
                                <button type="button" class="btn-close remove-from-cart" data-faktur="${inv.no_faktur}" aria-label="Close" style="font-size: 0.65rem;"></button>
                            </span>
                        `;
                        cartBadges.append(badge);

                        // Add hidden input for form submission
                        hiddenContainer.append(`<input type="hidden" name="invoices[]" value="${inv.no_faktur}">`);
                    });

                    var formattedGrandTotal = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(totalNominal);

                    cartTotal.text(formattedGrandTotal);
                    $('#btn-submit').prop('disabled', false);
                }

                syncTableCheckboxes();
            }

            // Check All table rows
            $('#check-all').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('.invoice-checkbox').each(function() {
                    var noFaktur = $(this).val();
                    if (isChecked) {
                        var invData = loadedInvoicesMap[noFaktur];
                        if (invData) {
                            selectedInvoices[noFaktur] = {
                                no_faktur: invData.no_faktur,
                                tanggal: invData.tanggal,
                                grand_total: parseFloat(invData.grand_total),
                                nama_pelanggan: invData.pelanggan ? invData.pelanggan.nama_pelanggan : '',
                                kode_pelanggan: invData.pelanggan ? invData.pelanggan.kode_pelanggan : '',
                                nama_wilayah: (invData.pelanggan && invData.pelanggan.wilayah) ? invData.pelanggan.wilayah.nama_wilayah : '-',
                                nama_sales: invData.sales ? invData.sales.name : '-',
                                nik_sales: invData.kode_sales ? invData.kode_sales : ''
                            };
                        }
                    } else {
                        delete selectedInvoices[noFaktur];
                    }
                });
                updateCartUI();
            });

            // Single row checkbox change
            $(document).on('change', '.invoice-checkbox', function() {
                var noFaktur = $(this).val();
                if ($(this).is(':checked')) {
                    var invData = loadedInvoicesMap[noFaktur];
                    if (invData) {
                        selectedInvoices[noFaktur] = {
                            no_faktur: invData.no_faktur,
                            tanggal: invData.tanggal,
                            grand_total: parseFloat(invData.grand_total),
                            nama_pelanggan: invData.pelanggan ? invData.pelanggan.nama_pelanggan : '',
                            kode_pelanggan: invData.pelanggan ? invData.pelanggan.kode_pelanggan : '',
                            nama_wilayah: (invData.pelanggan && invData.pelanggan.wilayah) ? invData.pelanggan.wilayah.nama_wilayah : '-',
                            nama_sales: invData.sales ? invData.sales.name : '-',
                            nik_sales: invData.kode_sales ? invData.kode_sales : ''
                        };
                    }
                } else {
                    delete selectedInvoices[noFaktur];
                }
                updateCartUI();
            });

            // Remove from cart button click
            $(document).on('click', '.remove-from-cart', function() {
                var noFaktur = $(this).data('faktur');
                delete selectedInvoices[noFaktur];
                updateCartUI();
            });

            // Clear cart button click
            $('#btn-clear-cart').on('click', function() {
                Swal.fire({
                    title: 'Kosongkan Keranjang?',
                    text: 'Semua faktur yang telah dipilih akan dihapus dari daftar kiriman ini.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Kosongkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        selectedInvoices = {};
                        updateCartUI();
                    }
                });
            });

            // Toggle POD Section based on Status
            function togglePodSection() {
                var status = $('#shipment-status').val();
                if (status === 'selesai') {
                    $('#pod-section').removeClass('d-none');
                } else {
                    $('#pod-section').addClass('d-none');
                }
            }

            $('#shipment-status').on('change', togglePodSection);
            togglePodSection(); // initial check
        });
    </script>
@endpush
