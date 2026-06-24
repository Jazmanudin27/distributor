<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Buku Kas & Bank</title>
    <!-- Bootstrap CSS -->
    @if(!isset($isExcel))
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @endif
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
            padding: 5px 8px !important;
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
            <h4 class="fw-bold mb-1">LAPORAN BUKU KAS & BANK</h4>
            <div class="small">
                Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/Y') }}
            </div>
            @if($kode_bank)
                @php 
                    $selectedBank = $banks->firstWhere('id', $kode_bank); 
                @endphp
                @if($selectedBank)
                    <div class="small">Rekening: <strong>{{ $selectedBank->nama_bank }} - {{ $selectedBank->no_rekening }} ({{ $selectedBank->atas_nama }})</strong></div>
                @endif
            @else
                <div class="small">Rekening: <strong>Semua Rekening</strong></div>
            @endif
            <div class="small text-muted">Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</div>
            <hr>
        </div>

        {{-- TABLES --}}
        <table class="table table-sm align-middle w-100" style="border-collapse: collapse; width: 100%;">
            <thead class="table-light">
                <tr class="text-center">
                    <th width="40">No</th>
                    <th width="90">Tanggal</th>
                    <th width="180">Rekening Kas/Bank</th>
                    <th>Keterangan</th>
                    <th width="120" class="text-end">Debet (Masuk)</th>
                    <th width="120" class="text-end">Kredit (Keluar)</th>
                    <th width="140" class="text-end">Saldo Berjalan</th>
                </tr>
            </thead>
            <tbody>
                {{-- SALDO AWAL ROW --}}
                <tr>
                    <td class="text-center text-secondary small">-</td>
                    <td class="text-center">-</td>
                    <td>-</td>
                    <td class="fw-semibold">SALDO AWAL</td>
                    <td class="text-end">-</td>
                    <td class="text-end">-</td>
                    <td class="text-end fw-bold" style="background-color: #fafafa;">
                        Rp {{ number_format($saldoAwal, 0, ',', '.') }}
                    </td>
                </tr>

                @php
                    $num = 1;
                @endphp
                @forelse ($items as $item)
                    <tr>
                        <td class="text-center">{{ $num++ }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                        <td>
                            @if($item->bank)
                                {{ $item->bank->nama_bank }}
                                <div style="font-size: 9px; color: #555;">
                                    {{ $item->bank->no_rekening }} - {{ $item->bank->atas_nama }}
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $item->keterangan ?? '-' }}</td>
                        <td class="text-end {{ $item->tipe === 'debet' ? 'text-success fw-semibold' : '' }}">
                            {{ $item->tipe === 'debet' ? 'Rp ' . number_format($item->jumlah, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-end {{ $item->tipe === 'kredit' ? 'text-danger fw-semibold' : '' }}">
                            {{ $item->tipe === 'kredit' ? 'Rp ' . number_format($item->jumlah, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-end fw-bold">
                            Rp {{ number_format($item->saldo_berjalan, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    @if ($items->isEmpty())
                        {{-- No new mutations in this period --}}
                    @endif
                @endforelse
            </tbody>
            <tfoot class="fw-bold">
                <tr class="table-light">
                    <td colspan="4" class="text-end">MUTASI PERIODE INI & SALDO AKHIR:</td>
                    <td class="text-end text-success">Rp {{ number_format($totalDebet, 0, ',', '.') }}</td>
                    <td class="text-end text-danger">Rp {{ number_format($totalKredit, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold" style="background-color: #eef2f7;">
                        Rp {{ number_format($saldoAkhir, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if(!isset($isExcel))
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
    @endif
</body>
</html>
