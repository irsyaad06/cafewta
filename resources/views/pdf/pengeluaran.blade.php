<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pengeluaran</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 16pt;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 10pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Pengeluaran</h2>
        <p>Periode: {{ $fromDate }} s/d {{ $toDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th>Diinput Oleh</th>
                <th class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @forelse($expenses as $expense)
                @php $total += $expense->amount; @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($expense->date)->format('d/m/Y') }}</td>
                    <td>{{ $expense->category ? $expense->category->name : '-' }}</td>
                    <td>{{ $expense->description }}</td>
                    <td>{{ $expense->user ? $expense->user->name : '-' }}</td>
                    <td class="text-right">{{ number_format($expense->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Tidak ada data pengeluaran pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">Total</th>
                <th class="text-right">{{ number_format($total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

</body>
</html>
