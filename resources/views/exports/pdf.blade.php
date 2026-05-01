<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - {{ $period }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }

        .container {
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #3B82F6;
        }

        .header h1 {
            font-size: 24px;
            color: #1F2937;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 14px;
            color: #6B7280;
        }

        .summary {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            overflow: hidden;
        }

        .summary-row {
            display: table-row;
        }

        .summary-cell {
            display: table-cell;
            padding: 12px 15px;
            border-bottom: 1px solid #E5E7EB;
        }

        .summary-cell:first-child {
            font-weight: 600;
            background-color: #F9FAFB;
            width: 40%;
        }

        .summary-cell.income {
            color: #10B981;
            font-weight: 700;
        }

        .summary-cell.expense {
            color: #EF4444;
            font-weight: 700;
        }

        .summary-cell.net {
            color: #3B82F6;
            font-weight: 700;
            font-size: 14px;
        }

        .info-section {
            margin-bottom: 25px;
            padding: 15px;
            background-color: #EFF6FF;
            border-left: 4px solid #3B82F6;
            border-radius: 4px;
        }

        .info-section h3 {
            font-size: 14px;
            margin-bottom: 8px;
            color: #1F2937;
        }

        .info-section p {
            font-size: 12px;
            color: #4B5563;
            margin-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table thead {
            background-color: #F3F4F6;
        }

        table th {
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            color: #374151;
            border-bottom: 2px solid #D1D5DB;
        }

        table td {
            padding: 10px 8px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 11px;
        }

        table tbody tr:hover {
            background-color: #F9FAFB;
        }

        .type-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
        }

        .type-income {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .type-expense {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .amount-income {
            color: #10B981;
            font-weight: 600;
        }

        .amount-expense {
            color: #EF4444;
            font-weight: 600;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            font-size: 10px;
            color: #9CA3AF;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #9CA3AF;
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- Header -->
        <div class="header">
            <h1>LAPORAN KEUANGAN</h1>
            <p>Periode: {{ $period }}</p>
        </div>

        <!-- Filter Info -->
        @if (!empty($filters['wallet_id']) || !empty($filters['type']) || !empty($filters['category_id']))
            <div class="info-section">
                <h3>Filter yang Diterapkan:</h3>
                @if (!empty($filters['wallet_id']))
                    <p>• Dompet: {{ $transactions->first()->wallet_name ?? 'N/A' }}</p>
                @endif
                @if (!empty($filters['type']))
                    <p>• Jenis: {{ $filters['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran' }}</p>
                @endif
                @if (!empty($filters['category_id']))
                    <p>• Kategori: {{ $transactions->first()->category_name ?? 'N/A' }}</p>
                @endif
            </div>
        @endif

        <!-- Summary -->
        <div class="summary">
            <div class="summary-row">
                <div class="summary-cell">Total Pemasukan</div>
                <div class="summary-cell income">Rp {{ number_format($summary['total_income'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">Total Pengeluaran</div>
                <div class="summary-cell expense">Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">Saldo Bersih</div>
                <div class="summary-cell net">Rp {{ number_format($summary['net'], 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Transactions Table -->
        <h3 style="margin-bottom: 15px; font-size: 16px; color: #1F2937;">Detail Transaksi</h3>

        @if ($transactions->isEmpty())
            <div class="empty-state">
                <p>Tidak ada transaksi pada periode ini</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Dompet</th>
                        <th>Kategori</th>
                        <th>Jenis</th>
                        <th style="text-align: right;">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $transaction)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}</td>
                            <td>{{ $transaction->wallet_name }}</td>
                            <td>{{ $transaction->category_name ?? '-' }}</td>
                            <td>
                                <span
                                    class="type-badge {{ $transaction->type === 'income' ? 'type-income' : 'type-expense' }}">
                                    {{ $transaction->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <span
                                    class="{{ $transaction->type === 'income' ? 'amount-income' : 'amount-expense' }}">
                                    Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Laporan ini dibuat secara otomatis oleh MyWallet pada {{ now()->format('d F Y, H:i') }} WIB</p>
            <p>© {{ now()->year }} MyWallet - Aplikasi Pencatatan Keuangan Pribadi</p>
        </div>

    </div>
</body>

</html>
