<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Export Data Master Barang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000000;
            padding: 6px 8px;
            vertical-align: top;
        }
        th {
            background-color: #d9e1f2;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-end {
            text-align: right;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 11px;
            text-align: center;
            margin-bottom: 15px;
            color: #555;
        }
    </style>
</head>
<body>

    <div class="title">DATA MASTER BARANG</div>
    <div class="subtitle">Tanggal Ekspor: {{ date('d-m-Y H:i:s') }}</div>

    <table>
        <thead>
            <tr>
                <th width="40">No</th>
                <th width="100">Kode Barang</th>
                <th width="100">Kode Item</th>
                <th width="200">Nama Barang</th>
                <th width="150">Supplier</th>
                <th width="120">Kategori</th>
                <th width="120">Merk</th>
                <th width="100">Stok (Base)</th>
                <th width="180">Format Stok</th>
                <th width="220">Detail Satuan (Isi | H. Pokok | H. Jual)</th>
                <th width="80">Stok Min</th>
                <th width="90">Status</th>
                <th width="150">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->kode_barang }}</td>
                    <td class="text-center">{{ $item->kode_item ?? '-' }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->supplier->nama_supplier ?? '-' }}</td>
                    <td>{{ $item->kategori ?? '-' }}</td>
                    <td>{{ $item->merk ?? '-' }}</td>
                    <td class="text-end">{{ number_format((float) ($item->stok ?? 0), 2, ',', '.') }}</td>
                    <td>{{ $item->formatStok($item->stok ?? 0) }}</td>
                    <td>
                        @if($item->satuans && $item->satuans->count() > 0)
                            @foreach($item->satuans as $sat)
                                {{ $sat->satuan }} (Isi: {{ $sat->isi }} | Rp {{ number_format($sat->harga_pokok, 0, ',', '.') }} | Rp {{ number_format($sat->harga_jual, 0, ',', '.') }})@if(!$loop->last) <br> @endif
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">{{ number_format((float)($item->stok_min ?? 0), 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->status ? 'Aktif' : 'Non-Aktif' }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
