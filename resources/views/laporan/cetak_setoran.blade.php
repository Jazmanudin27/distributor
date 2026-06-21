<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Setoran Penjualan</title>
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
            <h4 class="fw-bold mb-1">LAPORAN SETORAN & PENERIMAAN PEMBAYARAN SALES</h4>
            <div class="small">
                Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/Y') }}
            </div>
            @if($kode_sales)
                @php $salesName = $salesmen->firstWhere('nik', $kode_sales)->name ?? $kode_sales; @endphp
                <div class="small">Salesman: {{ $salesName }}</div>
            @endif
            <div class="small">Metode Bayar: {{ strtoupper($jenis_bayar) }}</div>
            <div class="small text-muted">Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</div>
            <hr>
        </div>

        {{-- TABLES --}}
        @if ($items->isEmpty())
            <div class="text-center py-5">
                <p>Tidak ada data setoran pembayaran yang ditemukan.</p>
            </div>
        @else
            <table class="table table-sm align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th width="40" class="text-center">No</th>
                        <th>Tanggal</th>
                        <th>No Bukti</th>
                        <th>No Faktur</th>
                        <th>Pelanggan</th>
                        <th>Salesman</th>
                        <th>Tipe Pembayaran</th>
                        <th>Keterangan</th>
                        <th class="text-end">Jumlah Setor</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $num = 1;
                        $totJumlah = 0;
                    @endphp
                    @foreach ($items as $payment)
                        @php
                            $totJumlah += $payment->jumlah;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $num++ }}</td>
                            <td>{{ \Carbon\Carbon::parse($payment->tanggal)->format('d-m-Y') }}</td>
                            <td>{{ $payment->no_bukti }}</td>
                            <td>{{ $payment->no_faktur }}</td>
                            <td>{{ $payment->nama_pelanggan }}</td>
                            <td>{{ $payment->nama_sales ?? '-' }}</td>
                            <td>{{ $payment->tipe_pembayaran }}</td>
                            <td>{{ $payment->keterangan ?? '-' }}</td>
                            <td class="text-end fw-bold">{{ number_format($payment->jumlah, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="fw-bold">
                    <tr class="table-light">
                        <td colspan="8" class="text-end">TOTAL KESELURUHAN SETORAN:</td>
                        <td class="text-end">{{ number_format($totJumlah, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>


</body>
</html>
