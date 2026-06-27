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
    <title>Cetak Laba Rugi Detail</title>
    <style>
        body {
            font-family: 'Inter', Tahoma, sans-serif;
            font-size: 12px;
            margin: 15px;
            line-height: 1.3;
            color: #333;
            width: 297mm;
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
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        td {
            border: 1px solid #dee2e6;
            padding: 6px 8px;
            white-space: nowrap;
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

<body>
    <section>
        <header style="text-align: center; margin-bottom: 25px;">
            <h1 class="header-title">LAPORAN LABA RUGI DETAIL</h1>
            <p style="margin: 0; font-size: 14px; color: #7f8c8d; font-weight: 500;">
                Periode: {{ tanggal_indo2($tanggal_dari) }} s/d {{ tanggal_indo2($tanggal_sampai) }}
            </p>
            <hr style="border: 0; border-top: 2px solid #2c3e50; margin-top: 15px; margin-bottom: 0;">
        </header>

        <table>
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Tanggal</th>
                    <th class="text-center">No Faktur</th>
                    <th class="text-center">Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Supplier</th>
                    <th class="text-center">Satuan</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Harga Jual (Rp)</th>
                    <th class="text-end">Subtotal (Rp)</th>
                    <th class="text-end">Diskon (Rp)</th>
                    <th class="text-end">Penjualan Net (Rp)</th>
                    <th class="text-end">HPP (Rp)</th>
                    <th class="text-end">Laba (Rp)</th>
                    <th class="text-end">Laba (%)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                    $grandSubtotal = 0;
                    $grandDiskon = 0;
                    $grandPenjualan = 0;
                    $grandHpp = 0;
                    $grandLaba = 0;

                    $detail = DB::table('penjualan_detail as d')
                        ->join('penjualan as p', 'p.no_faktur', '=', 'd.no_faktur')
                        ->join('barang as b', 'b.kode_barang', '=', 'd.kode_barang')
                        ->leftJoin('barang_satuan as s', 's.id', '=', 'd.satuan_id')
                        ->leftJoin('supplier as sup', 'sup.kode_supplier', '=', 'b.kode_supplier')
                        ->whereBetween('p.tanggal', [$tanggal_dari, $tanggal_sampai])
                        ->where('p.batal', 0) // hanya ambil penjualan yang tidak batal
                        ->where('d.is_promo', 0) // exclude promo
                        ->when(request('kode_supplier') ?: request('supplier'), function ($query) {
                            $query->where('b.kode_supplier', request('kode_supplier') ?: request('supplier'));
                        })
                        ->select(
                            'p.tanggal',
                            'p.no_faktur',
                            'b.kode_barang',
                            'b.nama_barang',
                            's.satuan',
                            'd.qty',
                            'd.harga',
                            'd.harga_pokok',
                            'd.is_promo',
                            'b.kode_supplier',
                            'sup.nama_supplier',
                            DB::raw('(d.qty * d.harga) as subtotal'),
                            'd.total_diskon as diskon',
                            DB::raw('(d.qty * d.harga) - d.total_diskon as total_penjualan'),
                            DB::raw('(d.qty * d.harga_pokok) as total_hpp'),
                            DB::raw('((d.qty * d.harga) - d.total_diskon) - (d.qty * d.harga_pokok) as laba'),
                        )
                        ->orderBy('p.tanggal')
                        ->orderBy('p.no_faktur')
                        ->get();
                @endphp

                @foreach ($detail as $row)
                    @php
                        $grandSubtotal += $row->subtotal;
                        $grandDiskon += $row->diskon;
                        $grandPenjualan += $row->total_penjualan;

                        // kalau promo → HPP dianggap 0
                        $rowHpp = $row->is_promo == 1 ? 0 : $row->total_hpp;
                        $grandHpp += $rowHpp;

                        // kalau laba negatif → 0
                        $rowLaba = $row->is_promo == 1 ? 0 : $row->laba;
                        $grandLaba += $rowLaba;

                        $persenLaba = $row->total_penjualan > 0 ? ($rowLaba / $row->total_penjualan) * 100 : 0;
                    @endphp
                    <tr @if ($row->is_promo == 1) style="background-color: orange; color: #000;" @endif>
                        <td class="text-center">{{ $no++ }}</td>
                        <td class="text-center">{{ tanggal_indo2($row->tanggal) }}</td>
                        <td class="text-center">{{ $row->no_faktur }}</td>
                        <td class="text-center">{{ $row->kode_barang }}</td>
                        <td>{{ $row->nama_barang }}</td>
                        <td>{{ $row->nama_supplier }}</td>
                        <td class="text-center">{{ $row->satuan }}</td>
                        <td class="text-end">{{ formatAngka($row->qty) }}</td>
                        <td class="text-end">{{ formatAngka($row->harga) }}</td>
                        <td class="text-end">{{ formatAngka($row->subtotal) }}</td>
                        <td class="text-end">{{ formatAngka($row->diskon) }}</td>
                        <td class="text-end">{{ formatAngka($row->total_penjualan) }}</td>
                        <td class="text-end">{{ formatAngka($rowHpp) }}</td>
                        <td class="text-end">{{ formatAngka($rowLaba) }}</td>
                        <td class="text-end">{{ formatAngka($persenLaba, 2) }}%</td>
                    </tr>
                @endforeach

                @php
                    $persenGrand = $grandPenjualan > 0 ? ($grandLaba / $grandPenjualan) * 100 : 0;
                @endphp
                <tr class="highlight">
                    <td colspan="9" class="text-center">TOTAL</td>
                    <td class="text-end">{{ formatAngka($grandSubtotal) }}</td>
                    <td class="text-end">{{ formatAngka($grandDiskon) }}</td>
                    <td class="text-end">{{ formatAngka($grandPenjualan) }}</td>
                    <td class="text-end">{{ formatAngka($grandHpp) }}</td>
                    <td class="text-end">{{ formatAngka($grandLaba) }}</td>
                    <td class="text-end">{{ formatAngka($persenGrand, 2) }}%</td>
                </tr>
            </tbody>
        </table>
    </section>

</body>

</html>
