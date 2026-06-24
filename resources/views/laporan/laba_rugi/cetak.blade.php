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
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
        .supplier-row:hover {
            background-color: #f8f9fa !important;
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
    <div class="container-fluid py-3" style="max-width: {{ $jenis_laporan === 'rekap' ? '700px' : '1200px' }};">
        {{-- HEADER --}}
        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1">
                @if($jenis_laporan === 'rekap')
                    LAPORAN LABA RUGI (REKAP)
                @elseif($jenis_laporan === 'per_supplier')
                    LAPORAN LABA RUGI PER SUPPLIER
                @elseif($jenis_laporan === 'per_tanggal_supplier')
                    LAPORAN LABA RUGI PER TANGGAL & PER SUPPLIER
                @elseif($jenis_laporan === 'detail')
                    LAPORAN LABA RUGI DETAIL
                @endif
            </h4>
            <div class="small">
                Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/Y') }}
                @if($kode_supplier && isset($suppliersList))
                    @php
                        $selectedSupplier = $suppliersList->firstWhere('kode_supplier', $kode_supplier);
                    @endphp
                    @if($selectedSupplier)
                        <br>Supplier: {{ $selectedSupplier->nama_supplier }} ({{ $kode_supplier }})
                    @endif
                @endif
            </div>
            <div class="small text-muted mt-1">Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</div>
            <hr>
        </div>

        {{-- CONTENT --}}
        @if($jenis_laporan === 'rekap')
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
                    <tr><td colspan="3" style="border: none !important; height: 15px;"></td></tr>

                    <!-- 3. RETUR PEMBELIAN -->
                    <tr class="section-header">
                        <td colspan="2">III. RETUR PEMBELIAN</td>
                        <td class="text-end"></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="indent-1">Retur Pembelian (Purchase Returns)</td>
                        <td class="text-end text-success">{{ number_format($purchaseReturn, 0, ',', '.') }}</td>
                    </tr>

                    <!-- Spacer -->
                    <tr><td colspan="3" style="border: none !important; height: 20px;"></td></tr>

                    <!-- 4. LABA KOTOR -->
                    <tr class="fw-bold table-light" style="font-size: 12px;">
                        <td colspan="2" style="font-size: 12px;">IV. LABA KOTOR (GROSS PROFIT)</td>
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

        @elseif($jenis_laporan === 'per_supplier')
            @if(!isset($isExcel) || !$isExcel)
                <div class="alert alert-info py-2 px-3 mb-3 no-print d-flex align-items-center gap-2" style="font-size: 11px;">
                    <i class="fa-solid fa-circle-info text-primary"></i>
                    <span>Tips: Klik pada baris supplier untuk melihat detail transaksi barang supplier tersebut.</span>
                </div>
            @endif
            <table class="table table-sm table-hover align-middle w-100">
                <thead>
                    <tr class="table-light text-center font-weight-bold">
                        <th width="40">No</th>
                        <th width="120">Kode Supplier</th>
                        <th>Nama Supplier</th>
                        <th width="140" class="text-end">Jumlah Penjualan</th>
                        <th width="140" class="text-end">Retur Penjualan</th>
                        <th width="140" class="text-end">Total HPP</th>
                        <th width="140" class="text-end">Retur Pembelian</th>
                        <th width="140" class="text-end">Laba Kotor</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalSales = 0;
                        $totalReturn = 0;
                        $totalHpp = 0;
                        $totalPurchaseReturn = 0;
                        $totalProfit = 0;
                    @endphp
                    @forelse($data as $index => $row)
                        @php
                            $totalSales += $row['jumlah_penjualan'];
                            $totalReturn += $row['retur_penjualan'];
                            $totalHpp += $row['total_hpp'];
                            $totalPurchaseReturn += $row['retur_pembelian'];
                            $totalProfit += $row['laba_kotor'];
                        @endphp
                        <tr class="supplier-row" 
                            @if(!isset($isExcel) || !$isExcel)
                                style="cursor: pointer;" 
                                onclick="drillDown('{{ $row['kode_supplier'] }}')"
                                title="Klik untuk melihat detail"
                            @endif>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center font-monospace">{{ $row['kode_supplier'] }}</td>
                            <td>{{ $row['nama_supplier'] }}</td>
                            <td class="text-end">{{ number_format($row['jumlah_penjualan'], 0, ',', '.') }}</td>
                            <td class="text-end text-danger">{{ $row['retur_penjualan'] > 0 ? '(' . number_format($row['retur_penjualan'], 0, ',', '.') . ')' : '0' }}</td>
                            <td class="text-end">{{ number_format($row['total_hpp'], 0, ',', '.') }}</td>
                            <td class="text-end text-success">{{ number_format($row['retur_pembelian'], 0, ',', '.') }}</td>
                            <td class="text-end fw-bold {{ $row['laba_kotor'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                {{ $row['laba_kotor'] < 0 ? '(' . number_format(abs($row['laba_kotor']), 0, ',', '.') . ')' : number_format($row['laba_kotor'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-3 text-muted">Tidak ada data untuk periode ini</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($data) > 0)
                <tfoot>
                    <tr class="fw-bold table-light text-end">
                        <td colspan="3" class="text-center">TOTAL</td>
                        <td>{{ number_format($totalSales, 0, ',', '.') }}</td>
                        <td class="text-danger">{{ $totalReturn > 0 ? '(' . number_format($totalReturn, 0, ',', '.') . ')' : '0' }}</td>
                        <td>{{ number_format($totalHpp, 0, ',', '.') }}</td>
                        <td class="text-success">{{ number_format($totalPurchaseReturn, 0, ',', '.') }}</td>
                        <td class="{{ $totalProfit >= 0 ? 'text-primary' : 'text-danger' }}">{{ $totalProfit < 0 ? '(' . number_format(abs($totalProfit), 0, ',', '.') . ')' : number_format($totalProfit, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>

        @elseif($jenis_laporan === 'per_tanggal_supplier')
            <table class="table table-sm align-middle w-100">
                <thead>
                    <tr class="table-light text-center font-weight-bold">
                        <th width="40">No</th>
                        <th width="90">Tanggal</th>
                        <th width="120">Kode Supplier</th>
                        <th>Nama Supplier</th>
                        <th width="140" class="text-end">Jumlah Penjualan</th>
                        <th width="140" class="text-end">Retur Penjualan</th>
                        <th width="140" class="text-end">Total HPP</th>
                        <th width="140" class="text-end">Retur Pembelian</th>
                        <th width="140" class="text-end">Laba Kotor</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalSales = 0;
                        $totalReturn = 0;
                        $totalHpp = 0;
                        $totalPurchaseReturn = 0;
                        $totalProfit = 0;
                    @endphp
                    @forelse($data as $index => $row)
                        @php
                            $totalSales += $row['jumlah_penjualan'];
                            $totalReturn += $row['retur_penjualan'];
                            $totalHpp += $row['total_hpp'];
                            $totalPurchaseReturn += $row['retur_pembelian'];
                            $totalProfit += $row['laba_kotor'];
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                            <td class="text-center font-monospace">{{ $row['kode_supplier'] }}</td>
                            <td>{{ $row['nama_supplier'] }}</td>
                            <td class="text-end">{{ number_format($row['jumlah_penjualan'], 0, ',', '.') }}</td>
                            <td class="text-end text-danger">{{ $row['retur_penjualan'] > 0 ? '(' . number_format($row['retur_penjualan'], 0, ',', '.') . ')' : '0' }}</td>
                            <td class="text-end">{{ number_format($row['total_hpp'], 0, ',', '.') }}</td>
                            <td class="text-end text-success">{{ number_format($row['retur_pembelian'], 0, ',', '.') }}</td>
                            <td class="text-end fw-bold {{ $row['laba_kotor'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                {{ $row['laba_kotor'] < 0 ? '(' . number_format(abs($row['laba_kotor']), 0, ',', '.') . ')' : number_format($row['laba_kotor'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-3 text-muted">Tidak ada data untuk periode ini</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($data) > 0)
                <tfoot>
                    <tr class="fw-bold table-light text-end">
                        <td colspan="4" class="text-center">TOTAL</td>
                        <td>{{ number_format($totalSales, 0, ',', '.') }}</td>
                        <td class="text-danger">{{ $totalReturn > 0 ? '(' . number_format($totalReturn, 0, ',', '.') . ')' : '0' }}</td>
                        <td>{{ number_format($totalHpp, 0, ',', '.') }}</td>
                        <td class="text-success">{{ number_format($totalPurchaseReturn, 0, ',', '.') }}</td>
                        <td class="{{ $totalProfit >= 0 ? 'text-primary' : 'text-danger' }}">{{ $totalProfit < 0 ? '(' . number_format(abs($totalProfit), 0, ',', '.') . ')' : number_format($totalProfit, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>

        @elseif($jenis_laporan === 'detail')
            <table class="table table-sm align-middle w-100">
                <thead>
                    <tr class="table-light text-center font-weight-bold">
                        <th width="30">No</th>
                        <th width="80">Tanggal</th>
                        <th width="80">Tipe</th>
                        <th width="100">No Transaksi</th>
                        <th width="90">Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Supplier</th>
                        <th width="50" class="text-center">Qty</th>
                        <th width="100" class="text-end">Harga Satuan</th>
                        <th width="120" class="text-end">Total Jual</th>
                        <th width="120" class="text-end">Total HPP</th>
                        <th width="120" class="text-end">Laba Kotor</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalQty = 0;
                        $totalJual = 0;
                        $totalHpp = 0;
                        $totalProfit = 0;
                    @endphp
                    @forelse($data as $index => $row)
                        @php
                            $totalQty += $row['qty'];
                            $totalJual += $row['total_jual'];
                            $totalHpp += $row['total_hpp'];
                            $totalProfit += $row['laba_kotor'];
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                            <td class="text-center">
                                @if($row['tipe'] === 'Penjualan')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5" style="font-size: 10px;">Jual</span>
                                @elseif($row['tipe'] === 'Retur Jual')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5" style="font-size: 10px;">Retur Jual</span>
                                @elseif($row['tipe'] === 'Retur Beli')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 10px;">Retur Beli</span>
                                @else
                                    {{ $row['tipe'] }}
                                @endif
                            </td>
                            <td class="text-center font-monospace">{{ $row['no_transaksi'] }}</td>
                            <td class="text-center font-monospace">{{ $row['kode_barang'] }}</td>
                            <td>{{ $row['nama_barang'] }}</td>
                            <td>{{ $row['nama_supplier'] }}</td>
                            <td class="text-center">{{ number_format($row['qty'], 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row['harga'], 0, ',', '.') }}</td>
                            <td class="text-end {{ $row['total_jual'] < 0 ? 'text-danger' : '' }}">
                                {{ $row['total_jual'] < 0 ? '(' . number_format(abs($row['total_jual']), 0, ',', '.') . ')' : number_format($row['total_jual'], 0, ',', '.') }}
                            </td>
                            <td class="text-end {{ $row['total_hpp'] < 0 ? 'text-success' : '' }}">
                                {{ $row['total_hpp'] < 0 ? '(' . number_format(abs($row['total_hpp']), 0, ',', '.') . ')' : number_format($row['total_hpp'], 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold {{ $row['laba_kotor'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                {{ $row['laba_kotor'] < 0 ? '(' . number_format(abs($row['laba_kotor']), 0, ',', '.') . ')' : number_format($row['laba_kotor'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-3 text-muted">Tidak ada data untuk periode ini</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($data) > 0)
                <tfoot>
                    <tr class="fw-bold table-light text-end">
                        <td colspan="7" class="text-center">TOTAL</td>
                        <td class="text-center">{{ number_format($totalQty, 0, ',', '.') }}</td>
                        <td></td>
                        <td class="{{ $totalJual < 0 ? 'text-danger' : '' }}">
                            {{ $totalJual < 0 ? '(' . number_format(abs($totalJual), 0, ',', '.') . ')' : number_format($totalJual, 0, ',', '.') }}
                        </td>
                        <td class="{{ $totalHpp < 0 ? 'text-success' : '' }}">
                            {{ $totalHpp < 0 ? '(' . number_format(abs($totalHpp), 0, ',', '.') . ')' : number_format($totalHpp, 0, ',', '.') }}
                        </td>
                        <td class="{{ $totalProfit >= 0 ? 'text-primary' : 'text-danger' }}">
                            {{ $totalProfit < 0 ? '(' . number_format(abs($totalProfit), 0, ',', '.') . ')' : number_format($totalProfit, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        @endif

        {{-- FOOTER TANDA TANGAN --}}
        <div class="row mt-5 text-center no-print">
            <div class="col-6 offset-6">
                <p class="mb-5">Disetujui Oleh,</p>
                <br><br>
                <p class="fw-bold mb-0">______________________</p>
                <p class="text-muted small">Owner / Pimpinan</p>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    @if(!isset($isExcel) || !$isExcel)
        <script>
            function drillDown(kodeSupplier) {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('jenis_laporan', 'detail');
                urlParams.set('kode_supplier', kodeSupplier);
                window.open(window.location.pathname + '?' + urlParams.toString(), '_blank');
            }

            window.onload = function() {
                window.print();
            }
        </script>
    @endif
</body>
</html>
