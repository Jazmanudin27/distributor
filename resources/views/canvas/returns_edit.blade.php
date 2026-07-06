@extends('layouts.app')
@section('title', 'Edit Setoran Penjualan Kanvas')
@section('content')
    <div class="row justify-content-start">
        <div class="col-lg-10 col-md-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header card-premium-header text-white py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="me-3 bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                            style="width: 45px; height: 45px;">
                            <i class="fa-solid fa-box-open fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Edit Setoran DPB: {{ $canvasSession->no_canvas }}</h5>
                            <small class="text-white-50 font-12">Koreksi kuantitas pengembalian sisa barang kanvas salesman ke gudang</small>
                        </div>
                    </div>
                    <a href="{{ route('canvas.returns.index') }}" class="btn btn-primary btn-sm fw-bold hover-scale">
                        <i class="fa-solid fa-arrow-left me-1 text-white"></i> Kembali
                    </a>
                </div>
                <div class="card-body p-4">
                    {{-- Session summary --}}
                    <div class="row bg-light rounded p-3 mb-4 border g-3">
                        <div class="col-md-4">
                            <span class="text-secondary small d-block">Salesman Kanvas</span>
                            <strong class="text-dark">{{ $canvasSession->sales->name ?? $canvasSession->kode_sales }}</strong>
                        </div>
                        <div class="col-md-4">
                            <span class="text-secondary small d-block">Tanggal Pengambilan</span>
                            <strong class="text-dark">{{ \Carbon\Carbon::parse($canvasSession->tanggal)->format('d-M-Y') }}</strong>
                        </div>
                        <div class="col-md-4">
                            <span class="text-secondary small d-block">Keterangan Setoran</span>
                            <span class="text-muted small">{{ $canvasSession->keterangan ?? '-' }}</span>
                        </div>
                    </div>

                    <form action="{{ route('canvas.returns.update', $canvasSession->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light text-secondary text-uppercase fs-7 font-11">
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th>Nama Barang</th>
                                    <th width="150" class="text-center">Satuan</th>
                                    <th width="140" class="text-end pe-3 bg-primary-subtle text-primary">Ambil (Loading)</th>
                                    <th width="140" class="text-end pe-3 bg-info-subtle text-info">Terjual (Sales)</th>
                                    <th width="240" class="text-center bg-success-subtle text-success fw-bold">Qty Kembali (Unload)</th>
                                    <th width="140" class="text-end pe-3">Expected Sisa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($canvasSession->details as $index => $detail)
                                    @php
                                        $expectedSisa = max(0.0, (float)$detail->qty_ambil - (float)$detail->qty_terjual);
                                        $qtyAmbilSmallest = $detail->qty_ambil * ($detail->barangSatuan->isi ?? 1);
                                        $qtyTerjualSmallest = $detail->qty_terjual * ($detail->barangSatuan->isi ?? 1);
                                        $qtyExpectedSmallest = $expectedSisa * ($detail->barangSatuan->isi ?? 1);
                                        $qtyKembaliSmallest = (float)$detail->qty_kembali * ($detail->barangSatuan->isi ?? 1);
                                        $satuans = $detail->barang->satuans;
                                    @endphp
                                    <tr>
                                        <td class="text-center fw-semibold text-secondary">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $detail->barang->nama_barang }}</div>
                                            <span class="text-secondary small font-11">Kode: {{ $detail->kode_barang }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-secondary border fw-semibold font-11 py-1 px-2.5" style="opacity: 0.85;">
                                                {{ $detail->barangSatuan->satuan ?? 'PCS' }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3 bg-primary-subtle text-primary fw-bold">
                                            <div class="fs-7">{{ $detail->barang->formatStok($qtyAmbilSmallest) }}</div>
                                            <small class="text-secondary font-11 d-block">({{ (float)$detail->qty_ambil }} {{ $detail->barangSatuan->satuan ?? 'PCS' }})</small>
                                        </td>
                                        <td class="text-end pe-3 bg-info-subtle text-info fw-bold">
                                            <div class="fs-7">{{ $detail->barang->formatStok($qtyTerjualSmallest) }}</div>
                                            <small class="text-secondary font-11 d-block">({{ (float)$detail->qty_terjual }} {{ $detail->barangSatuan->satuan ?? 'PCS' }})</small>
                                        </td>
                                        <td class="bg-success-subtle px-3">
                                            <input type="hidden" name="details[{{ $index }}][id]" value="{{ $detail->id }}">
                                            <input type="hidden" name="details[{{ $index }}][qty_kembali]"
                                                id="qty-kembali-{{ $index }}" class="input-qty-kembali"
                                                value="{{ (float)$detail->qty_kembali }}"
                                                data-isi="{{ $detail->barangSatuan->isi ?? 1 }}"
                                                data-row-id="{{ $index }}"
                                                data-max-smallest="{{ $qtyExpectedSmallest }}"
                                                data-satuans="{{ json_encode($satuans) }}">
                                            @php
                                                $unitValues = [];
                                                if ($satuans && $satuans->count() > 0) {
                                                    $sorted = $satuans->sortByDesc('isi');
                                                    $remaining = $qtyKembaliSmallest;
                                                    $count = $sorted->count();
                                                    $i = 0;
                                                    foreach ($sorted as $sat) {
                                                        $i++;
                                                        $factor = (float)($sat->isi ?: 1);
                                                        if ($i === $count) {
                                                            $unitQty = round($remaining / $factor, 4);
                                                            $unitValues[$sat->id] = (float)$unitQty;
                                                        } else {
                                                            $unitQty = floor(round($remaining / $factor, 8));
                                                            $unitValues[$sat->id] = (float)$unitQty;
                                                            $remaining = round($remaining - $unitQty * $factor, 4);
                                                        }
                                                    }
                                                } else {
                                                    $unitValues[0] = (float)$detail->qty_kembali;
                                                }
                                            @endphp
                                            <div class="d-flex flex-column gap-1 align-items-center">
                                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                    @if ($satuans && $satuans->count() > 0)
                                                        @foreach ($sorted as $sat)
                                                            @php
                                                                $val = $unitValues[$sat->id] ?? 0;
                                                            @endphp
                                                            <div class="input-group input-group-sm" style="width: 100px;">
                                                                <input type="number"
                                                                    class="form-control text-center input-unit-qty input-qty-row-{{ $index }}"
                                                                    data-isi="{{ $sat->isi }}"
                                                                    data-id="{{ $sat->id }}"
                                                                    data-row-id="{{ $index }}"
                                                                    value="{{ $val }}" min="0"
                                                                    step="any">
                                                                <span class="input-group-text bg-light text-secondary font-monospace"
                                                                    style="font-size: 10px; padding: 0.25rem 0.4rem;">{{ $sat->satuan }}</span>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="input-group input-group-sm" style="width: 100px;">
                                                            <input type="number"
                                                                class="form-control text-center input-unit-qty input-qty-row-{{ $index }}"
                                                                data-isi="1" data-id="0"
                                                                data-row-id="{{ $index }}"
                                                                value="{{ (float)$detail->qty_kembali }}" min="0"
                                                                step="any">
                                                            <span class="input-group-text bg-light text-secondary font-monospace"
                                                                style="font-size: 10px; padding: 0.25rem 0.4rem;">PCS</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="text-center mt-1 fw-bold text-success font-monospace live-convert-display"
                                                    id="convert-display-{{ $index }}" style="font-size: 11px;"></div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3 fw-bold">
                                            <div class="fs-7 text-dark">{{ $detail->barang->formatStok($qtyExpectedSmallest) }}</div>
                                            <small class="text-secondary font-11 d-block">({{ (float)$expectedSisa }} {{ $detail->barangSatuan->satuan ?? 'PCS' }})</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="row mt-4">
                            <div class="col-md-6 mb-3">
                                <label for="keterangan" class="form-label fs-7 fw-bold text-secondary">Catatan Koreksi Setoran</label>
                                <textarea name="keterangan" id="keterangan" rows="2" class="form-control form-control-sm"
                                    placeholder="Catatan perubahan/koreksi setoran...">{{ $canvasSession->keterangan }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-3">
                            <a href="{{ route('canvas.returns.index') }}" class="btn btn-light px-4 fw-semibold border hover-scale">
                                <i class="fa-solid fa-arrow-left me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success px-4 fw-semibold hover-scale text-white">
                                <i class="fa-solid fa-circle-check me-1"></i> Simpan Perubahan Setoran
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
                            let unitQty = Math.floor(Math.round((remaining / factor) * 100000000) / 100000000);
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
                $('.input-qty-kembali').each(function() {
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

            // Store previous value on focus
            $(document).on('focus', '.input-unit-qty', function() {
                $(this).data('prev-val', $(this).val());
            });

            // Listen on unit inputs change
            $(document).on('input change', '.input-unit-qty', function() {
                const $input = $(this);
                const prevVal = $input.data('prev-val') || 0;
                const rowId = $input.data('row-id');
                const $hiddenInput = $('#qty-kembali-' + rowId);
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

                if (totalSmallest > maxSmallest) {
                    Swal.fire({
                        title: 'Melebihi Batas',
                        html: `Jumlah pengembalian tidak boleh melebihi sisa barang (ambil - terjual)!<br><br>` +
                            `Maksimal kembali: <b>${formatStokJS(maxSmallest, satuans)}</b><br>` +
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
