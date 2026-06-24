<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Retur Pembelian</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #fff;
            font-family: "Inter", sans-serif;
            font-size: 11px;
            color: #000;
        }

        .table-sm th,
        .table-sm td {
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
            <h4 class="fw-bold mb-1">LAPORAN RETUR PEMBELIAN BARANG</h4>
            <div class="small">
                Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') }} s/d
                {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/Y') }}
            </div>
            @if ($kode_supplier)
                @php
                    $supplierName =
                        $suppliers->firstWhere('kode_supplier', $kode_supplier)->nama_supplier ?? $kode_supplier;
                @endphp
                <div class="small">Supplier: {{ $supplierName }}</div>
            @endif
            <div class="small text-muted">Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</div>
            <hr>
        </div>

        {{-- TABLES --}}
        @if ($items->isEmpty())
            <div class="text-center py-5">
                <p>Tidak ada data transaksi retur pembelian yang ditemukan.</p>
            </div>
        @else
            @if ($jenis_laporan === 'rekap')
                {{-- REKAP TABLES --}}
                <table class="table table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center">No</th>
                            <th>No Retur</th>
                            <th>Tanggal</th>
                            <th>Jenis Retur</th>
                            <th>Kondisi</th>
                            <th>No Faktur Asal</th>
                            <th>Supplier</th>
                            <th class="text-end">Total Nilai Retur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $num = 1;
                            $totRetur = 0;
                        @endphp

                        @if ($group_by_supplier === '1')
                            @foreach ($items as $supplierCode => $group)
                                @php
                                    $supplierName = $group->first()->supplier->nama_supplier ?? $supplierCode;
                                    $subRetur = $group->sum('total');
                                    $totRetur += $subRetur;
                                @endphp
                                <tr class="fw-bold bg-light">
                                    <td colspan="8">SUPPLIER: {{ $supplierName }} ({{ $supplierCode }})</td>
                                </tr>
                                @foreach ($group as $retur)
                                    <tr>
                                        <td class="text-center">{{ $num++ }}</td>
                                        <td>{{ $retur->no_retur }}</td>
                                        <td>{{ \Carbon\Carbon::parse($retur->tanggal)->format('d-m-Y') }}</td>
                                        <td>{{ $retur->jenis_retur }}</td>
                                        <td>{{ $retur->kondisi }}</td>
                                        <td>{{ $retur->no_faktur ?? '-' }}</td>
                                        <td>{{ $retur->supplier->nama_supplier ?? '-' }}</td>
                                        <td class="text-end fw-bold">{{ number_format($retur->total, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="fw-bold bg-light-subtle">
                                    <td colspan="7" class="text-end">Subtotal {{ $supplierName }}:</td>
                                    <td class="text-end">{{ number_format($subRetur, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @else
                            @foreach ($items as $retur)
                                @php
                                    $totRetur += $retur->total;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $num++ }}</td>
                                    <td>{{ $retur->no_retur }}</td>
                                    <td>{{ \Carbon\Carbon::parse($retur->tanggal)->format('d-m-Y') }}</td>
                                    <td>{{ $retur->jenis_retur }}</td>
                                    <td>{{ $retur->kondisi }}</td>
                                    <td>{{ $retur->no_faktur ?? '-' }}</td>
                                    <td>{{ $retur->supplier->nama_supplier ?? '-' }}</td>
                                    <td class="text-end fw-bold">{{ number_format($retur->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr class="table-light">
                            <td colspan="7" class="text-end">TOTAL KESELURUHAN RETUR:</td>
                            <td class="text-end">{{ number_format($totRetur, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            @else
                {{-- DETAIL TABLES --}}
                <table class="table table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center">No</th>
                            <th>No Retur</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th class="text-end">Qty</th>
                            <th>Satuan</th>
                            <th class="text-end">Harga Retur</th>
                            <th class="text-end">Subtotal Retur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $num = 1;
                            $totGrandDetail = 0;
                        @endphp

                        @if ($group_by_supplier === '1')
                            @foreach ($items as $supplierCode => $group)
                                @php
                                    $supplierName =
                                        $group->first()->returPembelian->supplier->nama_supplier ?? $supplierCode;
                                    $subTotal = $group->sum('subtotal_retur');
                                    $totGrandDetail += $subTotal;
                                @endphp
                                <tr class="fw-bold bg-light">
                                    <td colspan="10">SUPPLIER: {{ $supplierName }} ({{ $supplierCode }})</td>
                                </tr>
                                @foreach ($group as $detail)
                                    <tr>
                                        <td class="text-center">{{ $num++ }}</td>
                                        <td>{{ $detail->no_retur }}</td>
                                        <td>{{ \Carbon\Carbon::parse($detail->returPembelian->tanggal)->format('d-m-Y') }}
                                        </td>
                                        <td>{{ $detail->returPembelian->supplier->nama_supplier ?? '-' }}</td>
                                        <td>{{ $detail->kode_barang }}</td>
                                        <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                                        <td class="text-end">{{ number_format($detail->qty, 2, ',', '.') }}</td>
                                        <td>{{ $detail->barangSatuan->satuan ?? 'PCS' }}</td>
                                        <td class="text-end">{{ number_format($detail->harga_retur, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($detail->subtotal_retur, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                <tr class="fw-bold bg-light-subtle">
                                    <td colspan="9" class="text-end">Subtotal {{ $supplierName }}:</td>
                                    <td class="text-end">{{ number_format($subTotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @else
                            @foreach ($items as $detail)
                                @php
                                    $totGrandDetail += $detail->subtotal_retur;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $num++ }}</td>
                                    <td>{{ $detail->no_retur }}</td>
                                    <td>{{ \Carbon\Carbon::parse($detail->returPembelian->tanggal)->format('d-m-Y') }}
                                    </td>
                                    <td>{{ $detail->returPembelian->supplier->nama_supplier ?? '-' }}</td>
                                    <td>{{ $detail->kode_barang }}</td>
                                    <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($detail->qty, 2, ',', '.') }}</td>
                                    <td>{{ $detail->barangSatuan->satuan ?? 'PCS' }}</td>
                                    <td class="text-end">{{ number_format($detail->harga_retur, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($detail->subtotal_retur, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr class="table-light">
                            <td colspan="9" class="text-end">TOTAL KESELURUHAN RETUR:</td>
                            <td class="text-end">{{ number_format($totGrandDetail, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        @endif
    </div>


</body>

</html>
