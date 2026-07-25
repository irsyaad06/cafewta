<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pemasukan</title>
    <style>
        @page { margin: 40px; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333333;
            line-height: 1.4;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2c3e50;
        }
        .header h2 { margin: 0; font-size: 22px; color: #2c3e50; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 5px 0 0; font-size: 12px; color: #7f8c8d; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px 6px;
            border: 1px solid #bdc3c7;
        }
        th {
            background-color: #2c3e50;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            text-align: left;
        }
        th.text-right { text-align: right; }
        
        tbody tr:nth-child(even) { background-color: #f9f9f9; }
        
        .summary-row {
            background-color: #ecf0f1;
            font-weight: bold;
        }
        .summary-row td {
            border-top: 2px solid #2c3e50;
            border-bottom: 2px solid #2c3e50;
            font-size: 11px;
            color: #2c3e50;
        }
        
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #95a5a6;
            border-top: 1px solid #bdc3c7;
            padding-top: 10px;
            display: table;
            width: 100%;
        }
        .footer-left { display: table-cell; text-align: left; }
        .footer-right { display: table-cell; text-align: right; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Pemasukan Cafe</h2>
        <p>Periode: <strong>{{ $fromDate }}</strong> s/d <strong>{{ $toDate }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="12%">Tanggal</th>
                <th width="14%">No Invoice</th>
                <th width="14%">Kasir</th>
                <th width="14%">Metode</th>
                <th width="15%" class="text-right">Tagihan (Rp)</th>
                <th width="15%" class="text-right">HPP (Rp)</th>
                <th width="16%" class="text-right">Keuntungan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalTagihan = 0; 
                $totalHpp = 0; 
                $totalProfit = 0; 
            @endphp
            @forelse($transactions as $transaction)
                @php 
                    $totalTagihan += $transaction->total_amount; 
                    $totalHpp += $transaction->total_hpp; 
                    $totalProfit += $transaction->total_profit; 
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $transaction->invoice_number }}</td>
                    <td>{{ $transaction->user ? $transaction->user->name : '-' }}</td>
                    <td>{{ $transaction->paymentMethod ? $transaction->paymentMethod->name : '-' }}</td>
                    <td class="text-right">{{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($transaction->total_hpp, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($transaction->total_profit, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">Tidak ada data pemasukan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="summary-row">
                <td colspan="4" class="text-right">TOTAL KESELURUHAN :</td>
                <td class="text-right">{{ number_format($totalTagihan, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalHpp, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalProfit, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <div class="footer-left">Dokumen ini dihasilkan secara otomatis oleh sistem.</div>
        <div class="footer-right">Dicetak pada: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

</body>
</html>
