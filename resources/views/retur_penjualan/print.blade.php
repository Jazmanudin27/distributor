<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Faktur Retur Penjualan - {{ $item->no_retur }}</title>
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

        .alamat-pelanggan {
            white-space: normal;
            word-break: break-word;
        }
    </style>
</head>

<body>

    <table class="header-table" style="margin: 10px 0; width: 100%;">
        <tr>
            {{-- Logo and Company Name --}}
            <td style="width: 35%; vertical-align: top;">
                <div style="display: flex; align-items: center; gap: 5px; padding-bottom:8px;">
                    <img src="{{ asset('assets/img/MJAP.png') }}" alt="Logo MJAP" style="height: 50px;">
                    <div>
                        <div class="header-title">RETUR PENJUALAN</div>
                        <div class="header-subtitle">CV MITRA JAYA ABADI PERSADA</div>
                    </div>
                </div>
                <div class="header-address">SIRNAGALIH INDIHIANG</div>
                <div class="header-address">TASIKMALAYA</div>
                <div class="header-address">Rek: CIMB NIAGA</div>
                <div class="header-address">800190458700</div>
            </td>

            {{-- Metadata Left --}}
            <td style="width: 35%; font-size: 14px; vertical-align: top;">
                <table class="info-table">
                    <tr>
                        <td width="100">No Retur</td>
                        <td>: {{ $item->no_retur }}</td>
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
                        <td style="vertical-align: top;">Alamat</td>
                        <td class="alamat-pelanggan">:
                            {{ $item->pelanggan->alamat_toko ?? ($item->pelanggan->alamat_pelanggan ?? '-') }}</td>
                    </tr>
                    <tr>
                        <td>No. HP</td>
                        <td>: {{ $item->pelanggan->no_hp_pelanggan ?? '-' }}</td>
                    </tr>
                </table>
            </td>

            {{-- Metadata Right --}}
            <td style="width: 30%; font-size: 14px; vertical-align: top;">
                <table class="info-table">
                    <tr>
                        <td width="100">Input</td>
                        <td>: {{ $item->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Dicetak</td>
                        <td>: {{ auth()->user()->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Keterangan</td>
                        <td>: {{ $item->keterangan ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr class="text-center">
                <th class="col-no">No</th>
                <th class="col-kode">Kode</th>
                <th class="col-nama">Nama</th>
                <th class="col-jml">Jml</th>
                <th class="col-satuan">Satuan</th>
                <th class="col-harga">Harga</th>
                <th class="col-total">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $subtotalRaw = 0; @endphp
            @foreach ($item->details as $index => $detail)
                @php
                    $rowSub = $detail->qty * $detail->harga_retur;
                    $subtotalRaw += $rowSub;
                @endphp
                <tr class="row-barang">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detail->kode_barang }}</td>
                    <td>{{ $detail->barang->nama_barang ?? 'Barang Terhapus' }}</td>
                    <td class="text-center">{{ floatval($detail->qty) }}</td>
                    <td class="text-center">{{ $detail->barangSatuan->satuan ?? '-' }}</td>
                    <td class="text-end">Rp {{ number_format((float) $detail->harga_retur, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format((float) $rowSub, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr class="fw-bold">
                <td rowspan="3" colspan="3" class="text-start">
                    <small>
                        <ul style="margin:0; padding-left: 15px;">
                            1. Retur ini adalah bukti transaksi pengembalian barang.<br>
                            2. Barang retur dianggap sah setelah diterima gudang.<br>
                            3. Klaim retur tidak dilayani setelah dokumen ditandatangani.<br>
                        </ul>
                    </small>
                </td>
                <td colspan="3" class="text-end">Subtotal</td>
                <td class="text-end">Rp {{ number_format((float) $subtotalRaw, 0, ',', '.') }}</td>
            </tr>
            <tr class="fw-bold text-end">
                <td colspan="3">Potongan</td>
                <td>Rp {{ number_format((float) $item->details->sum('total_diskon_rupiah'), 0, ',', '.') }}</td>
            </tr>
            <tr class="fw-bold text-end highlight">
                <td colspan="3">Total Retur</td>
                <td>Rp {{ number_format((float) $item->total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table style="margin-top: 10px; width: 100%; border-collapse: collapse;">
        <tr>
            <td class="text-center" style="width:30%; border: none;">Diterima Oleh</td>
            <td class="text-center" style="width:30%; border: none;">Diserahkan Oleh</td>
            <td class="text-center" style="width:30%; border: none;">Hormat Kami</td>
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

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
