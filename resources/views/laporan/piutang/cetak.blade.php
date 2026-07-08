<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Piutang Pelanggan</title>
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
        @php
            $selectedWilayah = 'SEMUA WILAYAH';
            if (!empty($wilayah_id)) {
                $wil = isset($wilayahs) ? $wilayahs->where('kode_wilayah', $wilayah_id)->first() : null;
                if ($wil) {
                    $selectedWilayah = strtoupper($wil->nama_wilayah);
                }
            }
            if (!empty($sub_wilayah_id)) {
                $subWil = isset($subWilayahs) ? $subWilayahs->where('kode_wilayah', $sub_wilayah_id)->first() : null;
                if ($subWil) {
                    $selectedWilayah .= ' / ' . strtoupper($subWil->nama_wilayah);
                }
            }
        @endphp
        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1">LAPORAN PIUTANG & LIMIT KREDIT PELANGGAN
                ({{ strtoupper($jenis_laporan ?? 'rekap') }})</h4>
            @if ($kode_pelanggan)
                <div class="small">Pelanggan: {{ $items->first()['pelanggan']->nama_pelanggan ?? $kode_pelanggan }}
                </div>
            @endif
            @if (isset($kode_supplier) && $kode_supplier)
                @php $supplierName = $suppliers->firstWhere('kode_supplier', $kode_supplier)->nama_supplier ?? $kode_supplier; @endphp
                <div class="small">Supplier: <strong>{{ strtoupper($supplierName) }}</strong></div>
            @endif
            <div class="small">Wilayah: <strong>{{ $selectedWilayah }}</strong></div>
            <div class="small text-muted">Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</div>
            <hr>
        </div>

        {{-- TABLES --}}
        @if ($items->isEmpty())
            <div class="text-center py-5">
                <p>Tidak ada data piutang pelanggan yang ditemukan.</p>
            </div>
        @else
            @if (($jenis_laporan ?? 'rekap') === 'rekap')
                <table class="table table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center">No</th>
                            <th>Kode</th>
                            <th>Nama Pelanggan</th>
                            <th>Wilayah</th>
                            <th class="text-end">Limit Kredit</th>
                            <th class="text-end">Outstanding Piutang</th>
                            <th class="text-end">Sisa Limit</th>
                            <th class="text-end">Piutang Overdue</th>
                            <th class="text-center">Faktur Overdue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $num = 1;
                            $totLimit = 0;
                            $totOutstanding = 0;
                            $totSisaLimit = 0;
                            $totOverdue = 0;
                        @endphp
                        @foreach ($items as $item)
                            @php
                                $totLimit += $item['limit_kredit'];
                                $totOutstanding += $item['outstanding'];
                                $totSisaLimit += $item['sisa_limit'];
                                $totOverdue += $item['total_overdue'];
                            @endphp
                            <tr>
                                <td class="text-center">{{ $num++ }}</td>
                                <td>{{ $item['pelanggan']->kode_pelanggan }}</td>
                                <td>{{ $item['pelanggan']->nama_pelanggan }}</td>
                                <td>{{ $item['pelanggan']->wilayah->nama_wilayah ?? '-' }}</td>
                                <td class="text-end">{{ number_format($item['limit_kredit'], 0, ',', '.') }}</td>
                                <td class="text-end fw-bold text-danger">
                                    {{ number_format($item['outstanding'], 0, ',', '.') }}</td>
                                <td class="text-end text-success">{{ number_format($item['sisa_limit'], 0, ',', '.') }}
                                </td>
                                <td class="text-end text-danger">
                                    {{ number_format($item['total_overdue'], 0, ',', '.') }}</td>
                                <td class="text-center">{{ $item['overdue_count'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr class="table-light">
                            <td colspan="4" class="text-end">TOTAL KESELURUHAN:</td>
                            <td class="text-end">{{ number_format($totLimit, 0, ',', '.') }}</td>
                            <td class="text-end text-danger">{{ number_format($totOutstanding, 0, ',', '.') }}</td>
                            <td class="text-end text-success">{{ number_format($totSisaLimit, 0, ',', '.') }}</td>
                            <td class="text-end text-danger">{{ number_format($totOverdue, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @elseif (($jenis_laporan ?? 'rekap') === 'aging')
                    <table class="table table-sm align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="40" class="text-center">No</th>
                                <th>Kode</th>
                                <th>Nama Pelanggan</th>
                                <th>Wilayah</th>
                                <th class="text-end">Total Piutang</th>
                                <th class="text-end">Belum JT</th>
                                <th class="text-end">1 - 30 Hari</th>
                                <th class="text-end">31 - 60 Hari</th>
                                <th class="text-end">61 - 90 Hari</th>
                                <th class="text-end">> 90 Hari</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $num = 1;
                                $totPiutang = 0;
                                $totBelumJt = 0;
                                $tot1_30 = 0;
                                $tot31_60 = 0;
                                $tot61_90 = 0;
                                $tot90 = 0;
                            @endphp
                            @foreach ($items as $item)
                                @php
                                    $totPiutang += $item['total_piutang'];
                                    $totBelumJt += $item['belum_jt'];
                                    $tot1_30 += $item['overdue_1_30'];
                                    $tot31_60 += $item['overdue_31_60'];
                                    $tot61_90 += $item['overdue_61_90'];
                                    $tot90 += $item['overdue_90'];
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $num++ }}</td>
                                    <td>{{ $item['pelanggan']->kode_pelanggan }}</td>
                                    <td>{{ $item['pelanggan']->nama_pelanggan }}</td>
                                    <td>{{ $item['pelanggan']->wilayah->nama_wilayah ?? '-' }}</td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($item['total_piutang'], 0, ',', '.') }}</td>
                                    <td class="text-end text-success">
                                        {{ number_format($item['belum_jt'], 0, ',', '.') }}</td>
                                    <td class="text-end text-danger">
                                        {{ number_format($item['overdue_1_30'], 0, ',', '.') }}</td>
                                    <td class="text-end text-danger">
                                        {{ number_format($item['overdue_31_60'], 0, ',', '.') }}</td>
                                    <td class="text-end text-danger">
                                        {{ number_format($item['overdue_61_90'], 0, ',', '.') }}</td>
                                    <td class="text-end text-danger">
                                        {{ number_format($item['overdue_90'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="fw-bold">
                            <tr class="table-light">
                                <td colspan="4" class="text-end">TOTAL KESELURUHAN:</td>
                                <td class="text-end">{{ number_format($totPiutang, 0, ',', '.') }}</td>
                                <td class="text-end text-success">{{ number_format($totBelumJt, 0, ',', '.') }}</td>
                                <td class="text-end text-danger">{{ number_format($tot1_30, 0, ',', '.') }}</td>
                                <td class="text-end text-danger">{{ number_format($tot31_60, 0, ',', '.') }}</td>
                                <td class="text-end text-danger">{{ number_format($tot61_90, 0, ',', '.') }}</td>
                                <td class="text-end text-danger">{{ number_format($tot90, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @else
                    <table class="table table-sm align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="40" class="text-center">No</th>
                                <th>No Faktur</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Jatuh Tempo</th>
                                <th class="text-center">Umur Piutang</th>
                                <th class="text-end">Grand Total</th>
                                <th class="text-end">Total Bayar</th>
                                <th class="text-end">Retur PF</th>
                                <th class="text-end">Sisa Piutang</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $num = 1;
                                $totGrand = 0;
                                $totBayar = 0;
                                $totRetur = 0;
                                $totSisa = 0;
                            @endphp
                            @foreach ($items as $item)
                                @php
                                    $totGrand += $item['grand_total'];
                                    $totBayar += $item['total_bayar'];
                                    $totRetur += $item['total_retur'] ?? 0;
                                    $totSisa += $item['sisa_piutang'];
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $num++ }}</td>
                                    <td>{{ $item['no_faktur'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item['tanggal'])->format('d/m/Y') }}</td>
                                    <td>{{ $item['pelanggan']->nama_pelanggan }}
                                        ({{ $item['pelanggan']->kode_pelanggan }})
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($item['jatuh_tempo'])->format('d/m/Y') }}</td>
                                    <td class="text-center">{{ $item['umur_piutang'] }} Hari</td>
                                    <td class="text-end">{{ number_format($item['grand_total'], 0, ',', '.') }}</td>
                                    <td class="text-end text-success">
                                        {{ number_format($item['total_bayar'], 0, ',', '.') }}</td>
                                    <td class="text-end text-warning">
                                        {{ number_format($item['total_retur'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold text-danger">
                                        {{ number_format($item['sisa_piutang'], 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if ($item['status'] === 'OVERDUE')
                                            <span class="text-danger fw-bold">OVERDUE</span>
                                        @else
                                            <span class="text-success">LANCAR</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="fw-bold">
                            <tr class="table-light">
                                <td colspan="6" class="text-end">TOTAL KESELURUHAN:</td>
                                <td class="text-end">{{ number_format($totGrand, 0, ',', '.') }}</td>
                                <td class="text-end text-success">{{ number_format($totBayar, 0, ',', '.') }}</td>
                                <td class="text-end text-warning">{{ number_format($totRetur, 0, ',', '.') }}</td>
                                <td class="text-end text-danger">{{ number_format($totSisa, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
            @endif
        @endif
    </div>


</body>

</html>
