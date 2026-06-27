@php
    if (!function_exists('tanggal_indo2')) {
        function tanggal_indo2($date)
        {
            if (!$date) {
                return '';
            }
            return \Carbon\Carbon::parse($date)->format('d-M-Y');
        }
    }

    if (!function_exists('formatAngka')) {
        function formatAngka($val, $decimals = 0)
        {
            return number_format((float) $val, $decimals, ',', '.');
        }
    }
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Laba Rugi Per Supplier</title>
    <style>
        body {
            font-family: 'Inter', Tahoma, sans-serif;
            font-size: 13px;
            margin: 15px;
            line-height: 1.3;
            color: #333;
            width: 210mm;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: auto;
            margin-top: 10px;
        }

        th {
            background-color: #2c3e50 !important;
            color: #ffffff !important;
            border: 1px solid #1a252f !important;
            padding: 8px 10px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
        }

        td {
            border: 1px solid #dee2e6;
            padding: 6px 8px;
            white-space: nowrap;
            overflow: hidden;
        }

        tr:nth-child(even) td {
            background-color: #f8f9fa;
        }

        tr:hover td {
            background-color: #e9ecef;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .header-title {
            font-weight: 700;
            font-size: 24px;
            text-align: center;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .highlight td {
            font-weight: bold;
            background-color: #e9ecef !important;
            border-top: 2px solid #2c3e50 !important;
            border-bottom: 3px double #2c3e50 !important;
        }

        @media print {
            body {
                margin: 0;
            }
            th {
                background-color: #2c3e50 !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .highlight td {
                background-color: #e9ecef !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body class="A4">
    <section class="sheet">
        <header style="text-align: center; margin-bottom: 25px;">
            <h1 class="header-title">LAPORAN LABA RUGI PER SUPPLIER</h1>
            <p style="margin: 0; font-size: 14px; color: #7f8c8d; font-weight: 500;">
                Periode: {{ tanggal_indo2($tanggal_dari) }}
                s/d {{ tanggal_indo2($tanggal_sampai) }}
            </p>
            <hr style="border: 0; border-top: 2px solid #2c3e50; margin-top: 15px; margin-bottom: 0;">
        </header>

        <table>
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Kode Supplier</th>
                    <th>Nama Supplier</th>
                    <th class="text-end">Penjualan Bruto (Rp)</th>
                    <th class="text-end">Diskon Penjualan (Rp)</th>
                    <th class="text-end">Penjualan Net (Rp)</th>
                    <th class="text-end">Retur Penjualan (Rp)</th>
                    <th class="text-end">Total HPP (Rp)</th>
                    <th class="text-end">Retur Pembelian (Rp)</th>
                    <th class="text-end">Laba Kotor (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                    $grandPenjualanBruto = 0;
                    $grandDiskonPenjualan = 0;
                    $grandPenjualan = 0;
                    $grandReturPenjualan = 0;
                    $grandReturPembelian = 0;
                    $grandHpp = 0;
                    $grandLaba = 0;
                @endphp
                @foreach ($data as $d)
                    @php
                        // Penjualan per Supplier (Bruto & Diskon)
                        $salesData = DB::table('penjualan_detail as d')
                            ->join('barang as b', 'b.kode_barang', '=', 'd.kode_barang')
                            ->join('penjualan as p', 'p.no_faktur', '=', 'd.no_faktur')
                            ->whereBetween('p.tanggal', [$tanggal_dari, $tanggal_sampai])
                            ->where('b.kode_supplier', $d->kode_supplier)
                            ->where('d.is_promo', 0)
                            ->where('p.batal', 0)
                            ->selectRaw('SUM(d.qty * d.harga) as bruto, SUM(d.total_diskon) as diskon')
                            ->first();

                        $totalPenjualanBruto = (float) ($salesData->bruto ?? 0);
                        $diskonPenjualan = (float) ($salesData->diskon ?? 0);
                        $totalPenjualan = $totalPenjualanBruto - $diskonPenjualan;

                        // Retur Penjualan — net setelah diskon (sama dengan retur_penjualan.total)
                        $returPenjualan = DB::table('retur_penjualan_detail as rpd')
                            ->join('retur_penjualan as rp', 'rp.no_retur', '=', 'rpd.no_retur')
                            ->join('barang as b', 'b.kode_barang', '=', 'rpd.kode_barang')
                            ->whereBetween('rp.tanggal', [$tanggal_dari, $tanggal_sampai])
                            ->where('b.kode_supplier', $d->kode_supplier)
                            ->sum(DB::raw('rpd.subtotal_retur - COALESCE(rpd.total_diskon_rupiah, 0)'));

                        // Retur Pembelian (HPP dari barang yang diretur — filter supplier via kode_barang langsung)
                        $returPembelian = DB::table('retur_penjualan_detail as rpd')
                            ->join('retur_penjualan as rp', 'rp.no_retur', '=', 'rpd.no_retur')
                            ->join('barang as b', 'b.kode_barang', '=', 'rpd.kode_barang')
                            ->leftJoin('barang_satuan as bs', 'bs.id', '=', 'rpd.id_satuan')
                            ->whereBetween('rp.tanggal', [$tanggal_dari, $tanggal_sampai])
                            ->where('b.kode_supplier', $d->kode_supplier)
                            ->sum(DB::raw('rpd.qty * COALESCE(bs.harga_pokok, 0)'));

                        // HPP per Supplier
                        $total_hpp = DB::table('penjualan_detail as d')
                            ->join('barang as b', 'b.kode_barang', '=', 'd.kode_barang')
                            ->join('penjualan as p', 'p.no_faktur', '=', 'd.no_faktur')
                            ->whereBetween('p.tanggal', [$tanggal_dari, $tanggal_sampai])
                            ->where('b.kode_supplier', $d->kode_supplier)
                            ->where('d.is_promo', 0)
                            ->where('p.batal', 0)
                            ->sum(DB::raw('d.harga_pokok * d.qty'));

                        // Hitung bersih
                        $penjualanBersih = $totalPenjualan - $returPenjualan;
                        $hppBersih = $total_hpp - $returPembelian;
                        $laba = $penjualanBersih - $hppBersih;

                        // Akumulasi
                        $grandPenjualanBruto += $totalPenjualanBruto;
                        $grandDiskonPenjualan += $diskonPenjualan;
                        $grandPenjualan += $totalPenjualan;
                        $grandReturPenjualan += $returPenjualan;
                        $grandReturPembelian += $returPembelian;
                        $grandHpp += $total_hpp;
                        $grandLaba += $laba;
                    @endphp

                    @if ($totalPenjualanBruto > 0 || $returPenjualan > 0)
                        <tr style="cursor:pointer" onclick="submitLabaRugi('{{ $d->kode_supplier }}')">
                            <td class="text-center">{{ $no++ }}</td>
                            <td class="text-center">{{ $d->kode_supplier }}</td>
                            <td>{{ $d->nama_supplier }}</td>
                            <td class="text-end">{{ formatAngka($totalPenjualanBruto) }}</td>
                            <td class="text-end">{{ formatAngka($diskonPenjualan) }}</td>
                            <td class="text-end">{{ formatAngka($totalPenjualan) }}</td>
                            <td class="text-end">{{ formatAngka($returPenjualan) }}</td>
                            <td class="text-end">{{ formatAngka($total_hpp) }}</td>
                            <td class="text-end">{{ formatAngka($returPembelian) }}</td>
                            <td class="text-end">{{ formatAngka($laba) }}</td>
                        </tr>
                    @endif
                @endforeach

                <form id="form-kartu-stok" action="{{ route('cetakLabaRugi') }}" method="POST" target="_blank"
                    style="display:none;">
                    @csrf
                    <input type="hidden" name="tanggal_awal" value="{{ $tanggal_dari }}">
                    <input type="hidden" name="tanggal_akhir" value="{{ $tanggal_sampai }}">
                    <input type="hidden" name="bulan" value="{{ request('bulan') }}">
                    <input type="hidden" name="tahun" value="{{ request('tahun') }}">
                    <input type="hidden" name="kode_supplier" id="input-supplier">
                </form>
                <tr class="highlight">
                    <td colspan="3" class="text-center">TOTAL</td>
                    <td class="text-end">{{ formatAngka($grandPenjualanBruto) }}</td>
                    <td class="text-end">{{ formatAngka($grandDiskonPenjualan) }}</td>
                    <td class="text-end">{{ formatAngka($grandPenjualan) }}</td>
                    <td class="text-end">{{ formatAngka($grandReturPenjualan) }}</td>
                    <td class="text-end">{{ formatAngka($grandHpp) }}</td>
                    <td class="text-end">{{ formatAngka($grandReturPembelian) }}</td>
                    <td class="text-end">{{ formatAngka($grandLaba) }}</td>
                </tr>
            </tbody>
        </table>
    </section>

    <script>
        function submitLabaRugi(kodeSupplier) {
            document.getElementById('input-supplier').value = kodeSupplier;
            document.getElementById('form-kartu-stok').submit();
        }
    </script>
</body>

</html>
