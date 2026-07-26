<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pembelian Bahan Baku #{{ $purchase->id }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 40px;
            background: #f9f9f9;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 28px;
        }
        .header .status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-proses { background: #fff3cd; color: #856404; }
        .status-selesai { background: #d4edda; color: #155724; }
        
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        .info-block h3 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #7f8c8d;
            font-size: 14px;
            text-transform: uppercase;
        }
        .info-block p {
            margin: 5px 0;
            line-height: 1.5;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: bold;
        }
        td.right, th.right {
            text-align: right;
        }
        
        .totals {
            width: 100%;
            max-width: 400px;
            margin-left: auto;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .totals-row.grand-total {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: none;
            border-top: 2px solid #2c3e50;
            padding-top: 15px;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #7f8c8d;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .print-btn {
            display: block;
            width: 150px;
            margin: 20px auto;
            padding: 10px 15px;
            background: #3498db;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .print-btn:hover { background: #2980b9; }

        @media print {
            body { background: #fff; padding: 0; }
            .invoice-container { box-shadow: none; padding: 0; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <a href="javascript:window.print()" class="print-btn">Cetak Invoice</a>

    <div class="invoice-container">
        <div class="header">
            <div>
                <h1>INVOICE PEMBELIAN</h1>
                <p>No. Pesanan: <strong>#{{ str_pad($purchase->id, 5, '0', STR_PAD_LEFT) }}</strong></p>
                <p>Tanggal: {{ \Carbon\Carbon::parse($purchase->date)->translatedFormat('d F Y') }}</p>
            </div>
            <div style="text-align: right;">
                <span class="status {{ $purchase->status === 'selesai' ? 'status-selesai' : 'status-proses' }}">
                    {{ ucfirst($purchase->status) }}
                </span>
            </div>
        </div>

        <div class="info-section">
            <div class="info-block">
                <h3>Informasi Pemasok</h3>
                <p><strong>{{ $purchase->supplier->name ?? '-' }}</strong></p>
                <p>{{ $purchase->supplier->address ?? '-' }}</p>
                <p>{{ $purchase->supplier->phone ?? '-' }}</p>
                <p>{{ $purchase->supplier->email ?? '-' }}</p>
            </div>
            <div class="info-block" style="text-align: right;">
                <h3>Informasi Kafe</h3>
                <p><strong>Cafe WTA</strong></p>
                <p>Dibuat Oleh: {{ $purchase->user->name ?? 'Admin' }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Bahan Baku</th>
                    <th class="right">Harga Beli</th>
                    <th class="right">Kuantitas</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
                <tr>
                    <td>{{ $item->rawMaterial->name ?? 'Bahan Tidak Diketahui' }}</td>
                    <td class="right">Rp {{ number_format($item->buy_price, 0, ',', '.') }} / {{ $item->unit }}</td>
                    <td class="right">{{ number_format($item->quantity, 0, ',', '.') }} {{ $item->unit }}</td>
                    <td class="right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row grand-total">
                <span>Total Pesanan</span>
                <span>Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        @if($purchase->notes)
        <div style="margin-top: 40px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
            <strong style="color: #2c3e50;">Catatan:</strong>
            <p style="margin: 10px 0 0 0; font-size: 14px;">{{ $purchase->notes }}</p>
        </div>
        @endif

        <div class="footer">
            Invoice ini dibuat otomatis oleh sistem dan sah sebagai bukti pembelian.
        </div>
    </div>
</body>
</html>
