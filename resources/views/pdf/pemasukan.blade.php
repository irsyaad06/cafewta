<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pemasukan</title>
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

    <div style="display: table; width: 100%; margin-bottom: 20px;">
        <div style="display: table-cell; vertical-align: middle; width: 85px;">
            @if(file_exists(public_path('logo.png')))
                <img src="{{ public_path('logo.png') }}" style="width: 75px; height: auto;">
            @endif
        </div>
        <div style="display: table-cell; vertical-align: middle; text-align: left;">
            <h1 style="margin: 0; font-size: 18pt;">Cafe WTA</h1>
        </div>
        <div style="display: table-cell; vertical-align: middle; text-align: right;">
            <h2 style="margin: 0; font-size: 16pt;">Laporan Pemasukan</h2>
            <p style="margin: 5px 0 0; font-size: 10pt;">Periode: {{ $fromDate }} s/d {{ $toDate }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No Invoice</th>
                <th>Kasir</th>
                <th>Metode</th>
                <th class="text-right">Tagihan (Rp)</th>
                <th class="text-right">HPP (Rp)</th>
                <th class="text-right">Keuntungan (Rp)</th>
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
                    <td colspan="7" style="text-align: center;">Tidak ada data pemasukan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">Total</th>
                <th class="text-right">{{ number_format($totalTagihan, 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format($totalHpp, 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format($totalProfit, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    @if(isset($topMenus) && count($topMenus) > 0)
    <div style="margin-top: 30px;">
        <h3>Top 10 Menu Paling Banyak Dipesan</h3>
        <table style="width: 50%;">
            <thead>
                <tr>
                    <th style="width: 10%;">No</th>
                    <th>Nama Menu</th>
                    <th class="text-right" style="width: 30%;">Total Terjual</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topMenus as $index => $topMenu)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $topMenu->menu_name }}</td>
                        <td class="text-right">{{ $topMenu->total_qty }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</body>
</html>
