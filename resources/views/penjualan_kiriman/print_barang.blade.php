<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Rekap Kiriman Barang</title>
    <style>
        body {
            font-family: Tahoma, sans-serif;
            font-size: 14px;
            margin: 10px;
            line-height: 1.2;
            width: 210mm;
            color: #000;
            background-color: #fff;
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
            white-space: nowrap;
            overflow: hidden;
            color: #000;
        }

        .wrap-text {
            white-space: normal;
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
                width: 210mm;
            }

            * {
                color: #000 !important;
                background-color: transparent !important;
            }
        }
    </style>
</head>

<body>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
        <tr>
            <td colspan="2"
                style="text-align: center; font-size: 24px; font-weight: bold; padding-bottom: 5px; border: none;">
                REKAP KIRIMAN BARANG
            </td>
        </tr>
        <tr>
            <td style="width: 60%; border: none; vertical-align: top;">
                <table style="border-collapse: collapse; width: 100%;">
                    <tr>
                        <td style="width: 25%; font-size: 15px; border: none;">Tanggal Pengiriman</td>
                        <td style="width: 5%; text-align: center; border: none;">:</td>
                        <td style="border: none; font-size: 15px;">{{ \Carbon\Carbon::parse($tanggal)->format('d-M-Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 15px; border: none;">Wilayah</td>
                        <td style="text-align: center; border: none;">:</td>
                        <td style="border: none; font-size: 15px;">{{ strtoupper($wilayah->nama_wilayah) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="container">
        <!-- Detail Barang (col-8) -->
        <div class="col-8">
            <table>
                <thead>
                    <tr class="text-center">
                        <th class="col-no">No</th>
                        <th class="col-kode">Kode</th>
                        <th class="wrap-text">Nama Barang</th>
                        <th class="col-qty">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedDetails = collect($details)->groupBy('kode_barang');
                        $no = 1;
                    @endphp
                    @foreach ($groupedDetails as $kodeBarang => $group)
                        @php
                            $first = $group->first();
                            $qtyStrings = [];
                            foreach ($group as $item) {
                                $qtyStrings[] = floatval($item->total_qty) . ' ' . strtoupper($item->satuan);
                            }
                            $qtyDisplay = implode(' + ', $qtyStrings);
                        @endphp
                        <tr>
                            <td class="text-center">{{ $no++ }}</td>
                            <td>{{ $kodeBarang }}</td>
                            <td class="wrap-text">{{ strtoupper($first->nama_barang) }}</td>
                            <td class="text-start">
                                {{ $qtyDisplay }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Rekap Faktur (col-4) -->
        <div class="col-4" style="zoom:80%">
            <table>
                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>No Faktur</th>
                        <th>Tanggal</th>
                        <th class="wrap-text">Pelanggan</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalSum = 0;
                    @endphp
                    @foreach ($invoices as $idx => $inv)
                        @php
                            $totalSum += (float) $inv->grand_total;
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $inv->no_faktur }}</td>
                            <td>{{ \Carbon\Carbon::parse($inv->tanggal)->format('d-M-Y') }}</td>
                            <td class="wrap-text">{{ strtoupper($inv->nama_pelanggan) }}</td>
                            <td class="text-end">{{ number_format((float) $inv->grand_total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="highlight">
                        <td colspan="4" class="text-end">TOTAL</td>
                        <td class="text-end">{{ number_format($totalSum, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</body>

</html>
