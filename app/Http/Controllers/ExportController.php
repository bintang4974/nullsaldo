<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    /**
     * Show export form
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $wallets = $user->wallets()->where('is_active', true)->get();
        $categories = $user->categories;

        return view('exports.index', compact('wallets', 'categories'));
    }

    /**
     * Export transactions to PDF
     */
    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'wallet_id' => 'nullable|exists:wallets,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'type' => 'nullable|in:income,expense',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $user = $request->user();

        // Get transactions
        $transactions = $this->getFilteredTransactions($user->id, $validated);

        // Get summary
        $summary = [
            'total_income' => $transactions->where('type', 'income')->sum('amount'),
            'total_expense' => $transactions->where('type', 'expense')->sum('amount'),
        ];
        $summary['net'] = $summary['total_income'] - $summary['total_expense'];

        // Prepare data
        $data = [
            'transactions' => $transactions,
            'summary' => $summary,
            'filters' => $validated,
            'user' => $user,
            'period' => \Carbon\Carbon::create($validated['year'], $validated['month'])->format('F Y'),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('exports.pdf', $data);

        $filename = 'Laporan-Keuangan-' . $data['period'] . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export transactions to Excel
     */
    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'wallet_id' => 'nullable|exists:wallets,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'type' => 'nullable|in:income,expense',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $user = $request->user();

        // Get transactions
        $transactions = $this->getFilteredTransactions($user->id, $validated);

        $period = \Carbon\Carbon::create($validated['year'], $validated['month'])->format('F-Y');
        $filename = 'Transaksi-' . $period . '.xlsx';

        return Excel::download(
            new TransactionsExport($transactions, $validated),
            $filename
        );
    }

    /**
     * Get filtered transactions
     */
    protected function getFilteredTransactions(int $userId, array $filters)
    {
        $query = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('wallets.user_id', $userId)
            ->whereYear('transactions.transaction_date', $filters['year'])
            ->whereMonth('transactions.transaction_date', $filters['month']);

        // Filter by wallet
        if (!empty($filters['wallet_id'])) {
            $query->where('transactions.wallet_id', $filters['wallet_id']);
        }

        // Filter by type
        if (!empty($filters['type'])) {
            $query->where('transactions.type', $filters['type']);
        }

        // Filter by category
        if (!empty($filters['category_id'])) {
            $query->where('transactions.category_id', $filters['category_id']);
        }

        return $query
            ->select(
                'transactions.*',
                'wallets.name as wallet_name',
                'categories.name as category_name',
                'categories.icon as category_icon',
                'categories.color as category_color'
            )
            ->orderBy('transactions.transaction_date', 'desc')
            ->orderBy('transactions.created_at', 'desc')
            ->get();
    }
}
