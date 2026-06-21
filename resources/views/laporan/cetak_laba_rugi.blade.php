<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Laba Rugi</title>
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
        .table-sm th, .table-sm td {
            font-size: 11px !important;
            padding: 6px 8px !important;
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
        .section-header {
            font-weight: bold;
            text-transform: uppercase;
            background-color: #e9ecef;
        }
        .indent-1 {
            padding-left: 20px !important;
        }
        .indent-2 {
            padding-left: 40px !important;
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
    <div class="container py-3" style="max-width: 700px;">
        {{-- HEADER --}}
        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1">LAPORAN LABA RUGI</h4>
            <div class="small">
                Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/Y') }}
            </div>
            <div class="small text-muted">Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</div>
            <hr>
        </div>

        {{-- CONTENT --}}
        <table class="table table-sm align-middle w-100">
            <thead>
                <tr class="table-light text-center">
                    <th colspan="2">DESKRIPSI</th>
                    <th width="200" class="text-end">NOMINAL (RP)</th>
                </tr>
            </thead>
            <tbody>
                <!-- 1. PENDAPATAN -->
                <tr class="section-header">
                    <td colspan="2">I. PENDAPATAN PENJUALAN</td>
                    <td class="text-end"></td>
                </tr>
                <tr>
                    <td colspan="2" class="indent-1">Penjualan Kotor (Gross Sales)</td>
                    <td class="text-end">{{ number_format($salesGross, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="indent-1">Retur Penjualan</td>
                    <td class="text-end text-danger">({{ number_format($salesReturn, 0, ',', '.') }})</td>
                </tr>
                <tr class="fw-bold">
                    <td colspan="2" class="indent-1">Total Pendapatan Bersih (Net Sales)</td>
                    <td class="text-end border-top border-dark">{{ number_format($salesNet, 0, ',', '.') }}</td>
                </tr>

                <!-- Spacer -->
                <tr><td colspan="3" style="border: none !important; height: 15px;"></td></tr>

                <!-- 2. HPP -->
                <tr class="section-header">
                    <td colspan="2">II. HARGA POKOK PENJUALAN (HPP)</td>
                    <td class="text-end"></td>
                </tr>
                <tr>
                    <td colspan="2" class="indent-1">HPP Penjualan</td>
                    <td class="text-end">{{ number_format($hppGross, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="indent-1">HPP Retur Penjualan</td>
                    <td class="text-end text-success">({{ number_format($hppReturn, 0, ',', '.') }})</td>
                </tr>
                <tr class="fw-bold">
                    <td colspan="2" class="indent-1">Total Harga Pokok Penjualan Bersih (Net COGS)</td>
                    <td class="text-end border-top border-dark">{{ number_format($hppNet, 0, ',', '.') }}</td>
                </tr>

                <!-- Spacer -->
                <tr><td colspan="3" style="border: none !important; height: 20px;"></td></tr>

                <!-- 3. LABA KOTOR -->
                <tr class="fw-bold table-light" style="font-size: 12px;">
                    <td colspan="2" style="font-size: 12px;">III. LABA KOTOR (GROSS PROFIT)</td>
                    <td class="text-end {{ $profit >= 0 ? 'text-primary' : 'text-danger' }}" style="font-size: 12px;">
                        {{ $profit < 0 ? '-' : '' }}Rp {{ number_format(abs($profit), 0, ',', '.') }}
                    </td>
                </tr>
                <tr class="fw-bold text-muted">
                    <td colspan="2" class="indent-1">Persentase Margin Laba Kotor (Gross Margin)</td>
                    <td class="text-end">{{ number_format($marginPercent, 2, ',', '.') }}%</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer Tanda Tangan -->
        <div class="row mt-5 text-center no-print">
            <div class="col-6 offset-6">
                <p class="mb-5">Disetujui Oleh,</p>
                <br><br>
                <p class="fw-bold mb-0">______________________</p>
                <p class="text-muted small">Owner / Pimpinan</p>
            </div>
        </div>
    </div>

    <!-- Print trigger -->
    @if(!isset($isExcel) || !$isExcel)
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    @endif
</body>
</html>
