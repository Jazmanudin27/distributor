@extends('layouts.app')
@php
    $isEditingLoading = $canvasSession->status === 'pending' || ($canvasSession->status === 'loading' && request('mode') === 'edit');
    $isPending = $isEditingLoading;
@endphp
@section('title', $isPending ? 'Edit DPB' : 'Selesaikan DPB')
@section('content')
    <div class="row justify-content-start">
        <div class="col-lg-10 col-md-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div
                    class="card-header card-premium-header text-white py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="me-3 bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                            style="width: 45px; height: 45px;">
                            <i class="fa-solid fa-box-open fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">
                                @if ($isPending)
                                    Edit Qty Loading DPB: {{ $canvasSession->no_canvas }}
                                @else
                                    Selesaikan DPB: {{ $canvasSession->no_canvas }}
                                @endif
                            </h5>
                            <small class="text-white-50 font-12 font-italic">
                                @if ($isPending)
                                    Ubah kuantitas pengambilan barang salesman sebelum disetujui
                                @else
                                    Bongkar muatan dan catat pengembalian barang sisa
                                @endif
                            </small>
                        </div>
                    </div>
                    <a href="{{ route('canvas.show', $canvasSession->id) }}"
                        class="btn btn-primary btn-sm fw-bold hover-scale">
                        <i class="fa-solid fa-arrow-left me-1 text-white"></i> Kembali
                    </a>
                </div>
                <div class="card-body p-4">
                    {{-- Session summary --}}
                    <div class="row bg-light rounded p-3 mb-4 border g-3">
                        <div class="col-md-4">
                            <span class="text-secondary small d-block">Salesman Kanvas</span>
                            <strong
                                class="text-dark">{{ $canvasSession->sales->name ?? $canvasSession->kode_sales }}</strong>
                        </div>
                        <div class="col-md-4">
                            <span class="text-secondary small d-block">Tanggal Mulai</span>
                            <strong
                                class="text-dark">{{ \Carbon\Carbon::parse($canvasSession->tanggal)->format('d-M-Y') }}</strong>
                        </div>
                        <div class="col-md-4">
                            <span class="text-secondary small d-block">Keterangan Awal</span>
                            <span class="text-muted small">{{ $canvasSession->keterangan ?? '-' }}</span>
                        </div>
                    </div>


                    @if (!$isPending && isset($activeSessions) && $activeSessions->count() > 1)
                        <div class="alert alert-warning border border-warning rounded-3 mb-4 d-flex align-items-start" style="background-color: rgba(245, 158, 11, 0.08);">
                            <i class="fa-solid fa-triangle-exclamation fs-4 text-warning me-3 mt-1"></i>
                            <div>
                                <h6 class="alert-heading fw-bold mb-1" style="color: #d97706;">Akumulasi Beberapa DPB Aktif</h6>
                                <p class="mb-0 small text-dark">
                                    Salesman ini memiliki <strong>{{ $activeSessions->count() }} DPB aktif</strong> yang belum diselesaikan:
                                    <span class="font-monospace text-primary fw-semibold">
                                        {{ implode(', ', $activeSessions->pluck('no_canvas')->toArray()) }}
                                    </span>.
                                    Kuantitas pengambilan dan penjualan di bawah ini merupakan akumulasi dari seluruh DPB aktif tersebut.
                                    Penyelesaian ini akan menutup semua DPB aktif ini secara bersamaan.
                                </p>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('canvas.update', $canvasSession->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @if ($canvasSession->status === 'loading' && request('mode') === 'edit')
                            <input type="hidden" name="mode" value="edit">
                        @endif

                        <table class="table table-bordered table-sm align-middle">
                            @if ($isPending)
                                <thead class="table-light text-secondary text-uppercase fs-7 font-11">
                                    <tr>
                                        <th width="50" class="text-center">No</th>
                                        <th>Nama Barang</th>
                                        <th width="150" class="text-center">Satuan</th>
                                        <th width="340" class="text-center bg-primary-subtle text-primary fw-bold">Qty
                                            Ambil (Loading)</th>
                                        <th width="80" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="details-table-body">
                                    @foreach ($canvasSession->details as $index => $detail)
                                        @php
                                            $qtyAmbilSmallest = $detail->qty_ambil * ($detail->barangSatuan->isi ?? 1);
                                            $targetQty = (float) $detail->qty_ambil;
                                            $targetQtySmallest = $qtyAmbilSmallest;
                                        @endphp
                                        <tr>
                                            <td class="text-center fw-semibold text-secondary">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $detail->barang->nama_barang }}</div>
                                                <span class="text-secondary small font-11">Kode:
                                                    {{ $detail->kode_barang }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-light text-secondary border fw-semibold font-11 py-1 px-2.5"
                                                    style="opacity: 0.85;">
                                                    {{ $detail->barangSatuan->satuan ?? 'PCS' }}
                                                </span>
                                            </td>
                                            <td class="bg-primary-subtle px-3">
                                                <input type="hidden" name="details[{{ $index }}][id]"
                                                    value="{{ $detail->id }}">
                                                <input type="hidden" name="details[{{ $index }}][qty_ambil]"
                                                    id="qty-ambil-{{ $index }}" class="input-qty-ambil"
                                                    value="{{ $targetQty }}"
                                                    data-isi="{{ $detail->barangSatuan->isi ?? 1 }}"
                                                    data-row-id="{{ $index }}"
                                                    data-satuans="{{ json_encode($detail->barang->satuans) }}">
                                                @php
                                                    $satuans = $detail->barang->satuans;
                                                    $unitValues = [];
                                                    if ($satuans && $satuans->count() > 0) {
                                                        $sorted = $satuans->sortByDesc('isi');
                                                        $remaining = $targetQtySmallest;
                                                        $count = $sorted->count();
                                                        $i = 0;
                                                        foreach ($sorted as $sat) {
                                                            $i++;
                                                            $factor = (float) ($sat->isi ?: 1);
                                                            if ($i === $count) {
                                                                $unitQty = round($remaining / $factor, 4);
                                                                $unitValues[$sat->id] = (float) $unitQty;
                                                            } else {
                                                                $unitQty = floor(round($remaining / $factor, 8));
                                                                $unitValues[$sat->id] = (float) $unitQty;
                                                                $remaining = round($remaining - $unitQty * $factor, 4);
                                                            }
                                                        }
                                                    } else {
                                                        $unitValues[0] = $targetQty;
                                                    }
                                                @endphp
                                                <div class="d-flex flex-column gap-1 align-items-center">
                                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                        @if ($satuans && $satuans->count() > 0)
                                                            @foreach ($sorted as $sat)
                                                                @php
                                                                    $val = $unitValues[$sat->id] ?? 0;
                                                                @endphp
                                                                <div class="input-group input-group-sm"
                                                                    style="width: 100px;">
                                                                    <input type="number"
                                                                        class="form-control text-center input-unit-qty input-qty-row-{{ $index }}"
                                                                        data-isi="{{ $sat->isi }}"
                                                                        data-id="{{ $sat->id }}"
                                                                        data-row-id="{{ $index }}"
                                                                        value="{{ $val }}" min="0"
                                                                        step="any">
                                                                    <span
                                                                        class="input-group-text bg-light text-secondary font-monospace"
                                                                        style="font-size: 10px; padding: 0.25rem 0.4rem;">{{ $sat->satuan }}</span>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="input-group input-group-sm" style="width: 100px;">
                                                                <input type="number"
                                                                    class="form-control text-center input-unit-qty input-qty-row-{{ $index }}"
                                                                    data-isi="1" data-id="0"
                                                                    data-row-id="{{ $index }}"
                                                                    value="{{ $targetQty }}" min="0"
                                                                    step="any">
                                                                <span
                                                                    class="input-group-text bg-light text-secondary font-monospace"
                                                                    style="font-size: 10px; padding: 0.25rem 0.4rem;">PCS</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="text-center mt-1 fw-bold text-success font-monospace live-convert-display"
                                                        id="convert-display-{{ $index }}" style="font-size: 11px;">
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-sm btn-delete-row"
                                                    title="Hapus Barang">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            @else
                                <thead class="table-light text-secondary text-uppercase fs-7 font-11">
                                    <tr>
                                        <th width="50" class="text-center">No</th>
                                        <th>Nama Barang</th>
                                        <th width="150" class="text-center">Satuan</th>
                                        <th width="140" class="text-end pe-3 bg-primary-subtle text-primary">Ambil
                                            (Loading)</th>
                                        <th width="140" class="text-end pe-3 bg-info-subtle text-info">Terjual (Sales)
                                        </th>
                                        <th width="240" class="text-center bg-success-subtle text-success fw-bold">Qty
                                            Kembali (Unload)</th>
                                        <th width="140" class="text-end pe-3">Expected Sisa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $index = 0; @endphp
                                    @foreach ($accumulatedDetails as $key => $item)
                                        @php
                                            $detailIds = implode(',', $item['detail_ids']);
                                            $expectedSisa = max(
                                                0.0,
                                                (float) $item['qty_ambil'] - (float) $item['qty_terjual'],
                                            );
                                            $qtyAmbilSmallest = $item['qty_ambil'] * ($item['barangSatuan']->isi ?? 1);
                                            $qtyTerjualSmallest =
                                                $item['qty_terjual'] * ($item['barangSatuan']->isi ?? 1);
                                            $qtyExpectedSmallest = $expectedSisa * ($item['barangSatuan']->isi ?? 1);
                                            $satuans = $item['barang']->satuans;
                                        @endphp
                                        <tr>
                                            <td class="text-center fw-semibold text-secondary">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $item['barang']->nama_barang }}</div>
                                                <span class="text-secondary small font-11">Kode:
                                                    {{ $item['kode_barang'] }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-light text-secondary border fw-semibold font-11 py-1 px-2.5"
                                                    style="opacity: 0.85;">
                                                    {{ $item['barangSatuan']->satuan ?? 'PCS' }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3 bg-primary-subtle text-primary fw-bold">
                                                <div class="fs-7">{{ $item['barang']->formatStok($qtyAmbilSmallest) }}
                                                </div>
                                                <small
                                                    class="text-secondary font-11 d-block">({{ (float) $item['qty_ambil'] }}
                                                    {{ $item['barangSatuan']->satuan ?? 'PCS' }})</small>
                                            </td>
                                            <td class="text-end pe-3 bg-info-subtle text-info fw-bold">
                                                <div class="fs-7">{{ $item['barang']->formatStok($qtyTerjualSmallest) }}
                                                </div>
                                                <small
                                                    class="text-secondary font-11 d-block">({{ (float) $item['qty_terjual'] }}
                                                    {{ $item['barangSatuan']->satuan ?? 'PCS' }})</small>
                                            </td>
                                            <td class="bg-success-subtle px-3">
                                                <input type="hidden" name="details[{{ $index }}][detail_ids]"
                                                    value="{{ $detailIds }}">
                                                <input type="hidden" name="details[{{ $index }}][qty_kembali]"
                                                    id="qty-kembali-{{ $index }}" class="input-qty-kembali"
                                                    value="{{ $expectedSisa }}"
                                                    data-isi="{{ $item['barangSatuan']->isi ?? 1 }}"
                                                    data-row-id="{{ $index }}"
                                                    data-max-smallest="{{ $qtyAmbilSmallest }}"
                                                    data-satuans="{{ json_encode($satuans) }}">
                                                @php
                                                    $unitValues = [];
                                                    if ($satuans && $satuans->count() > 0) {
                                                        $sorted = $satuans->sortByDesc('isi');
                                                        $remaining = $qtyExpectedSmallest;
                                                        $count = $sorted->count();
                                                        $i = 0;
                                                        foreach ($sorted as $sat) {
                                                            $i++;
                                                            $factor = (float) ($sat->isi ?: 1);
                                                            if ($i === $count) {
                                                                 $unitQty = round($remaining / $factor, 4);
                                                                 $unitValues[$sat->id] = (float) $unitQty;
                                                            } else {
                                                                 $unitQty = floor(round($remaining / $factor, 8));
                                                                 $unitValues[$sat->id] = (float) $unitQty;
                                                                 $remaining = round($remaining - $unitQty * $factor, 4);
                                                            }
                                                        }
                                                    } else {
                                                        $unitValues[0] = $expectedSisa;
                                                    }
                                                @endphp
                                                <div class="d-flex flex-column gap-1 align-items-center">
                                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                        @if ($satuans && $satuans->count() > 0)
                                                            @foreach ($sorted as $sat)
                                                                @php
                                                                    $val = $unitValues[$sat->id] ?? 0;
                                                                @endphp
                                                                <div class="input-group input-group-sm"
                                                                    style="width: 100px;">
                                                                    <input type="number"
                                                                        class="form-control text-center input-unit-qty input-qty-row-{{ $index }}"
                                                                        data-isi="{{ $sat->isi }}"
                                                                        data-id="{{ $sat->id }}"
                                                                        data-row-id="{{ $index }}"
                                                                        value="{{ $val }}" min="0"
                                                                        step="any">
                                                                    <span
                                                                        class="input-group-text bg-light text-secondary font-monospace"
                                                                        style="font-size: 10px; padding: 0.25rem 0.4rem;">{{ $sat->satuan }}</span>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="input-group input-group-sm" style="width: 100px;">
                                                                <input type="number"
                                                                    class="form-control text-center input-unit-qty input-qty-row-{{ $index }}"
                                                                    data-isi="1" data-id="0"
                                                                    data-row-id="{{ $index }}"
                                                                    value="{{ $expectedSisa }}" min="0"
                                                                    step="any">
                                                                <span
                                                                    class="input-group-text bg-light text-secondary font-monospace"
                                                                    style="font-size: 10px; padding: 0.25rem 0.4rem;">PCS</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="text-center mt-1 fw-bold text-success font-monospace live-convert-display"
                                                        id="convert-display-{{ $index }}"
                                                        style="font-size: 11px;"></div>
                                                </div>
                                            </td>
                                            <td class="text-end pe-3 fw-bold">
                                                <div class="fs-7 text-dark">
                                                    {{ $item['barang']->formatStok($qtyExpectedSmallest) }}</div>
                                                <small class="text-secondary font-11 d-block">({{ (float) $expectedSisa }}
                                                    {{ $item['barangSatuan']->satuan ?? 'PCS' }})</small>
                                            </td>
                                        </tr>
                                        @php $index++; @endphp
                                    @endforeach
                                </tbody>
                            @endif
                        </table>

                        <div class="row mt-4">
                            <div class="col-md-6 mb-3">
                                <label for="keterangan" class="form-label fs-7 fw-bold text-secondary">
                                    {{ $isPending ? 'Catatan DPB' : 'Catatan Akhir DPB' }}
                                </label>
                                <textarea name="keterangan" id="keterangan" rows="2" class="form-control form-control-sm"
                                    placeholder="{{ $isPending ? 'Catatan awal DPB...' : 'Catatan unloading, misal: ada barang rusak 1 pcs, atau selisih...' }}">{{ $canvasSession->keterangan }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-3">
                            <a href="{{ route('canvas.show', $canvasSession->id) }}"
                                class="btn btn-light px-4 fw-semibold border hover-scale">
                                <i class="fa-solid fa-arrow-left me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success px-4 fw-semibold hover-scale text-white">
                                <i class="fa-solid fa-circle-check me-1"></i>
                                {{ $isPending ? 'Simpan Perubahan DPB' : ((!$isPending && isset($activeSessions) && $activeSessions->count() > 1) ? 'Selesaikan Semua DPB Aktif' : 'Selesaikan DPB & Unload') }}
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

            function updateAllConversions() {
                const isPending = @json($isPending);
                const selector = isPending ? '.input-qty-ambil' : '.input-qty-kembali';
                $(selector).each(function() {
                    const $el = $(this);
                    const rowId = $el.data('row-id');
                    const qtyVal = parseFloat($el.val()) || 0;
                    const isi = parseFloat($el.data('isi')) || 1;
                    const satuans = $el.data('satuans') || [];
                    const qtySmallest = qtyVal * isi;
                    const formatted = formatStokJS(qtySmallest, satuans);
                    $(`#convert-display-${rowId}`).text(formatted);
                });
            }

            // Trigger on load
            updateAllConversions();

            // Delete row handler
            $(document).on('click', '.btn-delete-row', function() {
                const $row = $(this).closest('tr');
                const rowCount = $('#details-table-body tr').length;
                if (rowCount <= 1) {
                    Swal.fire({
                        title: 'Minimal 1 Barang',
                        text: 'Minimal harus ada 1 barang dalam DPB!',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Hapus Barang?',
                    text: 'Apakah Anda yakin ingin menghapus barang ini dari DPB?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $row.remove();
                        // Re-index No column
                        $('#details-table-body tr').each(function(index) {
                            $(this).find('td:first').text(index + 1);
                        });
                        updateAllConversions();
                    }
                });
            });

            // Store previous value on focus
            $(document).on('focus', '.input-unit-qty', function() {
                $(this).data('prev-val', $(this).val());
            });

            // Listen on unit inputs change
            $(document).on('input change', '.input-unit-qty', function() {
                const $input = $(this);
                const prevVal = $input.data('prev-val') || 0;
                const rowId = $input.data('row-id');
                const isPending = @json($isPending);
                const $hiddenInput = isPending ? $('#qty-ambil-' + rowId) : $('#qty-kembali-' + rowId);
                const maxSmallest = parseFloat($hiddenInput.data('max-smallest'));
                const primaryIsi = parseFloat($hiddenInput.data('isi')) || 1;
                const satuans = $hiddenInput.data('satuans') || [];

                let totalSmallest = 0;
                $(`.input-qty-row-${rowId}`).each(function() {
                    const val = parseFloat($(this).val()) || 0;
                    const factor = parseFloat($(this).data('isi')) || 1;
                    totalSmallest += val * factor;
                });

                // Round values to prevent float precision errors
                totalSmallest = Math.round(totalSmallest * 10000) / 10000;

                if (!isPending && totalSmallest > maxSmallest) {
                    Swal.fire({
                        title: 'Melebihi Batas',
                        html: `Jumlah pengembalian tidak boleh melebihi jumlah ambil!<br><br>` +
                            `Maksimal ambil: <b>${formatStokJS(maxSmallest, satuans)}</b><br>` +
                            `Diinput: <b>${formatStokJS(totalSmallest, satuans)}</b>`,
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    $input.val(prevVal);
                    return;
                }

                $input.data('prev-val', $input.val());

                // Set the value of the hidden input in primary units
                const qtyVal = totalSmallest / primaryIsi;
                $hiddenInput.val(qtyVal);

                // Update live convert display
                $(`#convert-display-${rowId}`).text(formatStokJS(totalSmallest, satuans));
            });
        });
    </script>
@endpush
