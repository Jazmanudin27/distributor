<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan DPB Kanvas - {{ $canvasSession->no_canvas }}</title>
    <style>
        body {
            font-family: Tahoma, sans-serif;
            font-size: 11px;
            margin: 0;
            line-height: 1.2;
            width: 210mm;
            padding: 10px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px 3px;
            white-space: nowrap;
            overflow: hidden;
        }

        .row-barang td {
            border: none !important;
            padding: 2px 3px;
            border-bottom: 1px dotted #000;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .header-table td {
            vertical-align: top;
            padding: 2px 4px;
            border: none;
        }

        .header-title {
            font-weight: bold;
            font-size: 15px;
        }

        .header-subtitle {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .header-address {
            font-size: 10px;
            color: #333;
        }

        .info-table td {
            padding: 1px 3px;
            border: none;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 6px;
            border-bottom: 1.5px solid #000;
            padding-bottom: 2px;
        }

        small {
            font-size: 9px;
        }
    </style>
</head>

<body>

    <table class="header-table" style="margin-bottom: 15px;">
        <tr>
            <td style="width: 50%;">
                <div style="display: flex; align-items: center; gap: 8px; padding-bottom: 8px;">
                    <img src="{{ asset('assets/img/MJAP.png') }}" alt="Logo MJAP" style="height: 50px;">
                    <div>
                        <div class="header-title">LAPORAN PENJUALAN KANVAS</div>
                        <div class="header-subtitle">CV MITRA JAYA ABADI PERSADA</div>
                    </div>
                </div>
                <div class="header-address">SIRNAGALIH INDIHIANG</div>
                <div class="header-address">TASIKMALAYA</div>
                <div class="header-address">Rek: CIMB NIAGA A.N NANDANG PRISTIWANTO</div>
                <div class="header-address">800184933300</div>
            </td>
            <td style="width: 50%;">
                <table class="info-table" style="float: right;">
                    <tr>
                        <td width="120">No. DPB</td>
                        <td>: <b>{{ $canvasSession->no_canvas }}</b></td>
                    </tr>
                    <tr>
                        <td>Tanggal Loading</td>
                        <td>: {{ \Carbon\Carbon::parse($canvasSession->tanggal)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td>Salesman</td>
                        <td>: <b>{{ strtoupper($canvasSession->sales->name ?? $canvasSession->kode_sales) }}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>: {{ $canvasSession->status === 'completed' ? 'SELESAI' : 'AKTIF / DI JALAN' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="section-title">I. REKAP MUTASI & REKONSILIASI BARANG</div>
    <table>
        <thead>
            <tr class="text-center">
                <th width="40">No</th>
                <th width="100">Kode Barang</th>
                <th>Nama Barang</th>
                <th width="85">Harga Satuan</th>
                <th width="100">Ambil (Loading)</th>
                <th width="100">Total Nilai</th>
                <th width="100">Terjual (Sales)</th>
                <th width="100">Kembali (Unload)</th>
                <th width="100">Selisih (Discrepancy)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalAmbil = 0;
                $totalTerjual = 0;
                $totalKembali = 0;
                $totalSelisih = 0;
                $grandTotalAmbil = 0;
            @endphp
            @foreach ($canvasSession->details as $index => $detail)
                @php
                    $totalAmbil += (float) $detail->qty_ambil;
                    $totalTerjual += (float) $detail->qty_terjual;
                    $totalKembali += (float) $detail->qty_kembali;
                    $totalSelisih += (float) $detail->selisih;

                    $isi = (float) ($detail->barangSatuan->isi ?? 1);
                    $qtyAmbilSmallest = (float) $detail->qty_ambil * $isi;
                    $qtyTerjualSmallest = (float) $detail->qty_terjual * $isi;
                    $qtyKembaliSmallest = (float) $detail->qty_kembali * $isi;
                    $qtySelisihSmallest = (float) $detail->selisih * $isi;

                    // Mengambil harga dari tabel satuan barang langsung
                    $price = $detail->barangSatuan->harga_jual ?? 0;
                    $subTotalAmbil = (float) $detail->qty_ambil * $price;
                    $grandTotalAmbil += $subTotalAmbil;
                @endphp
                <tr class="row-barang">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center font-monospace">{{ $detail->kode_barang }}</td>
                    <td>{{ $detail->barang->nama_barang }}{{ $detail->diskon_persen > 0 ? ' (Disc: ' . (float) $detail->diskon_persen . '%)' : '' }}
                    </td>
                    <td class="text-end">Rp {{ number_format($price, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold">
                        {{ str_replace(', ', ' ', $detail->barang->formatStok($qtyAmbilSmallest)) }}</td>
                    <td class="text-end fw-bold">Rp {{ number_format($subTotalAmbil, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold text-info">
                        {{ str_replace(', ', ' ', $detail->barang->formatStok($qtyTerjualSmallest)) }}</td>
                    <td class="text-end fw-bold text-success">
                        {{ str_replace(', ', ' ', $detail->barang->formatStok($qtyKembaliSmallest)) }}</td>
                    <td class="text-end fw-bold {{ $detail->selisih != 0 ? 'text-danger' : '' }}">
                        {{ str_replace(', ', ' ', $detail->barang->formatStok($qtySelisihSmallest)) }}</td>
                </tr>
            @endforeach
            <tr class="fw-bold" style="background-color: #f2f2f2;">
                <td colspan="5" class="text-end py-1.5">TOTAL NILAI MUATAN (LOADING):</td>
                <td class="text-end py-1.5" style="border-double: 3px double #000;">Rp {{ number_format($grandTotalAmbil, 0, ',', '.') }}</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">II. RINCIAN FAKTUR PENJUALAN YANG DIHASILKAN</div>
    <table>
        <thead>
            <tr class="text-center">
                <th width="30">No</th>
                <th width="90">No. Faktur</th>
                <th>Nama Pelanggan</th>
                <th>Nama Barang</th>
                <th width="50">Satuan</th>
                <th width="40">Jml</th>
                <th width="80">Harga</th>
                <th width="30">D1</th>
                <th width="30">D2</th>
                <th width="30">D3</th>
                <th width="90" class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalItemSales = 0;
                $totalGlobalDiscounts = 0;
                $rowNumber = 1;
            @endphp
            @forelse ($invoices as $inv)
                @php
                    $totalGlobalDiscounts += (float) $inv->diskon;
                @endphp
                @foreach ($inv->details as $det)
                    @php
                        $subTotalDet = $det->qty * $det->harga - $det->total_diskon;
                        $totalItemSales += $subTotalDet;
                    @endphp
                    <tr class="row-barang">
                        <td class="text-center">{{ $rowNumber++ }}</td>
                        <td class="text-center font-monospace">{{ $inv->no_faktur }}</td>
                        <td>{{ $inv->pelanggan->nama_pelanggan ?? '-' }}</td>
                        <td>{{ $det->barang->nama_barang ?? 'Barang Terhapus' }}</td>
                        <td class="text-center">{{ $det->barangSatuan->satuan ?? '-' }}</td>
                        <td class="text-center">{{ floatval($det->qty) }}</td>
                        <td class="text-end">Rp {{ number_format((float) $det->harga, 0, ',', '.') }}</td>
                        <td class="text-center">
                            {{ floatval($det->diskon1_persen) > 0 ? floatval($det->diskon1_persen) . '%' : '-' }}
                        </td>
                        <td class="text-center">
                            {{ floatval($det->diskon2_persen) > 0 ? floatval($det->diskon2_persen) . '%' : '-' }}
                        </td>
                        <td class="text-center">
                            {{ floatval($det->diskon3_persen) > 0 ? floatval($det->diskon3_persen) . '%' : '-' }}
                        </td>
                        <td class="text-end fw-bold">
                            Rp {{ number_format($subTotalDet, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="11" class="text-center text-muted" style="padding: 15px;">Belum ada faktur penjualan
                        tercatat pada sesi kanvas ini.</td>
                </tr>
            @endforelse
            @if ($invoices->count() > 0)
                <tr class="fw-bold" style="background-color: #fafafa; font-size: 11px;">
                    <td colspan="10" class="text-end py-1">TOTAL ITEM:</td>
                    <td class="text-end py-1">Rp {{ number_format($totalItemSales, 0, ',', '.') }}</td>
                </tr>
                @if ($totalGlobalDiscounts > 0)
                    <tr class="fw-bold" style="background-color: #fafafa; font-size: 11px;">
                        <td colspan="10" class="text-end py-1 text-danger">TOTAL POTONGAN FAKTUR:</td>
                        <td class="text-end py-1 text-danger">-Rp
                            {{ number_format($totalGlobalDiscounts, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="fw-bold" style="background-color: #f2f2f2; font-size: 12px;">
                    <td colspan="10" class="text-end py-1.5">GRAND TOTAL PENJUALAN KANVAS:</td>
                    <td class="text-end py-1.5" style="border-double: 3px double #000;">Rp
                        {{ number_format($totalItemSales - $totalGlobalDiscounts, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    @if ($canvasSession->keterangan)
        <table style="margin-top: 10px; width: 100%;">
            <tr>
                <td style="border: none; font-weight: bold; width: 10%;">Catatan:</td>
                <td style="border: none; font-style: italic;">{{ $canvasSession->keterangan }}</td>
            </tr>
        </table>
    @endif

    <table style="margin-top: 30px; width: 100%; border: none;">
        <tr style="border: none;">
            <td class="text-center" style="width: 33%; border: none;">Admin Gudang (Loading)</td>
            <td class="text-center" style="width: 33%; border: none;">Salesman Kanvas</td>
            <td class="text-center" style="width: 33%; border: none;">Admin Kantor (Unload)</td>
        </tr>
        <tr style="height: 60px; border: none;">
            <td style="border: none;"></td>
            <td style="border: none;"></td>
            <td style="border: none;"></td>
        </tr>
        <tr style="border: none;">
            <td class="text-center" style="border: none;">(...................)</td>
            <td class="text-center" style="border: none;">(
                <b>{{ strtoupper($canvasSession->sales->name ?? $canvasSession->kode_sales) }}</b> )
            </td>
            <td class="text-center" style="border: none;">(...................)</td>
        </tr>
    </table>

</body>

</html>
