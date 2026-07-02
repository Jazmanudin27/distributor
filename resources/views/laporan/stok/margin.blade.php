<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Margin Barang</title>
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
            padding: 5px 6px !important;
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

        tr.hover-row:hover td {
            background: #f4faff !important;
        }

        tfoot td {
            font-weight: bold;
            background: #eaf1f8 !important;
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
        <div class="text-center mb-3">
            <h4 class="fw-bold mb-1">LAPORAN STOK &amp; MARGIN BARANG</h4>
            <div class="">
                Supplier:
                <strong>
                    @if ($kode_supplier)
                        @php $suppObj = $suppliers->firstWhere('kode_supplier', $kode_supplier); @endphp
                        {{ $suppObj ? $suppObj->nama_supplier : $kode_supplier }}
                    @else
                        Semua
                    @endif
                </strong>
                &nbsp;|&nbsp; Kategori: <strong>{{ $kategori ?? 'Semua' }}</strong>
                &nbsp;|&nbsp; Merk: <strong>{{ $merk ?? 'Semua' }}</strong>
            </div>
            <div class="text-muted small">Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</div>
            <hr style="border-top: 1px dashed #000; opacity: 1;">
        </div>

        {{-- TABEL --}}
        <div style="font-family: 'Inter', sans-serif; font-size: 13px; color: #000; margin: 5px;">
            <table class="table table-sm align-middle w-100">
                <thead>
                    <tr class="table-light text-center">
                        <th width="30">No</th>
                        <th width="80">Kode Barang</th>
                        <th width="80">Kode Item</th>
                        <th>Nama Barang</th>
                        <th width="80">Kategori</th>
                        <th width="80">Merk</th>
                        <th width="80">Stok Fisik</th>
                        <th width="50">Satuan</th>
                        <th width="90">Harga Pokok</th>
                        <th width="90">Harga Jual</th>
                        <th width="90">Margin (Rp)</th>
                        <th width="60">Margin (%)</th>
                        <th width="100">Total Pokok</th>
                        <th width="100">Total Jual</th>
                        <th width="100">Total Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grandTotalPokok = 0;
                        $grandTotalJual = 0;
                        $grandTotalMargin = 0;
                    @endphp
                    @forelse ($items as $index => $item)
                        @php
                            $grandTotalPokok += $item['total_pokok'];
                            $grandTotalJual += $item['total_jual'];
                            $grandTotalMargin += $item['total_margin'];
                        @endphp
                        <tr class="hover-row">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="font-monospace text-secondary text-center">{{ $item['kode_barang'] }}</td>
                            <td class="font-monospace text-center">{{ $item['kode_item'] ?? '-' }}</td>
                            <td class="fw-bold">{{ $item['nama_barang'] }}</td>
                            <td class="text-center">{{ $item['kategori'] ?? '-' }}</td>
                            <td class="text-center">{{ $item['merk'] ?? '-' }}</td>
                            <td class="text-end font-monospace fw-semibold">
                                {{ number_format($item['stok'], $item['stok'] == (int) $item['stok'] ? 0 : 2, ',', '.') }}
                            </td>
                            <td class="text-center">{{ $item['satuan'] }}</td>
                            <td class="text-end font-monospace">Rp
                                {{ number_format($item['harga_pokok'], 0, ',', '.') }}</td>
                            <td class="text-end font-monospace">Rp
                                {{ number_format($item['harga_jual'], 0, ',', '.') }}</td>
                            <td class="text-end font-monospace text-success fw-semibold">
                                Rp {{ number_format($item['margin_rp'], 0, ',', '.') }}
                            </td>
                            <td class="text-center font-monospace text-success fw-bold">
                                {{ number_format($item['margin_persen'], 1, ',', '.') }}%
                            </td>
                            <td class="text-end font-monospace">Rp
                                {{ number_format($item['total_pokok'], 0, ',', '.') }}</td>
                            <td class="text-end font-monospace">Rp
                                {{ number_format($item['total_jual'], 0, ',', '.') }}</td>
                            <td class="text-end font-monospace fw-bold text-success">
                                Rp {{ number_format($item['total_margin'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="text-center py-4 text-muted">
                                Tidak ada data barang untuk ditampilkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($items->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="12" class="text-end fw-bold">TOTAL NILAI:</td>
                            <td class="text-end font-monospace text-dark">Rp
                                {{ number_format($grandTotalPokok, 0, ',', '.') }}</td>
                            <td class="text-end font-monospace text-dark">Rp
                                {{ number_format($grandTotalJual, 0, ',', '.') }}</td>
                            <td class="text-end font-monospace text-success">Rp
                                {{ number_format($grandTotalMargin, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="15" class="text-center fw-bold text-muted py-2"
                                style="background-color: #f8fafc !important;">
                                &mdash; Akhir Laporan &mdash;
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</body>

</html>
