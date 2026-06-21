@if (($jenis_laporan ?? 'rekap_sisa_piutang') === 'rekap_sisa_piutang')
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Rekap Tagihan</title>
    <style>
        body {
            font-family: Tahoma, sans-serif;
            font-size: 15px;
            margin: 10px;
            line-height: 1.2;
            width: 210mm;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: auto;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px 4px;
            vertical-align: top;
            white-space: nowrap;
            overflow: hidden;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .text-start {
            text-align: left;
        }

        .fw-bold {
            font-weight: bold;
        }

        .header-title {
            font-weight: bold;
            font-size: 24px;
            text-align: center;
        }

        .header-subtitle {
            font-size: 16px;
            text-align: center;
            margin-bottom: 10px;
        }

        .container {
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .col-8 {
            width: 65%;
        }

        .col-4 {
            width: 35%;
        }

        .highlight {
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        .col-no {
            width: 20px;
        }

        .col-kode {
            width: 80px;
        }

        .col-qty {
            width: 50px;
        }

        .col-satuan {
            width: 50px;
        }

        @media print {
            body {
                margin: 0;
            }
        }

        .kotak-rekap td,
        .kotak-rekap {
            border: none;
        }

        td.nama-pelanggan {
            white-space: normal !important;
            word-wrap: break-word;
            line-height: 1.2;
        }
    </style>
</head>

<body class="A4">
    <section class="sheet">
        <header style="text-align: center; margin-bottom: 20px;">
            <h1 style="margin: 0; font-size: 22px;">CV MITRA JAYA ABADI PERSADA</h1>
            <h2 style="margin: 5px 0; font-size: 18px; text-transform: uppercase;">
                Rekap Sisa Piutang Penjualan Customer
            </h2>
            <hr style="border: 1px solid #000; margin-top: 10px;">
        </header>

        @php
            $selectedSales = 'SEMUA SALES';
            if ($kode_sales) {
                $salesUser = $salesmen->where('nik', $kode_sales)->first();
                if ($salesUser) {
                    $selectedSales = strtoupper($salesUser->name);
                }
            }

            $selectedWilayah = 'SEMUA WILAYAH';
            if ($wilayah_id) {
                $wil = $wilayahs->where('kode_wilayah', $wilayah_id)->first();
                if ($wil) {
                    $selectedWilayah = strtoupper($wil->nama_wilayah);
                }
            }
            if ($sub_wilayah_id) {
                $subWil = $subWilayahs->where('kode_wilayah', $sub_wilayah_id)->first();
                if ($subWil) {
                    $selectedWilayah .= ' / ' . strtoupper($subWil->nama_wilayah);
                }
            }

            // Count overdue invoices
            $overdueCount = 0;
            $today = \Carbon\Carbon::now();
            foreach ($items as $item) {
                $ljt = $item['pelanggan']->ljt ?? 14;
                $jatuh_tempo = \Carbon\Carbon::parse($item['tanggal'])->addDays($ljt);
                if ($today->greaterThan($jatuh_tempo)) {
                    $overdueCount++;
                }
            }
        @endphp

        <table class="kotak-rekap" style="margin-top: 10px;">
            <tr>
                <td style="width: 15%;">SALES</td>
                <td style="width: 2%; text-align:center;">:</td>
                <td style="width: 25%;">{{ $selectedSales }}</td>
                <td style="width: 15%;">FAKTUR KELUAR</td>
                <td style="width: 2%; text-align:center;">:</td>
                <td style="width: 15%;">{{ count($items) }}</td>
                <td style="width: 18%;">TOTAL HITUNG ADM (Rp)</td>
                <td style="width: 2%; text-align:center;">:</td>
                <td style="width: 10%;"></td>
            </tr>
            <tr>
                <td>WILAYAH</td>
                <td style="text-align:center;">:</td>
                <td>{{ $selectedWilayah }}</td>
                <td>FAKTUR KEMBALI</td>
                <td style="text-align:center;">:</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>FAKTUR OVERDUE</td>
                <td style="text-align:center;">:</td>
                <td>{{ $overdueCount }}</td>
            </tr>
        </table>

        <br>

        <table>
            <thead>
                <tr>
                    <th style="width: 3%">No</th>
                    <th style="width: 12%">TGL FAKTUR</th>
                    <th style="width: 15%">KODE TRANSAKSI</th>
                    <th style="width: 30%">NAMA PELANGGAN</th>
                    <th style="width: 10%">SALES</th>
                    <th style="width: 12%">JUMLAH</th>
                    <th style="width: 9%">TITIP</th>
                    <th style="width: 9%">RETUR/POT.</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $num = 1;
                    $totalJumlah = 0;
                @endphp
                @foreach ($items as $item)
                    @php
                        $totalJumlah += $item['sisa_piutang'];
                    @endphp
                    <tr>
                        <td class="text-center" style="padding-bottom: 15px;">{{ $num++ }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($item['tanggal'])->format('d-M-Y') }}</td>
                        <td class="text-center">{{ $item['no_faktur'] }}</td>
                        <td class="nama-pelanggan">{{ $item['pelanggan']->nama_pelanggan ?? '-' }}</td>
                        <td>{{ $item['sales']->name ?? '-' }}</td>
                        <td style="text-align: right">{{ number_format($item['sisa_piutang'], 0, ',', '.') }}</td>
                        <td style="text-align: right"></td>
                        <td style="text-align: right"></td>
                    </tr>
                @endforeach
                <tr class="highlight">
                    <td colspan="5" class="text-center fw-bold">TOTAL</td>
                    <td class="text-right fw-bold" style="text-align: right">
                        {{ number_format($totalJumlah, 0, ',', '.') }}
                    </td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </section>
</body>

</html>
@else
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
        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1">LAPORAN PIUTANG & LIMIT KREDIT PELANGGAN
                ({{ strtoupper($jenis_laporan ?? 'rekap') }})</h4>
            @if ($kode_pelanggan)
                <div class="small">Pelanggan: {{ $items->first()['pelanggan']->nama_pelanggan ?? $kode_pelanggan }}
                </div>
            @endif
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
                </table>
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
@endif
