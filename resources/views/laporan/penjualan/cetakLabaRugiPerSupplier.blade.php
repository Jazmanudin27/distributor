@php
if (!function_exists('tanggal_indo2')) {
    function tanggal_indo2($date) {
        if (!$date) return '';
        return \Carbon\Carbon::parse($date)->format('d-M-Y');
    }
}

if (!function_exists('formatAngka')) {
    function formatAngka($val, $decimals = 0) {
        return number_format((float)$val, $decimals, ',', '.');
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
            font-family: Tahoma, sans-serif;
            font-size: 14px;
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
            padding: 4px 6px;
            white-space: nowrap;
            overflow: hidden;
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
            font-weight: bold;
            font-size: 22px;
            text-align: center;
        }

        .highlight {
            font-weight: bold;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>

<body class="A4">
    <section class="sheet">
        <header style="text-align: center; margin-bottom: 20px;">
            <h1 class="header-title">LAPORAN LABA RUGI PER SUPPLIER</h1>
            <p style="margin: 0;">
                Periode: {{ tanggal_indo2($tanggal_dari) }}
                s/d {{ tanggal_indo2($tanggal_sampai) }}
            </p>
            <hr style="border: 1px solid #000; margin-top: 10px;">
        </header>

        <table>
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Kode Supplier</th>
                    <th>Nama Supplier</th>
                    <th class="text-end">Jumlah Penjualan (Rp)</th>
                    <th class="text-end">Retur Penjualan (Rp)</th>
                    <th class="text-end">Total HPP (Rp)</th>
                    <th class="text-end">Retur Pembelian (Rp)</th>
                    <th class="text-end">Laba Kotor (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                    $grandPenjualan = 0;
                    $grandReturPenjualan = 0;
                    $grandReturPembelian = 0;
                    $grandHpp = 0;
                    $grandLaba = 0;
                @endphp
                @foreach ($data as $d)
                    @php
                        // Penjualan per Supplier
                        $totalPenjualan = DB::table('penjualan_detail as d')
                            ->join('barang as b', 'b.kode_barang', '=', 'd.kode_barang')
                            ->join('penjualan as p', 'p.no_faktur', '=', 'd.no_faktur')
                            ->whereBetween('p.tanggal', [$tanggal_dari, $tanggal_sampai])
                            ->where('b.kode_supplier', $d->kode_supplier)
                            ->where('d.is_promo', 0)
                            ->where('p.batal', 0)
                            ->sum(DB::raw('(d.qty * d.harga) - d.total_diskon'));

                        // Retur Penjualan
                        // $returPenjualan = DB::table('retur_penjualan_detail as rd')
                        //     ->join('barang as b', 'b.kode_barang', '=', 'rd.kode_barang')
                        //     ->join('retur_penjualan as r', 'r.no_retur', '=', 'rd.no_retur')
                        //     ->whereBetween('r.tanggal', [$tanggal_dari, $tanggal_sampai])
                        //     ->where('b.kode_supplier', $d->kode_supplier)
                        //     ->sum(DB::raw('rd.subtotal_retur - rd.total_diskon_rupiah'));

                        // HPP
                        $total_hpp = DB::table('penjualan_detail as d')
                            ->join('barang as b', 'b.kode_barang', '=', 'd.kode_barang')
                            ->join('penjualan as p', 'p.no_faktur', '=', 'd.no_faktur')
                            ->whereBetween('p.tanggal', [$tanggal_dari, $tanggal_sampai])
                            ->where('b.kode_supplier', $d->kode_supplier)
                            ->where('d.is_promo', 0)
                            ->where('p.batal', 0)
                            ->sum(DB::raw('d.harga_pokok * d.qty'));

                        // Retur Pembelian
                        $returPembelian = DB::table('retur_pembelian_detail as rpd')
                            ->join('barang as b', 'b.kode_barang', '=', 'rpd.kode_barang')
                            ->join('retur_pembelian as rp', 'rp.no_retur', '=', 'rpd.no_retur')
                            ->whereBetween('rp.tanggal', [$tanggal_dari, $tanggal_sampai])
                            ->where('b.kode_supplier', $d->kode_supplier)
                            ->sum('rpd.subtotal_retur');

                        // Retur Penjualan
                        $returPenjualan = DB::table('retur_penjualan_detail as rpd')
                            ->join('barang as b', 'b.kode_barang', '=', 'rpd.kode_barang')
                            ->join('retur_penjualan as rp', 'rp.no_retur', '=', 'rpd.no_retur')
                            ->whereBetween('rp.tanggal', [$tanggal_dari, $tanggal_sampai])
                            ->where('b.kode_supplier', $d->kode_supplier)
                            ->sum(DB::raw('rpd.subtotal_retur - rpd.total_diskon_rupiah'));

                        // Retur Pembelian
                        // $returPembelian = DB::table('retur_pembelian_detail as rd')
                        //     ->join('barang as b', 'b.kode_barang', '=', 'rd.kode_barang')
                        //     ->join('retur_pembelian as r', 'r.no_retur', '=', 'rd.no_retur')
                        //     ->whereBetween('r.tanggal', [$tanggal_dari, $tanggal_sampai])
                        //     ->where('b.kode_supplier', $d->kode_supplier)
                        //     ->sum(DB::raw('rd.subtotal_retur'));

                        // Hitung bersih
                        $penjualanBersih = $totalPenjualan - $returPenjualan;
                        $hppBersih = $total_hpp - $returPembelian;
                        $laba = $penjualanBersih - $hppBersih;

                        // Akumulasi
                        $grandPenjualan += $totalPenjualan;
                        $grandReturPenjualan += $returPenjualan;
                        $grandReturPembelian += $returPembelian;
                        $grandHpp += $total_hpp;
                        $grandLaba += $laba;
                    @endphp

                    @if ($totalPenjualan > 0)
                        <tr style="cursor:pointer" onclick="submitLabaRugi('{{ $d->kode_supplier }}')">
                            <td class="text-center">{{ $no++ }}</td>
                            <td class="text-center">{{ $d->kode_supplier }}</td>
                            <td>{{ $d->nama_supplier }}</td>
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
