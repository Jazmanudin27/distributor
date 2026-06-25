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
    <title>Cetak Laba Rugi Per Tanggal</title>
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

<body>
    <section>
        <header style="text-align: center; margin-bottom: 20px;">
            <h1 class="header-title">LAPORAN LABA RUGI PER TANGGAL</h1>
            <p style="margin: 0;">
                Periode: {{ tanggal_indo2($tanggal_dari) }} s/d {{ tanggal_indo2($tanggal_sampai) }}
            </p>
            <hr style="border: 1px solid #000; margin-top: 10px;">
        </header>

        <table>
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Tanggal</th>
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

                    $tanggalList = DB::table('penjualan')
                        ->select('tanggal')
                        ->whereBetween('tanggal', [$tanggal_dari, $tanggal_sampai])
                        ->groupBy('tanggal')
                        ->orderBy('tanggal')
                        ->get();
                @endphp

                @foreach ($tanggalList as $t)
                    @php
                        // Total Penjualan per Tanggal
                        $totalPenjualan = DB::table('penjualan_detail as d')
                            ->join('penjualan as p', 'p.no_faktur', '=', 'd.no_faktur')
                            ->where('p.tanggal', $t->tanggal)
                            ->where('d.is_promo', 0)
                            ->sum(DB::raw('(d.qty * d.harga) - d.total_diskon'));

                        // Retur Pembelian per Tanggal (calculated as HPP of BS Sales Returns)
                        $returPembelian = DB::table('retur_penjualan_detail as rpd')
                            ->join('retur_penjualan as rp', 'rp.no_retur', '=', 'rpd.no_retur')
                            ->join('barang_satuan as bs', 'bs.id', '=', 'rpd.id_satuan')
                            ->join('barang as b', 'b.kode_barang', '=', 'bs.kode_barang')
                            ->where('rp.tanggal', $t->tanggal)
                            ->where('rpd.kondisi', 'bs')
                            ->sum(DB::raw('rpd.qty * bs.harga_pokok'));

                        // Retur Penjualan per Tanggal
                        $returPenjualan = DB::table('retur_penjualan_detail as rpd')
                            ->join('barang_satuan as bs', 'bs.id', '=', 'rpd.id_satuan')
                            ->join('barang as b', 'b.kode_barang', '=', 'bs.kode_barang')
                            ->join('retur_penjualan as rp', 'rp.no_retur', '=', 'rpd.no_retur')
                            ->where('rp.tanggal', $t->tanggal)
                            ->sum(DB::raw('rpd.qty * bs.harga_jual'));

                        // HPP per Tanggal
                        $total_hpp = DB::table('penjualan_detail as d')
                            ->join('penjualan as p', 'p.no_faktur', '=', 'd.no_faktur')
                            ->where('p.tanggal', $t->tanggal)
                            ->where('d.is_promo', 0)
                            ->sum(DB::raw('d.harga_pokok * d.qty'));

                        // Retur Pembelian per Tanggal
                        // $returPembelian = DB::table('retur_pembelian_detail as rd')
                        //     ->join('retur_pembelian as r', 'r.no_retur', '=', 'rd.no_retur')
                        //     ->where('r.tanggal', $t->tanggal)
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

                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td class="text-center">{{ tanggal_indo2($t->tanggal) }}</td>
                        <td class="text-end">{{ formatAngka($totalPenjualan) }}</td>
                        <td class="text-end">{{ formatAngka($returPenjualan) }}</td>
                        <td class="text-end">{{ formatAngka($total_hpp) }}</td>
                        <td class="text-end">{{ formatAngka($returPembelian) }}</td>
                        <td class="text-end">{{ formatAngka($laba) }}</td>
                    </tr>
                @endforeach

                <tr class="highlight">
                    <td colspan="2" class="text-center">TOTAL</td>
                    <td class="text-end">{{ formatAngka($grandPenjualan) }}</td>
                    <td class="text-end">{{ formatAngka($grandReturPenjualan) }}</td>
                    <td class="text-end">{{ formatAngka($grandHpp) }}</td>
                    <td class="text-end">{{ formatAngka($grandReturPembelian) }}</td>
                    <td class="text-end">{{ formatAngka($grandLaba) }}</td>
                </tr>
            </tbody>
        </table>
    </section>
</body>

</html>
