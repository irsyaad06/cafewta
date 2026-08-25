<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>No Invoice</th>
            <th>Kasir</th>
            <th>Metode</th>
            <th>Tagihan (Rp)</th>
            <th>HPP (Rp)</th>
            <th>Keuntungan (Rp)</th>
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
                <td>{{ $transaction->total_amount }}</td>
                <td>{{ $transaction->total_hpp }}</td>
                <td>{{ $transaction->total_profit }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7">Tidak ada data pemasukan pada periode ini.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4" style="text-align: right; font-weight: bold;">Total Keseluruhan:</th>
            <th style="font-weight: bold;">{{ $totalTagihan }}</th>
            <th style="font-weight: bold;">{{ $totalHpp }}</th>
            <th style="font-weight: bold;">{{ $totalProfit }}</th>
        </tr>
    </tfoot>
</table>

<table>
    <tr></tr>
    <tr>
        <th colspan="3" style="font-weight: bold; font-size: 14px;">Top 10 Menu Paling Banyak Dipesan</th>
    </tr>
    <tr>
        <th style="font-weight: bold;">No</th>
        <th style="font-weight: bold;">Nama Menu</th>
        <th style="font-weight: bold;">Total Terjual</th>
    </tr>
    @forelse($topMenus as $index => $topMenu)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $topMenu->menu_name }}</td>
            <td>{{ $topMenu->total_qty }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="3">Tidak ada data.</td>
        </tr>
    @endforelse
</table>
