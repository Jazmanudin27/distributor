<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Pembelian</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
        }
        .table-sm th, .table-sm td {
            font-size: 11px !important;
            padding: 4px 6px !important;
            border: 1px solid #000 !important;
        }
        .table-light th {
            background-color: #f2f2f2 !important;
            color: #000 !important;
        }
        hr {
            border-top: 1px dashed #000;
            opacity: 1;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-3">
        {{-- HEADER --}}
        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1">LAPORAN PEMBELIAN BARANG</h4>
            <div class="small">
                Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/Y') }}
            </div>
            @if($kode_supplier)
                @php
                    $supplierName = $suppliers->firstWhere('kode_supplier', $kode_supplier)->nama_supplier ?? $kode_supplier;
                @endphp
                <div class="small">Supplier: {{ $supplierName }}</div>
            @endif
            <div class="small text-muted">Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</div>
            <hr>
        </div>

        {{-- TABLES --}}
        @if ($items->isEmpty())
            <div class="text-center py-5">
                <p>Tidak ada data transaksi pembelian yang ditemukan.</p>
            </div>
        @else
            @if ($jenis_laporan === 'rekap')
                {{-- REKAP TABLES --}}
                <table class="table table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center">No</th>
                            <th>No Faktur</th>
                            <th>Tanggal</th>
                            <th>Jatuh Tempo</th>
                            <th>Supplier</th>
                            <th>PO</th>
                            <th class="text-end">Potongan</th>
                            <th class="text-end">Pajak</th>
                            <th class="text-end">Biaya Lain</th>
                            <th class="text-end">Claim</th>
                            <th class="text-end">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $num = 1;
                            $totPotongan = 0;
                            $totPajak = 0;
                            $totBiaya = 0;
                            $totClaim = 0;
                            $totGrand = 0;
                        @endphp

                        @if ($group_by_supplier === '1')
                            @foreach ($items as $supplierCode => $group)
                                @php
                                    $supplierName = $group->first()->supplier->nama_supplier ?? $supplierCode;
                                    $subPotongan = $group->sum('potongan');
                                    $subPajak = $group->sum('pajak');
                                    $subBiaya = $group->sum('biaya_lain');
                                    $subClaim = $group->sum('potongan_claim');
                                    $subGrand = $group->sum('grand_total');

                                    $totPotongan += $subPotongan;
                                    $totPajak += $subPajak;
                                    $totBiaya += $subBiaya;
                                    $totClaim += $subClaim;
                                    $totGrand += $subGrand;
                                @endphp
                                <tr class="fw-bold bg-light">
                                    <td colspan="11">SUPPLIER: {{ $supplierName }} ({{ $supplierCode }})</td>
                                </tr>
                                @foreach ($group as $invoice)
                                    <tr>
                                        <td class="text-center">{{ $num++ }}</td>
                                        <td>{{ $invoice->no_faktur }}</td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->tanggal)->format('d-m-Y') }}</td>
                                        <td>{{ $invoice->jatuh_tempo ? \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('d-m-Y') : '-' }}</td>
                                        <td>{{ $invoice->supplier->nama_supplier ?? '-' }}</td>
                                        <td>{{ $invoice->no_po ?? '-' }}</td>
                                        <td class="text-end">{{ number_format($invoice->potongan, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($invoice->pajak, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($invoice->biaya_lain, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($invoice->potongan_claim, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold">{{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                <tr class="fw-bold bg-light-subtle">
                                    <td colspan="6" class="text-end">Subtotal {{ $supplierName }}:</td>
                                    <td class="text-end">{{ number_format($subPotongan, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($subPajak, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($subBiaya, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($subClaim, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($subGrand, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @else
                            @foreach ($items as $invoice)
                                @php
                                    $totPotongan += $invoice->potongan;
                                    $totPajak += $invoice->pajak;
                                    $totBiaya += $invoice->biaya_lain;
                                    $totClaim += $invoice->potongan_claim;
                                    $totGrand += $invoice->grand_total;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $num++ }}</td>
                                    <td>{{ $invoice->no_faktur }}</td>
                                    <td>{{ \Carbon\Carbon::parse($invoice->tanggal)->format('d-m-Y') }}</td>
                                    <td>{{ $invoice->jatuh_tempo ? \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('d-m-Y') : '-' }}</td>
                                    <td>{{ $invoice->supplier->nama_supplier ?? '-' }}</td>
                                    <td>{{ $invoice->no_po ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($invoice->potongan, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($invoice->pajak, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($invoice->biaya_lain, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($invoice->potongan_claim, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">{{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr class="table-light">
                            <td colspan="6" class="text-end">TOTAL KESELURUHAN:</td>
                            <td class="text-end">{{ number_format($totPotongan, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($totPajak, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($totBiaya, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($totClaim, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($totGrand, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            @else
                {{-- DETAIL TABLES --}}
                <table class="table table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center">No</th>
                            <th>No Faktur</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th class="text-end">Qty</th>
                            <th>Satuan</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Diskon Item</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $num = 1;
                            $totSubtotal = 0;
                            $totDiskon = 0;
                            $totGrandDetail = 0;
                        @endphp

                        @if ($group_by_supplier === '1')
                            @foreach ($items as $supplierCode => $group)
                                @php
                                    $supplierName = $group->first()->pembelian->supplier->nama_supplier ?? $supplierCode;
                                    $subSubtotal = $group->sum('subtotal');
                                    $subDiskon = $group->sum('diskon');
                                    $subTotal = $group->sum('total');

                                    $totSubtotal += $subSubtotal;
                                    $totDiskon += $subDiskon;
                                    $totGrandDetail += $subTotal;
                                @endphp
                                <tr class="fw-bold bg-light">
                                    <td colspan="11">SUPPLIER: {{ $supplierName }} ({{ $supplierCode }})</td>
                                </tr>
                                @foreach ($group as $detail)
                                    <tr>
                                        <td class="text-center">{{ $num++ }}</td>
                                        <td>{{ $detail->no_faktur }}</td>
                                        <td>{{ \Carbon\Carbon::parse($detail->pembelian->tanggal)->format('d-m-Y') }}</td>
                                        <td>{{ $detail->pembelian->supplier->nama_supplier ?? '-' }}</td>
                                        <td>{{ $detail->kode_barang }}</td>
                                        <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                                        <td class="text-end">{{ number_format($detail->qty, 2, ',', '.') }}</td>
                                        <td>{{ $detail->satuan }}</td>
                                        <td class="text-end">{{ number_format($detail->harga, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($detail->diskon, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold">{{ number_format($detail->total, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                <tr class="fw-bold bg-light-subtle">
                                    <td colspan="8" class="text-end">Subtotal {{ $supplierName }}:</td>
                                    <td class="text-end">{{ number_format($subSubtotal, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($subDiskon, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($subTotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @else
                            @foreach ($items as $detail)
                                @php
                                    $totSubtotal += $detail->subtotal;
                                    $totDiskon += $detail->diskon;
                                    $totGrandDetail += $detail->total;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $num++ }}</td>
                                    <td>{{ $detail->no_faktur }}</td>
                                    <td>{{ \Carbon\Carbon::parse($detail->pembelian->tanggal)->format('d-m-Y') }}</td>
                                    <td>{{ $detail->pembelian->supplier->nama_supplier ?? '-' }}</td>
                                    <td>{{ $detail->kode_barang }}</td>
                                    <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($detail->qty, 2, ',', '.') }}</td>
                                    <td>{{ $detail->satuan }}</td>
                                    <td class="text-end">{{ number_format($detail->harga, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($detail->diskon, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">{{ number_format($detail->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr class="table-light">
                            <td colspan="8" class="text-end">TOTAL KESELURUHAN:</td>
                            <td class="text-end">{{ number_format($totSubtotal, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($totDiskon, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($totGrandDetail, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        @endif
    </div>


</body>
</html>
