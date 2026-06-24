<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Faktur Penjualan - {{ $item->no_faktur }}</title>
    <style>
        body {
            font-family: Tahoma, sans-serif;
            font-size: 14px;
            margin: 0;
            line-height: 1.2;
            width: 210mm;
        }

        table {
            border-collapse: collapse;
            width: 100%;
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

        .highlight {
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        ul {
            padding-left: 16px;
            margin: 5px 0 0 0;
        }

        small {
            font-size: 11px;
        }

        .col-no {
            width: 20px;
        }

        .col-kode {
            width: 60px;
        }

        .col-nama {
            width: 200px;
        }

        .col-jml {
            width: 30px;
        }

        .col-satuan {
            width: 30px;
        }

        .col-harga {
            width: 70px;
        }

        .col-diskon {
            width: 30px;
        }

        .col-total {
            width: 90px;
        }

        .header-title {
            font-weight: bold;
            font-size: 18px;
        }

        .header-subtitle {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .header-address {
            font-size: 13px;
        }

        .info-table td {
            padding: 1px 3px;
        }

        .alamat-toko {
            white-space: normal;
            word-break: break-word;
        }
    </style>
</head>

<body>

    <table class="header-table" style="margin: 10px 0;">
        <tr>
            <td style="width: 35%;">
                <div style="display: flex; align-items: center; gap: 8px; padding-bottom:8px;">
                    <img src="{{ asset('assets/img/MJAP.png') }}" alt="Logo MJAP" style="height: 50px;">
                    <div>
                        <div class="header-title">FAKTUR PENJUALAN</div>
                        <div class="header-subtitle">CV MITRA JAYA ABADI PERSADA</div>
                    </div>
                </div>
                <div class="header-address">SIRNAGALIH INDIHIANG</div>
                <div class="header-address">TASIKMALAYA</div>
                <div class="header-address">Rek: CIMB NIAGA A.N NANDANG PRISTIWANTO</div>
                <div class="header-address">800184933300</div>
            </td>
            <td style="width: 35%; font-size: 14px;">
                <table class="info-table">
                    <tr>
                        <td width="100">No Faktur</td>
                        <td>: {{ $item->no_faktur }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td>Pelanggan</td>
                        <td>: {{ $item->pelanggan->nama_pelanggan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Sales</td>
                        <td>: <b>{{ strtoupper($item->sales->name ?? '-') }}</b></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Alamat Toko</td>
                        <td class="alamat-toko">:
                            {{ $item->pelanggan->alamat_toko ?? ($item->pelanggan->alamat_pelanggan ?? '-') }}</td>
                    </tr>
                    <tr>
                        <td>No Telp.</td>
                        <td>: {{ $item->pelanggan->no_hp_pelanggan ?? '-' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 30%; font-size: 14px;">
                <table class="info-table">
                    <tr>
                        <td width="120">Tgl. Jatuh Tempo</td>
                        <td>:
                            @if (strtolower($item->jenis_transaksi) === 'kredit')
                                {{ $item->tanggal? \Carbon\Carbon::parse($item->tanggal)->addDays($item->pelanggan->ljt ?? 30)->format('d/m/Y'): '-' }}
                            @else
                                {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Wilayah</td>
                        <td>: <b>{{ $item->pelanggan->wilayah->nama_wilayah ?? '-' }}</b></td>
                    </tr>
                    <tr>
                        <td>Input</td>
                        <td>: {{ $item->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Dicetak</td>
                        <td>: {{ auth()->user()->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Jenis Transaksi</td>
                        <td>
                            <b style="zoom: 180%">
                                {{ ($item->jenis_transaksi ?? 'K') == 'T' ? 'TUNAI' : 'KREDIT' }}
                            </b>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @php
        $showD1 = false;
        $showD2 = false;
        $showD3 = false;
        foreach ($item->details as $d) {
            if (floatval($d->diskon1_persen) > 0) {
                $showD1 = true;
            }
            if (floatval($d->diskon2_persen) > 0) {
                $showD2 = true;
            }
            if (floatval($d->diskon3_persen) > 0) {
                $showD3 = true;
            }
        }
        $colspanRight = 3 + ($showD1 ? 1 : 0) + ($showD2 ? 1 : 0) + ($showD3 ? 1 : 0);
    @endphp
    <table>
        <thead>
            <tr class="text-center">
                <td class="col-no">No</td>
                <td class="col-kode">Kode</td>
                <td class="col-nama">Nama</td>
                <td class="col-jml">Jml</td>
                <td class="col-satuan">Satuan</td>
                <td class="col-harga">Harga</td>
                @if ($showD1)
                    <td class="col-diskon">D1</td>
                @endif
                @if ($showD2)
                    <td class="col-diskon">D2</td>
                @endif
                @if ($showD3)
                    <td class="col-diskon">D3</td>
                @endif
                <td class="col-total">Total</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($item->details as $index => $detail)
                <tr class="row-barang">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detail->kode_barang }}</td>
                    <td>{{ $detail->barang->nama_barang ?? 'Barang Terhapus' }}</td>
                    <td class="text-center">{{ floatval($detail->qty) }}</td>
                    <td class="text-center">{{ $detail->barangSatuan->satuan ?? '-' }}</td>
                    <td class="text-end">Rp {{ number_format((float) $detail->harga, 0, ',', '.') }}</td>
                    @if ($showD1)
                        <td class="text-center">
                            {{ floatval($detail->diskon1_persen) > 0 ? floatval($detail->diskon1_persen) . '%' : '-' }}
                        </td>
                    @endif
                    @if ($showD2)
                        <td class="text-center">
                            {{ floatval($detail->diskon2_persen) > 0 ? floatval($detail->diskon2_persen) . '%' : '-' }}
                        </td>
                    @endif
                    @if ($showD3)
                        <td class="text-center">
                            {{ floatval($detail->diskon3_persen) > 0 ? floatval($detail->diskon3_persen) . '%' : '-' }}
                        </td>
                    @endif
                    <td class="text-end">Rp
                        {{ number_format((float) ($detail->qty * $detail->harga - $detail->total_diskon), 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach

            <tr>
                <td class="text-start" rowspan="3" colspan="3">
                    <small>
                        <ul style="margin:0; padding-left: 15px;">
                            1. Faktur asli adalah bukti pembayaran yang sah.<br>
                            2. Barang di mobil jadi tanggung jawab kurir/supir.<br>
                            3. Pembayaran dengan GIRO dianggap lunas setelah cair.<br>
                            4. Klaim tidak dilayani setelah faktur ditandatangani.<br>
                        </ul>
                    </small>
                </td>
                <td class="text-end" colspan="{{ $colspanRight }}">Subtotal</td>
                <td class="text-end">Rp {{ number_format((float) $item->total, 0, ',', '.') }}</td>
            </tr>
            <tr class="text-end">
                <td colspan="{{ $colspanRight }}">Potongan</td>
                <td>Rp {{ number_format((float) $item->diskon, 0, ',', '.') }}</td>
            </tr>
            <tr class="text-end">
                <td colspan="{{ $colspanRight }}">Total Keseluruhan</td>
                <td class="fw-bold">Rp {{ number_format((float) $item->grand_total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table style="margin-top: 10px; border-collapse: collapse; width: 100%;">
        <tr>
            <td style="width:15%; border: none;">Keterangan</td>
            <td style="width:85%; border: none;" colspan="3">: </td>
        </tr>
    </table>

    <table style="margin-top: 10px; border-collapse: collapse; width: 100%;">
        <tr>
            <td class="text-center" style="width:30%; border: none;">Penerima</td>
            <td class="text-center" style="width:30%; border: none;">Pengirim</td>
            <td class="text-center" style="width:30%; border: none;">Hormat Kami</td>
            <td rowspan="3" style="border: none; vertical-align: top;"></td>
        </tr>
        <tr style="height: 35px;">
            <td style="border: none;"></td>
            <td style="border: none;"></td>
            <td style="border: none;"></td>
        </tr>
        <tr>
            <td class="text-center" style="border: none;">(...................)</td>
            <td class="text-center" style="border: none;">(...................)</td>
            <td class="text-center" style="border: none;">(...................)</td>
        </tr>
    </table>


</body>

</html>
