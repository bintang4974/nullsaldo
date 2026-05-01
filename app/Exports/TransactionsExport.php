<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $transactions;
    protected $filters;

    public function __construct($transactions, $filters)
    {
        $this->transactions = $transactions;
        $this->filters = $filters;
    }

    /**
     * Return collection of transactions
     */
    public function collection()
    {
        return $this->transactions;
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'Tanggal',
            'Dompet',
            'Kategori',
            'Jenis',
            'Nominal',
            'Catatan',
        ];
    }

    /**
     * Map data to columns
     */
    public function map($transaction): array
    {
        return [
            \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y'),
            $transaction->wallet_name,
            $transaction->category_name ?? '-',
            $transaction->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
            $transaction->amount,
            $transaction->description ?? '-',
        ];
    }

    /**
     * Apply styles to worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (headers)
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3B82F6'],
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];
    }

    /**
     * Set worksheet title
     */
    public function title(): string
    {
        $period = \Carbon\Carbon::create($this->filters['year'], $this->filters['month'])->format('F Y');
        return 'Transaksi ' . $period;
    }
}
