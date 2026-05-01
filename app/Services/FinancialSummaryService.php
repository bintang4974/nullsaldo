<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialSummaryService
{
    /**
     * Generate comprehensive financial summary for AI
     */
    public function generateSummary(User $user, ?int $month = null, ?int $year = null): string
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        // Get all financial data
        $currentBalance = $this->getCurrentBalance($user);
        $monthlyIncome = $this->getMonthlyIncome($user, $month, $year);
        $monthlyExpense = $this->getMonthlyExpense($user, $month, $year);
        $dailyAverage = $this->getDailyAverage($user, $month, $year);
        $topCategories = $this->getTopCategories($user, $month, $year);
        $budgetStatus = $this->getBudgetStatus($user, $month, $year);
        $savingsRate = $this->getSavingsRate($monthlyIncome, $monthlyExpense);
        $recentTransactions = $this->getRecentTransactions($user, 5);
        $comparisonLastMonth = $this->getComparisonLastMonth($user, $month, $year);

        // Build summary text
        $summary = "=== DATA KEUANGAN USER ===\n\n";

        // Period info
        $periodName = Carbon::create($year, $month)->format('F Y');
        $summary .= "PERIODE: {$periodName}\n";
        $summary .= "TANGGAL HARI INI: " . now()->format('d F Y') . "\n\n";

        // Current balance
        $summary .= "SALDO SAAT INI:\n";
        $summary .= "Rp " . number_format($currentBalance, 0, ',', '.') . "\n\n";

        // Monthly income & expense
        $summary .= "BULAN INI ({$periodName}):\n";
        $summary .= "- Total Pemasukan: Rp " . number_format($monthlyIncome, 0, ',', '.') . "\n";
        $summary .= "- Total Pengeluaran: Rp " . number_format($monthlyExpense, 0, ',', '.') . "\n";
        $summary .= "- Selisih (Saving): Rp " . number_format($monthlyIncome - $monthlyExpense, 0, ',', '.') . "\n";
        $summary .= "- Rata-rata Pengeluaran Harian: Rp " . number_format($dailyAverage, 0, ',', '.') . "\n";
        $summary .= "- Tingkat Tabungan: {$savingsRate}%\n\n";

        // Top categories
        if (!empty($topCategories)) {
            $summary .= "KATEGORI PENGELUARAN TERBESAR:\n";
            foreach ($topCategories as $index => $cat) {
                $summary .= ($index + 1) . ". {$cat->category_name} - Rp " .
                    number_format($cat->total, 0, ',', '.') .
                    " ({$cat->percentage}%)\n";
            }
            $summary .= "\n";
        }

        // Budget status
        if (!empty($budgetStatus)) {
            $summary .= "STATUS BUDGET:\n";
            $summary .= "- Total Budget Bulan Ini: Rp " . number_format($budgetStatus['total_budget'], 0, ',', '.') . "\n";
            $summary .= "- Total Pengeluaran: Rp " . number_format($budgetStatus['total_spending'], 0, ',', '.') . "\n";
            $summary .= "- Penggunaan Budget: {$budgetStatus['usage_percentage']}%\n";
            $summary .= "- Sisa Budget: Rp " . number_format($budgetStatus['remaining'], 0, ',', '.') . "\n";

            if (!empty($budgetStatus['over_budget'])) {
                $summary .= "- Kategori Over Budget:\n";
                foreach ($budgetStatus['over_budget'] as $item) {
                    $summary .= "  * {$item['category']} (melebihi {$item['over_percentage']}%)\n";
                }
            }
            $summary .= "\n";
        }

        // Comparison with last month
        if ($comparisonLastMonth) {
            $summary .= "PERBANDINGAN DENGAN BULAN LALU:\n";
            $summary .= "- Pengeluaran Bulan Lalu: Rp " . number_format($comparisonLastMonth['last_month_expense'], 0, ',', '.') . "\n";
            $summary .= "- Pengeluaran Bulan Ini: Rp " . number_format($monthlyExpense, 0, ',', '.') . "\n";
            $diff = $monthlyExpense - $comparisonLastMonth['last_month_expense'];
            $trend = $diff > 0 ? 'NAIK' : 'TURUN';
            $summary .= "- Trend: {$trend} Rp " . number_format(abs($diff), 0, ',', '.') .
                " ({$comparisonLastMonth['change_percentage']}%)\n\n";
        }

        // Recent transactions
        if (!empty($recentTransactions)) {
            $summary .= "TRANSAKSI TERBARU (5 terakhir):\n";
            foreach ($recentTransactions as $index => $trx) {
                $type = $trx->type === 'income' ? 'Pemasukan' : 'Pengeluaran';
                $date = Carbon::parse($trx->transaction_date)->format('d M');
                $summary .= ($index + 1) . ". [{$date}] {$type} - {$trx->category_name} - Rp " .
                    number_format($trx->amount, 0, ',', '.') . "\n";
                if ($trx->description) {
                    $summary .= "   Catatan: {$trx->description}\n";
                }
            }
            $summary .= "\n";
        }

        // Additional context
        $summary .= "=== CATATAN PENTING ===\n";
        $summary .= "1. Semua angka dalam Rupiah (IDR)\n";
        $summary .= "2. Data di atas adalah data REAL dari database user\n";
        $summary .= "3. Jangan mengarang angka atau data yang tidak ada\n";
        $summary .= "4. Berikan insight yang membantu user mengelola keuangan\n";
        $summary .= "5. Gunakan bahasa Indonesia yang ramah dan mudah dipahami\n";

        return $summary;
    }

    /**
     * Get current total balance across all wallets
     */
    protected function getCurrentBalance(User $user): float
    {
        return $user->wallets()
            ->where('is_active', true)
            ->sum('current_balance');
    }

    /**
     * Get monthly income
     */
    protected function getMonthlyIncome(User $user, int $month, int $year): float
    {
        return DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->where('wallets.user_id', $user->id)
            ->where('transactions.type', 'income')
            ->whereYear('transactions.transaction_date', $year)
            ->whereMonth('transactions.transaction_date', $month)
            ->sum('transactions.amount');
    }

    /**
     * Get monthly expense
     */
    protected function getMonthlyExpense(User $user, int $month, int $year): float
    {
        return DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->where('wallets.user_id', $user->id)
            ->where('transactions.type', 'expense')
            ->whereYear('transactions.transaction_date', $year)
            ->whereMonth('transactions.transaction_date', $month)
            ->sum('transactions.amount');
    }

    /**
     * Get daily average expense
     */
    protected function getDailyAverage(User $user, int $month, int $year): float
    {
        $totalExpense = $this->getMonthlyExpense($user, $month, $year);
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        return $totalExpense > 0 ? $totalExpense / $daysInMonth : 0;
    }

    /**
     * Get top expense categories
     */
    protected function getTopCategories(User $user, int $month, int $year, int $limit = 5)
    {
        $categories = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('wallets.user_id', $user->id)
            ->where('transactions.type', 'expense')
            ->whereYear('transactions.transaction_date', $year)
            ->whereMonth('transactions.transaction_date', $month)
            ->select(
                'categories.name as category_name',
                DB::raw('SUM(transactions.amount) as total')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        $totalExpense = $categories->sum('total');

        foreach ($categories as $cat) {
            $cat->percentage = $totalExpense > 0
                ? round(($cat->total / $totalExpense) * 100, 0)
                : 0;
        }

        return $categories;
    }

    /**
     * Get budget status
     */
    protected function getBudgetStatus(User $user, int $month, int $year): ?array
    {
        $budgets = DB::table('category_budgets')
            ->join('categories', 'category_budgets.category_id', '=', 'categories.id')
            ->where('categories.user_id', $user->id)
            ->where('category_budgets.month', $month)
            ->where('category_budgets.year', $year)
            ->select(
                'category_budgets.*',
                'categories.name as category_name'
            )
            ->get();

        if ($budgets->isEmpty()) {
            return null;
        }

        $totalBudget = $budgets->sum('monthly_limit');
        $totalSpending = 0;
        $overBudget = [];

        foreach ($budgets as $budget) {
            $spending = DB::table('transactions')
                ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
                ->where('wallets.user_id', $user->id)
                ->where('transactions.category_id', $budget->category_id)
                ->where('transactions.type', 'expense')
                ->whereYear('transactions.transaction_date', $year)
                ->whereMonth('transactions.transaction_date', $month)
                ->sum('transactions.amount');

            $totalSpending += $spending;

            if ($spending > $budget->monthly_limit) {
                $overPercentage = round((($spending - $budget->monthly_limit) / $budget->monthly_limit) * 100, 0);
                $overBudget[] = [
                    'category' => $budget->category_name,
                    'budget' => $budget->monthly_limit,
                    'spending' => $spending,
                    'over_percentage' => $overPercentage,
                ];
            }
        }

        return [
            'total_budget' => $totalBudget,
            'total_spending' => $totalSpending,
            'remaining' => $totalBudget - $totalSpending,
            'usage_percentage' => $totalBudget > 0
                ? round(($totalSpending / $totalBudget) * 100, 0)
                : 0,
            'over_budget' => $overBudget,
        ];
    }

    /**
     * Get savings rate percentage
     */
    protected function getSavingsRate(float $income, float $expense): float
    {
        if ($income <= 0) {
            return 0;
        }

        return round((($income - $expense) / $income) * 100, 0);
    }

    /**
     * Get recent transactions
     */
    protected function getRecentTransactions(User $user, int $limit = 5)
    {
        return DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('wallets.user_id', $user->id)
            ->select(
                'transactions.*',
                'categories.name as category_name'
            )
            ->orderByDesc('transactions.transaction_date')
            ->orderByDesc('transactions.created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get comparison with last month
     */
    protected function getComparisonLastMonth(User $user, int $month, int $year): ?array
    {
        // Calculate last month
        if ($month == 1) {
            $lastMonth = 12;
            $lastYear = $year - 1;
        } else {
            $lastMonth = $month - 1;
            $lastYear = $year;
        }

        $lastMonthExpense = $this->getMonthlyExpense($user, $lastMonth, $lastYear);

        if ($lastMonthExpense == 0) {
            return null;
        }

        $currentExpense = $this->getMonthlyExpense($user, $month, $year);
        $diff = $currentExpense - $lastMonthExpense;

        $changePercentage = round((abs($diff) / $lastMonthExpense) * 100, 0);

        return [
            'last_month_expense' => $lastMonthExpense,
            'current_expense' => $currentExpense,
            'difference' => $diff,
            'change_percentage' => $changePercentage,
            'trend' => $diff > 0 ? 'naik' : 'turun',
        ];
    }
}
