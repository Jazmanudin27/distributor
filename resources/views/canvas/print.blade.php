<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan DPB Kanvas - {{ $canvasSession->no_canvas }}</title>
    <style>
        body {
            font-family: Tahoma, sans-serif;
            font-size: 13px;
            margin: 0;
            line-height: 1.2;
            width: 210mm;
            padding: 10px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px 6px;
        }

        th {
            background-color: #f2f2f2;
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
            font-size: 18px;
        }

        .header-subtitle {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 2px;
        }

        .header-address {
            font-size: 11px;
            color: #555;
        }

        .info-table td {
            padding: 2px 3px;
            border: none;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
            border-bottom: 2px solid #000;
            padding-bottom: 3px;
        }
    </style>
</head>

<body>

    <table class="header-table" style="margin-bottom: 15px;">
        <tr>
            <td style="width: 50%;">
                <div style="display: flex; align-items: center; gap: 8px; padding-bottom: 8px;">
                    <img src="{{ asset('assets/img/MJAP.png') }}" alt="Logo MJAP" style="height: 45px;">
                    <div>
                        <div class="header-title">LAPORAN PENJUALAN KANVAS (DPB)</div>
                        <div class="header-subtitle">CV MITRA JAYA ABADI PERSADA</div>
                    </div>
                </div>
                <div class="header-address">SIRNAGALIH INDIHIANG, TASIKMALAYA</div>
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
                        <td>: <b>{{ strtoupper($canvasSession->sales->name ?? $canvasSession->kode_sales) }}</b> (NIK:
                            {{ $canvasSession->kode_sales }})</td>
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
                <th width="100">Satuan</th>
                <th width="100">Ambil (Loading)</th>
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
            @endphp
            @foreach ($canvasSession->details as $index => $detail)
                @php
                    $totalAmbil += (float) $detail->qty_ambil;
                    $totalTerjual += (float) $detail->qty_terjual;
                    $totalKembali += (float) $detail->qty_kembali;
                    $totalSelisih += (float) $detail->selisih;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center font-monospace">{{ $detail->kode_barang }}</td>
                    <td>{{ $detail->barang->nama_barang }}</td>
                    <td class="text-center">{{ $detail->barangSatuan->satuan ?? 'PCS' }}</td>
                    <td class="text-end fw-bold">{{ (float) $detail->qty_ambil }}</td>
                    <td class="text-end fw-bold text-info">{{ (float) $detail->qty_terjual }}</td>
                    <td class="text-end fw-bold text-success">{{ (float) $detail->qty_kembali }}</td>
                    <td class="text-end fw-bold {{ $detail->selisih != 0 ? 'text-danger' : '' }}">
                        {{ (float) $detail->selisih }}</td>
                </tr>
            @endforeach
            <tr class="fw-bold" style="background-color: #f9f9f9;">
                <td colspan="4" class="text-end">TOTAL:</td>
                <td class="text-end">{{ $totalAmbil }}</td>
                <td class="text-end">{{ $totalTerjual }}</td>
                <td class="text-end">{{ $totalKembali }}</td>
                <td class="text-end {{ $totalSelisih != 0 ? 'text-danger' : '' }}">{{ $totalSelisih }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">II. RINCIAN FAKTUR PENJUALAN YANG DIHASILKAN</div>
    <table>
        <thead>
            <tr class="text-center">
                <th width="40">No</th>
                <th width="120">Kode / No. Faktur</th>
                <th>Nama Pelanggan / Item Barang</th>
                <th width="200" class="text-center">Kuantitas & Harga</th>
                <th width="180" class="text-end">Total / Diskon</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalSales = 0;
            @endphp
            @forelse ($invoices as $index => $inv)
                @php
                    $grandTotalSales += (float) $inv->grand_total;
                @endphp
                <tr style="background-color: #e9ecef; font-weight: bold;">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center font-monospace text-primary">{{ $inv->no_faktur }}</td>
                    <td>{{ $inv->pelanggan->nama_pelanggan ?? '-' }}</td>
                    <td class="text-center">Metode: {{ $inv->jenis_transaksi === 'T' ? 'Tunai' : 'Kredit' }}</td>
                    <td class="text-end text-primary">Rp {{ number_format((float) $inv->grand_total, 0, ',', '.') }}
                    </td>
                </tr>
                @foreach ($inv->details as $dIndex => $det)
                    @php
                        $subTotalDet = $det->qty * $det->harga - $det->total_diskon;
                        $diskonText = [];
                        if ((float) $det->diskon1_persen > 0) {
                            $diskonText[] = (float) $det->diskon1_persen . '%';
                        }
                        if ((float) $det->diskon2_persen > 0) {
                            $diskonText[] = (float) $det->diskon2_persen . '%';
                        }
                        if ((float) $det->diskon3_persen > 0) {
                            $diskonText[] = (float) $det->diskon3_persen . '%';
                        }
                        $diskonTextStr = count($diskonText) > 0 ? implode(' + ', $diskonText) : '-';
                    @endphp
                    <tr style="font-size: 11px; color: #444;">
                        <td></td>
                        <td class="text-center font-monospace">{{ $det->kode_barang }}</td>
                        <td style="padding-left: 15px;">&bull; {{ $det->barang->nama_barang ?? 'Barang Terhapus' }}
                        </td>
                        <td class="text-center">{{ floatval($det->qty) }} {{ $det->barangSatuan->satuan ?? 'PCS' }} @
                            Rp {{ number_format((float) $det->harga, 0, ',', '.') }}</td>
                        <td class="text-end">
                            @if ((float) $det->total_diskon > 0)
                                <small class="text-secondary" style="font-size: 9px; display: block;">(Disc:
                                    {{ $diskonTextStr }} / -Rp
                                    {{ number_format((float) $det->total_diskon, 0, ',', '.') }})</small>
                            @endif
                            Rp {{ number_format($subTotalDet, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
                <tr
                    style="font-size: 11px; font-weight: bold; background-color: #fafafa; border-bottom: 2px solid #000;">
                    <td></td>
                    <td colspan="3" class="text-end">Subtotal Faktur: Rp
                        {{ number_format((float) $inv->total, 0, ',', '.') }} | Potongan Global: Rp
                        {{ number_format((float) $inv->diskon, 0, ',', '.') }}</td>
                    <td class="text-end text-primary">Grand Total: Rp
                        {{ number_format((float) $inv->grand_total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding: 15px;">Belum ada faktur penjualan
                        tercatat pada sesi kanvas ini.</td>
                </tr>
            @endforelse
            @if ($invoices->count() > 0)
                <tr class="fw-bold" style="background-color: #f2f2f2; font-size: 14px;">
                    <td colspan="4" class="text-end py-2">TOTAL PENJUALAN KANVAS:</td>
                    <td class="text-end py-2" style="border-double: 3px double #000;">Rp
                        {{ number_format($grandTotalSales, 0, ',', '.') }}</td>
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

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
