@extends('layouts.app')
@section('title', $item->exists ? 'Edit Stok Opname' : 'Stok Opname Baru')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white py-3">
            <h5 class="mb-0 fw-bold">
                <i class="fa-solid fa-clipboard-list me-2"></i>
                {{ $item->exists ? 'Edit Transaksi Stok Opname' : 'Transaksi Stok Opname Baru' }}
            </h5>
            <small class="text-white-50">Sesuaikan pencatatan stok sistem berdasarkan perhitungan fisik aktual</small>
        </div>

        <div class="card-body p-4">
            <form action="{{ $item->exists ? route('stok-opname.update', $item->no_opname) : route('stok-opname.store') }}"
                method="POST" id="opnameForm">
                @csrf
                @if ($item->exists)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <!-- Metadata Panel -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-secondary mb-1">No Opname <span
                                class="text-danger">*</span></label>
                        <input type="text" name="no_opname" id="no_opname"
                            class="form-control form-control-sm bg-light fw-bold"
                            value="{{ old('no_opname', $item->no_opname) }}" readonly required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-secondary mb-1">Tanggal Opname <span
                                class="text-danger">*</span></label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm"
                            value="{{ old('tanggal', $item->tanggal ?? date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-secondary mb-1">Operator / Pengisi</label>
                        <input type="text" class="form-control form-control-sm bg-light"
                            value="{{ Auth::user()->name ?? '-' }}" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold text-secondary mb-1">Keterangan / Alasan Penyesuaian</label>
                        <textarea name="keterangan" id="keterangan" rows="2" class="form-control form-control-sm"
                            placeholder="Tulis catatan penyesuaian stok... (contoh: Barang rusak, salah hitung sebelumnya, dll.)">{{ old('keterangan', $item->keterangan) }}</textarea>
                    </div>
                </div>

                {{-- BULK FILTER AND LOAD BAR --}}
                <div class="bg-light p-3 rounded my-4 border">
                    <h6 class="fw-bold text-secondary mb-2 fs-7">Muat Barang Massal Berdasarkan Filter</h6>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fs-8 text-secondary mb-1">Pilih Kategori</label>
                            <select id="filter_kategori" class="form-select form-select-sm select2-init">
                                <option value="">-- Semua Kategori --</option>
                                @foreach ($kategoris as $k)
                                    <option value="{{ $k->nama_kategori }}">{{ $k->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fs-8 text-secondary mb-1">Pilih Merk</label>
                            <select id="filter_merk" class="form-select form-select-sm select2-init">
                                <option value="">-- Semua Merk --</option>
                                @foreach ($merks as $m)
                                    <option value="{{ $m->nama_merk }}">{{ $m->nama_merk }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex">
                            <button type="button" id="btn-load-filtered"
                                class="btn btn-primary btn-sm w-100 fw-bold hover-scale" style="height: 31px;">
                                <i class="fa-solid fa-cloud-arrow-down me-1"></i> Muat Barang
                            </button>
                        </div>
                    </div>
                </div>

                {{-- DYNAMIC LIST TABLE --}}
                <div class="card border shadow-sm rounded">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-list me-1 text-primary"></i> Daftar Penyesuaian Barang
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" id="table-items">
                            <thead class="table-light text-secondary text-uppercase">
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th width="120">Kode</th>
                                    <th width="200">Nama Barang</th>
                                    <th width="110" class="text-end">Stok Sistem</th>
                                    <th width="320" class="text-end pe-3">Stok Fisik (Per Satuan)</th>
                                    <th width="110" class="text-center">Selisih</th>
                                    <th>Keterangan Item</th>
                                    <th width="60" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Dynamic Rows --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ACTIONS BUTTONS --}}
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('stok-opname.index') }}" class="btn btn-light px-4 fw-semibold border hover-scale">
                        <i class="fa-solid fa-arrow-left me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold hover-scale" id="btn-submit">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Opname
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const barangs = {!! json_encode($barangs) !!};
            const existingDetails = {!! json_encode($item->details ?? []) !!};
            const isEditMode = {{ $item->exists ? 'true' : 'false' }};
            let rowIndex = 0;

            // Initialize select2
            $('.select2-init').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Format stock to UOM readable format
            function formatStokJS(stok, satuans) {
                let remaining = Math.abs(stok);
                let isNegative = stok < 0;
                let breakdowns = [];
                if (satuans && satuans.length > 0) {
                    let sorted = [...satuans].sort((a, b) => b.isi - a.isi);
                    sorted.forEach(sat => {
                        let factor = parseFloat(sat.isi) || 1;
                        let unitQty = Math.floor(remaining / factor);
                        if (unitQty > 0) {
                            breakdowns.push(`${unitQty} ${sat.satuan}`);
                            remaining = remaining % factor;
                        }
                    });
                    if (remaining > 0 && sorted.length > 0) {
                        let last = sorted[sorted.length - 1];
                        breakdowns.push(`${remaining} ${last.satuan}`);
                    }
                } else {
                    breakdowns.push(`${remaining} PCS`);
                }
                let formatted = breakdowns.join(', ') || '0 PCS';
                return isNegative ? '-' + formatted : formatted;
            }

            // Function to break down physical stock into UOM quantities
            function breakdownStock(qty, satuans) {
                let result = {};
                if (qty === null || qty === undefined || qty === '') {
                    return result;
                }
                if (!satuans || satuans.length === 0) {
                    result[1] = qty;
                    return result;
                }
                // Sort units by conversion factor descending
                let sorted = [...satuans].sort((a, b) => b.isi - a.isi);
                let remaining = qty;
                sorted.forEach(function(sat) {
                    let factor = sat.isi || 1;
                    let unitQty = Math.floor(remaining / factor);
                    result[sat.id] = unitQty;
                    remaining = remaining % factor;
                });
                // Add any leftover to the smallest unit (which is last)
                if (remaining > 0 && sorted.length > 0) {
                    let smallestUnitId = sorted[sorted.length - 1].id;
                    result[smallestUnitId] += remaining;
                }
                return result;
            }

            // Bulk Loader Button Click
            $('#btn-load-filtered').on('click', function() {
                const cat = $('#filter_kategori').val();
                const merk = $('#filter_merk').val();

                let filtered = barangs;
                if (cat) {
                    filtered = filtered.filter(b => b.kategori === cat);
                }
                if (merk) {
                    filtered = filtered.filter(b => b.merk === merk);
                }

                if (filtered.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Tidak Ada Barang',
                        text: 'Tidak ada barang yang cocok dengan filter yang dipilih.'
                    });
                    return;
                }

                let countAdded = 0;
                filtered.forEach(function(barang) {
                    // Check duplicate
                    let exists = false;
                    $('#table-items tbody tr').each(function() {
                        if ($(this).find('.kode-barang-val').val() === barang.kode_barang) {
                            exists = true;
                            return false;
                        }
                    });

                    if (!exists) {
                        const stokSistem = parseFloat(barang.stok) || 0;
                        addItemRow({
                            kode_barang: barang.kode_barang,
                            nama_barang: barang.nama_barang,
                            stok_sistem: stokSistem,
                            stok_fisik: stokSistem,
                            selisih: 0,
                            keterangan: '',
                            satuans: barang.satuans
                        });
                        countAdded++;
                    }
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Barang Dimuat',
                    text: `${countAdded} barang berhasil ditambahkan ke daftar.`
                });
            });


            function addItemRow(data) {
                // Find units from barangs array if not provided in data
                let satuans = data.satuans;
                if (!satuans) {
                    const foundB = barangs.find(b => b.kode_barang === data.kode_barang);
                    satuans = foundB ? foundB.satuans : [];
                }

                // breakdown stock
                let breakdown = breakdownStock(data.stok_fisik, satuans);

                let uomInputsHtml = '<div class="d-flex flex-wrap gap-1 justify-content-end align-items-center">';
                if (satuans && satuans.length > 0) {
                    // Sort descending by factor (isi)
                    let sorted = [...satuans].sort((a, b) => b.isi - a.isi);
                    sorted.forEach(function(sat) {
                        let val = breakdown[sat.id] !== undefined ? breakdown[sat.id] : '';
                        uomInputsHtml += `
                            <div class="input-group input-group-sm" style="width: 85px;">
                                <input type="number" class="form-control text-end uom-qty-input font-monospace p-1" 
                                    data-isi="${sat.isi}" value="${val}" min="0" step="any" placeholder="0">
                                <span class="input-group-text px-1.5 text-secondary fw-semibold text-xs" style="font-size: 9px !important; line-height: 1.2;">${sat.satuan}</span>
                            </div>
                        `;
                    });
                } else {
                    let val = data.stok_fisik !== null && data.stok_fisik !== undefined ? data.stok_fisik : '';
                    uomInputsHtml += `
                        <div class="input-group input-group-sm" style="width: 100px;">
                            <input type="number" class="form-control text-end uom-qty-input font-monospace" data-isi="1" value="${val}" min="0" step="any">
                            <span class="input-group-text px-1.5 text-secondary text-xs">PCS</span>
                        </div>
                    `;
                }
                uomInputsHtml += '</div>';

                const isFisikEmpty = data.stok_fisik === null || data.stok_fisik === undefined || data
                    .stok_fisik === '';
                const stokFisikVal = isFisikEmpty ? '' : data.stok_fisik;
                const totalFisikLabel = isFisikEmpty ? '-' : data.stok_fisik;

                const selisihVal = isFisikEmpty ? '' : data.selisih;
                const selisihLabel = isFisikEmpty ? '-' : (data.selisih > 0 ? '+' + data.selisih : data.selisih);

                const diffVal = parseFloat(data.selisih) || 0;
                let badgeClass = 'bg-secondary-subtle text-secondary border';
                if (!isFisikEmpty) {
                    if (diffVal > 0) {
                        badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                    } else if (diffVal < 0) {
                        badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                    }
                }

                const rowHtml = `
                    <tr id="row-${rowIndex}">
                        <td class="text-center row-number font-monospace text-secondary fw-semibold"></td>
                        <td>
                            <input type="text" name="items[${rowIndex}][kode_barang]" class="form-control form-control-sm font-monospace bg-light kode-barang-val fw-semibold" value="${data.kode_barang}" readonly>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm bg-light fw-semibold" value="${data.nama_barang}" readonly>
                        </td>
                        <td>
                            <input type="number" name="items[${rowIndex}][stok_sistem]" class="form-control form-control-sm text-end bg-light sistem-input font-monospace" value="${data.stok_sistem}" readonly>
                            <div class="text-end text-muted small mt-1 font-monospace fw-semibold" style="font-size: 0.73rem;">
                                ${formatStokJS(data.stok_sistem, satuans)}
                            </div>
                        </td>
                        <td>
                            ${uomInputsHtml}
                            <input type="hidden" name="items[${rowIndex}][stok_fisik]" class="fisik-input" value="${stokFisikVal}">
                            <div class="text-end text-muted small mt-1 font-monospace fw-semibold" style="font-size: 0.73rem;">
                                Total Fisik: <span class="total-fisik-label fw-bold text-dark">${totalFisikLabel}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <input type="hidden" name="items[${rowIndex}][selisih]" class="selisih-val" value="${selisihVal}">
                            <span class="badge ${badgeClass} fw-bold font-monospace py-1.5 px-2.5 fs-8 selisih-span">${selisihLabel}</span>
                        </td>
                        <td>
                            <input type="text" name="items[${rowIndex}][keterangan]" class="form-control form-control-sm" value="${data.keterangan || ''}" placeholder="Catatan item...">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm rounded btn-delete-row" title="Hapus Item">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#table-items tbody').append(rowHtml);
                rowIndex++;
                reorderRows();
            }

            function reorderRows() {
                let num = 1;
                $('#table-items tbody tr').each(function() {
                    $(this).find('.row-number').text(num++);
                });
            }

            // Input changes in UOM inputs inside the table grid
            $(document).on('input change', '.uom-qty-input', function() {
                const tr = $(this).closest('tr');

                // First check if all inputs in this row are empty/blank
                let isAnyFilled = false;
                tr.find('.uom-qty-input').each(function() {
                    if ($(this).val() !== '') {
                        isAnyFilled = true;
                    }
                });

                if (!isAnyFilled) {
                    // Set physical stock hidden value and label to empty
                    tr.find('.fisik-input').val('');
                    tr.find('.total-fisik-label').text('-');
                    tr.find('.selisih-val').val('');

                    const span = tr.find('.selisih-span');
                    span.removeClass(
                        'bg-secondary bg-success-subtle text-success bg-danger-subtle text-danger border border-success-subtle border-danger-subtle'
                    );
                    span.addClass('bg-secondary-subtle text-secondary border');
                    span.text('-');
                    return;
                }

                let totalFisik = 0;
                tr.find('.uom-qty-input').each(function() {
                    const qty = parseFloat($(this).val()) || 0;
                    const factor = parseFloat($(this).data('isi')) || 1;
                    totalFisik += qty * factor;
                });

                // Update physical stock hidden value and label
                tr.find('.fisik-input').val(totalFisik);
                tr.find('.total-fisik-label').text(totalFisik);

                // Calculate selisih
                const sistem = parseFloat(tr.find('.sistem-input').val()) || 0;
                const selisih = totalFisik - sistem;
                tr.find('.selisih-val').val(selisih);

                const span = tr.find('.selisih-span');
                span.removeClass(
                    'bg-secondary bg-success-subtle text-success bg-danger-subtle text-danger border border-success-subtle border-danger-subtle bg-secondary-subtle text-secondary border'
                );

                let sign = '';
                if (selisih > 0) {
                    span.addClass('bg-success-subtle text-success border border-success-subtle');
                    sign = '+';
                } else if (selisih < 0) {
                    span.addClass('bg-danger-subtle text-danger border border-danger-subtle');
                } else {
                    span.addClass('bg-secondary-subtle text-secondary border');
                }
                span.text(sign + selisih);
            });

            // Delete row
            $(document).on('click', '.btn-delete-row', function() {
                $(this).closest('tr').remove();
                reorderRows();
            });

            // Load edit mode details
            if (isEditMode && existingDetails.length > 0) {
                existingDetails.forEach(function(detail) {
                    addItemRow({
                        kode_barang: detail.kode_barang,
                        nama_barang: detail.barang ? detail.barang.nama_barang : '-',
                        stok_sistem: detail.stok_sistem,
                        stok_fisik: detail.stok_fisik,
                        selisih: detail.selisih,
                        keterangan: detail.keterangan || '',
                        satuans: detail.barang ? detail.barang.satuans : []
                    });
                });
            }
        });
    </script>
@endpush
