<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Kategori</th>
            <th>Keterangan</th>
            <th>Diinput Oleh</th>
            <th>Nominal (Rp)</th>
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
                <td>{{ $expense->amount }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5">Tidak ada data pengeluaran pada periode ini.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4" style="text-align: right; font-weight: bold;">Total Keseluruhan:</th>
            <th style="font-weight: bold;">{{ $total }}</th>
        </tr>
    </tfoot>
</table>

<table>
    <tr></tr>
    <tr>
        <th colspan="3" style="font-weight: bold; font-size: 14px;">Kategori Pengeluaran Paling Sering</th>
    </tr>
    <tr>
        <th style="font-weight: bold;">No</th>
        <th style="font-weight: bold;">Kategori</th>
        <th style="font-weight: bold;">Total Frekuensi</th>
    </tr>
    @forelse($topExpenses as $index => $topExpense)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $topExpense->category_name }}</td>
            <td>{{ $topExpense->total_count }} kali</td>
        </tr>
    @empty
        <tr>
            <td colspan="3">Tidak ada data.</td>
        </tr>
    @endforelse
</table>
