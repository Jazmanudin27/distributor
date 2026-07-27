<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Export Data Master Pelanggan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000000;
            padding: 7px 9px;
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
            font-size: 18px;
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

    <div class="title">DATA MASTER PELANGGAN</div>
    <div class="subtitle">Tanggal Ekspor: {{ date('d-m-Y H:i:s') }}</div>

    <table>
        <thead>
            <tr>
                <th width="40">No</th>
                <th width="120">Kode Pelanggan</th>
                <th width="200">Nama Pelanggan</th>
                <th width="250">Alamat Pelanggan</th>
                <th width="120">No. HP</th>
                <th width="120">Wilayah</th>
                <th width="120">Sub Wilayah</th>
                <th width="140">Salesman</th>
                <th width="100">Tipe</th>
                <th width="120">Limit Kredit</th>
                <th width="120">Outstanding Piutang</th>
                <th width="120">Sisa Limit</th>
                <th width="110">Metode Bayar</th>
                <th width="110">Status Approval</th>
                <th width="90">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pelanggans as $index => $item)
                @php
                    $outstanding = $item->getOutstandingPiutang();
                    $sisaLimit   = max(0, $item->limit_pelanggan - $outstanding);
                    $approvalStr = 'Pending';
                    if ($item->approve === 1) {
                        $approvalStr = 'Disetujui';
                    } elseif ($item->approve === 2) {
                        $approvalStr = 'Ditolak';
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->kode_pelanggan }}</td>
                    <td>{{ $item->nama_pelanggan }}</td>
                    <td>{{ $item->alamat_pelanggan }}</td>
                    <td class="text-center">{{ $item->no_hp_pelanggan ?: '-' }}</td>
                    <td>{{ $item->wilayah->nama_wilayah ?? '-' }}</td>
                    <td>{{ $item->subWilayah->nama_wilayah ?? '-' }}</td>
                    <td>{{ $item->sales->name ?? ($item->kode_sales ?? '-') }}</td>
                    <td class="text-center">{{ $item->jenis_pelanggan == '1' ? 'Khusus' : 'Regular' }}</td>
                    <td class="text-end">Rp {{ number_format((float)$item->limit_pelanggan, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format((float)$outstanding, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format((float)$sisaLimit, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->metode_bayar ?: '-' }}</td>
                    <td class="text-center">{{ $approvalStr }}</td>
                    <td class="text-center">{{ $item->status == 1 ? 'Aktif' : 'Non-Aktif' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
