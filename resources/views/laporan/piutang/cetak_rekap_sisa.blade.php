<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Rekap Sisa Piutang</title>
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

        .text-right {
            text-align: right;
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
            <h2 style="margin: 5px 0; font-size: 18px; text-transform: uppercase; margin-bottom: 0;">
                Rekap Sisa Piutang Penjualan Customer
            </h2>
            <div style="font-size: 14px; font-weight: bold; margin-top: 5px;">
                PER TANGGAL: {{ \Carbon\Carbon::parse($tanggal)->format('d-M-Y') }}
            </div>
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

            // Count overdue invoices relative to selected $tanggal
            $overdueCount = 0;
            $reportDate = \Carbon\Carbon::parse($tanggal);
            foreach ($items as $item) {
                $ljt = $item['pelanggan'] ? $item['pelanggan']->ljt ?? 30 : 30;
                $jatuh_tempo = \Carbon\Carbon::parse($item['tanggal'])->addDays($ljt);
                if ($reportDate->greaterThan($jatuh_tempo)) {
                    $overdueCount++;
                }
            }

            $isSpvSales =
                auth()->check() &&
                (strtolower(auth()->user()->role ?? '') === 'spv sales' || auth()->user()->hasRole('spv sales'));
            $colspanVal = $isSpvSales ? 7 : 5;
        @endphp

        <table class="kotak-rekap" style="margin-top: 10px;">
            <tr>
                <td style="width: 10%;">SALES</td>
                <td style="width: 2%; text-align:center;">:</td>
                <td style="width: 25%;">{{ $selectedSales }}</td>
                <td style="width: 10%;">FAKTUR KELUAR</td>
                <td style="width: 2%; text-align:center;">:</td>
                <td style="width: 20%;">{{ count($items) }}</td>
                <td style="width: 15%;">TOTAL HITUNG ADM (Rp)</td>
                <td style="width: 2%; text-align:center;">:</td>
                <td style="width: 15%;"></td>
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
                    <th style="width: 8%">TGL FAKTUR</th>
                    <th style="width: 8%">KODE TRANSAKSI</th>
                    <th style="width: 15%">NAMA PELANGGAN</th>
                    @if ($isSpvSales)
                        <th style="width: 8%">WILAYAH</th>
                        <th style="width: 8%">SUB WILAYAH</th>
                    @endif
                    <th style="width: 7%">SALES</th>
                    <th style="width: 7%">JUMLAH</th>
                    <th style="width: 14%">TITIP</th>
                    <th style="width: 12%">RETUR/POT.</th>
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
                        @if ($isSpvSales)
                            <td class="text-center">{{ $item['pelanggan']->wilayah->nama_wilayah ?? '-' }}</td>
                            <td class="text-center">{{ $item['pelanggan']->subWilayah->nama_wilayah ?? '-' }}</td>
                        @endif
                        <td>{{ $item['sales']->name ?? '-' }}</td>
                        <td style="text-align: right">{{ number_format($item['sisa_piutang'], 0, ',', '.') }}</td>
                        <td style="text-align: right"></td>
                        <td style="text-align: right"></td>
                    </tr>
                @endforeach
                <tr class="highlight">
                    <td colspan="{{ $colspanVal }}" class="text-center fw-bold">TOTAL</td>
                    <td class="text-right fw-bold">
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
