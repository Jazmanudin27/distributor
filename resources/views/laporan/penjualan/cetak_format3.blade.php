<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Penjualan - Format 3</title>
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

        .table-simple th {
            background-color: #0d6efd !important;
            color: #fff !important;
            font-size: 11px !important;
            padding: 8px 6px !important;
            border: 1px solid #dee2e6 !important;
            text-align: center !important;
            font-weight: bold !important;
            white-space: nowrap !important;
        }

        .table-simple td {
            font-size: 11px !important;
            padding: 6px 6px !important;
            border: 1px solid #dee2e6 !important;
            white-space: nowrap !important;
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

@php
    $isExcel = isset($isExcel) ? $isExcel : false;
    $numFmt = function ($val, $decimals = 0) use ($isExcel) {
        if ($isExcel) {
            return $decimals === 0 ? (int) round((float) $val) : round((float) $val, $decimals);
        }
        return number_format((float) $val, $decimals, ',', '.');
    };

    $pctFmt = function ($val, $decimals = 2) use ($isExcel) {
        if ($isExcel) {
            return $val ? round((float) $val, $decimals) / 100 : '';
        }
        return $val > 0 ? number_format((float) $val, $decimals, ',', '.') : '';
    };

    if (!function_exists('formatTanggalIndo')) {
        function formatTanggalIndo($date)
        {
            if (!$date) {
                return '-';
            }
            $d = \Carbon\Carbon::parse($date);
            $bulanIndo = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember',
            ];
            return $d->day . ' ' . $bulanIndo[$d->month] . ' ' . $d->year;
        }
    }

    if (!function_exists('formatTanggalShortIndo')) {
        function formatTanggalShortIndo($date)
        {
            if (!$date) {
                return '-';
            }
            $d = \Carbon\Carbon::parse($date);
            $bulanIndo = [
                1 => 'Jan',
                2 => 'Feb',
                3 => 'Mar',
                4 => 'Apr',
                5 => 'Mei',
                6 => 'Jun',
                7 => 'Jul',
                8 => 'Ags',
                9 => 'Sep',
                10 => 'Okt',
                11 => 'Nov',
                12 => 'Des',
            ];
            return $d->day . '-' . $bulanIndo[$d->month] . '-' . $d->year;
        }
    }
@endphp

<body>
    <div class="container-fluid py-3">
        {{-- HEADER --}}
        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1">LAPORAN PENJUALAN (FORMAT 3)</h4>
            <div class="small">
                Periode: {{ formatTanggalIndo($tanggal_mulai) }} s/d {{ formatTanggalIndo($tanggal_akhir) }}
            </div>
            @if ($kode_sales)
                @php $salesName = $salesmen->firstWhere('nik', $kode_sales)->name ?? $kode_sales; @endphp
                <div class="small">Salesman: {{ $salesName }}</div>
            @endif
            @if ($kode_pelanggan)
                @php $pelangganName = $pelanggans->firstWhere('kode_pelanggan', $kode_pelanggan)->nama_pelanggan ?? $kode_pelanggan; @endphp
                <div class="small">Pelanggan: {{ $pelangganName }}</div>
            @endif
            <div class="small text-muted">Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</div>
            <hr>
        </div>

        {{-- TABLES --}}
        @if ($items->isEmpty())
            <div class="text-center py-5">
                <p>Tidak ada data transaksi penjualan yang ditemukan.</p>
            </div>
        @else
            {{-- DETAIL SIMPLE TABLES (Format 3) --}}
            <table class="table table-sm align-middle w-100 table-simple"
                style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr style="background-color:#0d6efd; color:#ffffff;">
                        <th class="text-center"
                            style="border: 1px solid #000; text-align: center; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                            No</th>
                        <th class="text-center"
                            style="border: 1px solid #000; text-align: center; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                            Tanggal</th>
                        <th
                            style="border: 1px solid #000; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px; text-align: left;">
                            Nama Pelanggan</th>
                        <th class="text-center"
                            style="border: 1px solid #000; text-align: center; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                            No. Faktur</th>
                        <th
                            style="border: 1px solid #000; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px; text-align: left;">
                            Nama Barang</th>
                        <th class="text-end"
                            style="border: 1px solid #000; text-align: right; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                            Harga</th>
                        <th class="text-end"
                            style="border: 1px solid #000; text-align: right; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                            Qty</th>
                        <th class="text-center"
                            style="border: 1px solid #000; text-align: center; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                            Satuan</th>
                        <th class="text-center"
                            style="border: 1px solid #000; text-align: center; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                            D1</th>
                        <th class="text-center"
                            style="border: 1px solid #000; text-align: center; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                            D2</th>
                        <th class="text-center"
                            style="border: 1px solid #000; text-align: center; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                            D3</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $num = 1;
                    @endphp
                    @foreach ($items as $row)
                        <tr>
                            <td class="text-center"
                                style="border: 1px solid #dee2e6; text-align: center; padding: 6px 6px;">
                                {{ $num++ }}</td>
                            <td class="text-center"
                                style="border: 1px solid #dee2e6; text-align: center; padding: 6px 6px;">
                                {{ formatTanggalShortIndo($row->tanggal) }}</td>
                            <td style="border: 1px solid #dee2e6; padding: 6px 6px;">
                                {{ $row->nama_pelanggan ?? '-' }}</td>
                            <td class="text-center"
                                style="border: 1px solid #dee2e6; text-align: center; padding: 6px 6px; mso-number-format: '\@';">
                                {{ $row->no_faktur }}</td>
                            <td style="border: 1px solid #dee2e6; padding: 6px 6px;">{{ $row->nama_barang ?? '-' }}
                            </td>
                            <td class="text-end"
                                style="border: 1px solid #dee2e6; text-align: right; padding: 6px 6px; mso-number-format:'#,##0';">
                                {{ $numFmt($row->harga) }}</td>
                            <td class="text-end"
                                style="border: 1px solid #dee2e6; text-align: right; padding: 6px 6px; mso-number-format:'#,##0';">
                                {{ $numFmt($row->qty) }}</td>
                            <td class="text-center"
                                style="border: 1px solid #dee2e6; text-align: center; padding: 6px 6px;">
                                {{ $row->satuan ?? 'PCS' }}</td>
                            <td class="text-center"
                                style="border: 1px solid #dee2e6; text-align: center; padding: 6px 6px; mso-number-format:'0.00%';">
                                {{ $row->diskon1_persen > 0 ? $pctFmt($row->diskon1_persen) : '' }}
                            </td>
                            <td class="text-center"
                                style="border: 1px solid #dee2e6; text-align: center; padding: 6px 6px; mso-number-format:'0.00%';">
                                {{ $row->diskon2_persen > 0 ? $pctFmt($row->diskon2_persen) : '' }}
                            </td>
                            <td class="text-center"
                                style="border: 1px solid #dee2e6; text-align: center; padding: 6px 6px; mso-number-format:'0.00%';">
                                {{ $row->diskon3_persen > 0 ? $pctFmt($row->diskon3_persen) : '' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>

</html>
