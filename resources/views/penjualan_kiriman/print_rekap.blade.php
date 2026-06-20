<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekap Kiriman Sales</title>
    <style>
        body {
            font-family: Tahoma, sans-serif;
            font-size: 13px;
            margin: 0;
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
            padding: 2px 3px;
            white-space: nowrap;
            overflow: hidden;
            color: #000;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .header-table td {
            vertical-align: top;
            padding: 2px 4px;
            border: none;
        }

        .highlight {
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        .col-no {
            width: 20px;
        }

        .col-faktur {
            width: 100px;
        }

        .col-tanggal {
            width: 80px;
        }

        .col-total {
            width: 90px;
        }

        .header-title {
            font-weight: bold;
            font-size: 24px;
            text-align: center;
        }

        .header-subtitle {
            font-size: 18px;
            text-align: center;
            margin-bottom: 10px;
        }

        @media print {
            body {
                width: 210mm;
                margin: 0;
            }

            /* Prevent browser styling from overriding black text */
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
                REKAP KIRIMAN
            </td>
        </tr>
        <tr>
            <td style="width: 60%; border: none; vertical-align: top;">
                <table style="border-collapse: collapse; width: 100%;">
                    <tr>
                        <td style="width: 50%; font-size: 15px; border: none;">Tanggal Pengiriman</td>
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
            <td style="width: 40%; border: none; vertical-align: top;">
                <table style="border-collapse: collapse; width: 100%;">
                    <tr>
                        <td style="width: 40%; font-size: 15px; border: none;">Driver</td>
                        <td style="width: 5%; text-align: center; border: none;">:</td>
                        <td style="border: none; font-size: 15px;">__________________</td>
                    </tr>
                    <tr>
                        <td style="font-size: 15px; border: none;">Dropping</td>
                        <td style="text-align: center; border: none;">:</td>
                        <td style="border: none; font-size: 15px;">__________________</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table>
        <thead>
            <tr class="text-center">
                <th class="col-no">No</th>
                <th class="col-pelanggan">Pelanggan</th>
                <th class="col-faktur">No Faktur</th>
                <th class="col-tanggal">Tanggal</th>
                <th class="col-sales">Sales</th>
                <th class="col-total">Total</th>
                <th class="col-keterangan">Keterangan</th>
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
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ strtoupper($inv->nama_pelanggan) }}</td>
                    <td>{{ $inv->no_faktur }}</td>
                    <td>{{ \Carbon\Carbon::parse($inv->tanggal)->format('d-M-Y') }}</td>
                    <td>{{ strtoupper($inv->nama_sales ?? '-') }}</td>
                    <td class="text-end">{{ number_format((float) $inv->grand_total, 0, ',', '.') }}</td>
                    <td>{{ $inv->keterangan }}</td>
                </tr>
            @endforeach
            <tr class="highlight">
                <td colspan="5" class="text-end">TOTAL</td>
                <td class="text-end">Rp{{ number_format($totalSum, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

</body>

</html>
