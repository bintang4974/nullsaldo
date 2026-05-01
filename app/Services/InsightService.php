<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InsightService
{
    /**
     * Generate insights untuk user
     */
    public function generateInsights(User $user): array
    {
        $insights = [];

        // 1. Top spending category
        $topCategory = $this->getTopSpendingCategory($user);
        if ($topCategory) {
            $insights[] = [
                'icon' => 'pie-chart',
                'color' => 'red',
                'text' => "Pengeluaran terbesar bulan ini adalah <strong>{$topCategory->name}</strong> dengan total Rp " .
                    number_format($topCategory->total, 0, ',', '.') .
                    " ({$topCategory->percentage}% dari total pengeluaran).",
            ];
        }

        // 2. Savings rate
        $savingsRate = $this->getSavingsRate($user);
        if ($savingsRate !== null) {
            if ($savingsRate > 0) {
                $emoji = $savingsRate >= 50 ? '🎉' : '💪';
                $message = $savingsRate >= 50
                    ? "Kamu berhasil menabung <strong>{$savingsRate}%</strong> dari pemasukan bulan ini. Luar biasa!"
                    : "Tingkat tabungan bulan ini adalah <strong>{$savingsRate}%</strong>. Coba tingkatkan lagi bulan depan!";

                $insights[] = [
                    'icon' => 'piggy-bank',
                    'color' => $savingsRate >= 50 ? 'green' : 'yellow',
                    'text' => $emoji . ' ' . $message,
                ];
            } else if ($savingsRate < 0) {
                $insights[] = [
                    'icon' => 'alert-triangle',
                    'color' => 'red',
                    'text' => "⚠️ Pengeluaran melebihi pemasukan bulan ini (<strong>" . abs($savingsRate) . "%</strong>). Evaluasi pengeluaran segera!",
                ];
            }
        }

        // 3. Comparison with last month
        $comparison = $this->compareWithLastMonth($user);
        if ($comparison['has_data']) {
            $diff = abs($comparison['difference']);
            $isHigher = $comparison['is_higher'];
            $percentChange = $comparison['percent_change'];

            if ($diff > 0) {
                $emoji = $isHigher ? '📈' : '📉';
                $message = $isHigher
                    ? "Pengeluaran bulan ini <strong>lebih tinggi Rp " . number_format($diff, 0, ',', '.') . "</strong> ({$percentChange}%) dibanding bulan lalu."
                    : "Pengeluaran bulan ini <strong>lebih rendah Rp " . number_format($diff, 0, ',', '.') . "</strong> ({$percentChange}%) dibanding bulan lalu. Bagus!";

                $insights[] = [
                    'icon' => $isHigher ? 'trending-up' : 'trending-down',
                    'color' => $isHigher ? 'orange' : 'green',
                    'text' => $emoji . ' ' . $message,
                ];
            }
        }

        // 4. Spending streak (3 hari berturut-turut ada transaksi)
        $streak = $this->getTransactionStreak($user);
        if ($streak >= 3) {
            $insights[] = [
                'icon' => 'calendar-check',
                'color' => 'blue',
                'text' => "🔥 Kamu rajin mencatat! Sudah <strong>{$streak} hari berturut-turut</strong> ada transaksi tercatat. Pertahankan konsistensinya!",
            ];
        }

        // 5. Highest single transaction
        $highestTransaction = $this->getHighestTransaction($user);
        if ($highestTransaction) {
            $insights[] = [
                'icon' => 'award',
                'color' => 'purple',
                'text' => "💸 Transaksi terbesar bulan ini adalah <strong>{$highestTransaction->category_name}</strong> sebesar Rp " .
                    number_format($highestTransaction->amount, 0, ',', '.') .
                    " pada " . Carbon::parse($highestTransaction->transaction_date)->format('d M') . ".",
            ];
        }

        // 6. No spending day (jika ada hari tanpa pengeluaran minggu ini)
        $noSpendingDays = $this->getNoSpendingDaysThisWeek($user);
        if ($noSpendingDays > 0) {
            $insights[] = [
                'icon' => 'shield-check',
                'color' => 'green',
                'text' => "✨ Hebat! Ada <strong>{$noSpendingDays} hari</strong> minggu ini tanpa pengeluaran. Ini membantu kamu menabung lebih banyak!",
            ];
        }

        // Limit to 4 insights
        return array_slice($insights, 0, 4);
    }

    /**
     * Get top spending category this month
     */
    protected function getTopSpendingCategory(User $user)
    {
        $result = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('wallets.user_id', $user->id)
            ->where('transactions.type', 'expense')
            ->whereYear('transactions.transaction_date', now()->year)
            ->whereMonth('transactions.transaction_date', now()->month)
            ->select(
                'categories.name',
                DB::raw('SUM(transactions.amount) as total')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->first();

        if ($result) {
            $totalExpense = DB::table('transactions')
                ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
                ->where('wallets.user_id', $user->id)
                ->where('transactions.type', 'expense')
                ->whereYear('transactions.transaction_date', now()->year)
                ->whereMonth('transactions.transaction_date', now()->month)
                ->sum('transactions.amount');

            $result->percentage = $totalExpense > 0
                ? round(($result->total / $totalExpense) * 100, 0)
                : 0;
        }

        return $result;
    }

    /**
     * Get savings rate (percentage)
     */
    protected function getSavingsRate(User $user): ?float
    {
        $stats = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->where('wallets.user_id', $user->id)
            ->whereYear('transactions.transaction_date', now()->year)
            ->whereMonth('transactions.transaction_date', now()->month)
            ->select(
                DB::raw('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income'),
                DB::raw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense')
            )
            ->first();

        if ($stats->income > 0) {
            return round((($stats->income - $stats->expense) / $stats->income) * 100, 0);
        }

        return null;
    }

    /**
     * Compare expense with last month
     */
    protected function compareWithLastMonth(User $user): array
    {
        $currentMonth = now();
        $lastMonth = now()->subMonth();

        $current = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->where('wallets.user_id', $user->id)
            ->where('transactions.type', 'expense')
            ->whereYear('transactions.transaction_date', $currentMonth->year)
            ->whereMonth('transactions.transaction_date', $currentMonth->month)
            ->sum('transactions.amount');

        $last = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->where('wallets.user_id', $user->id)
            ->where('transactions.type', 'expense')
            ->whereYear('transactions.transaction_date', $lastMonth->year)
            ->whereMonth('transactions.transaction_date', $lastMonth->month)
            ->sum('transactions.amount');

        $percentChange = 0;
        if ($last > 0) {
            $percentChange = round((abs($current - $last) / $last) * 100, 0);
        }

        return [
            'has_data' => $last > 0,
            'difference' => $current - $last,
            'is_higher' => $current > $last,
            'percent_change' => $percentChange,
        ];
    }

    /**
     * Get transaction streak (consecutive days with transactions)
     */
    protected function getTransactionStreak(User $user): int
    {
        $streak = 0;
        $currentDate = today();

        for ($i = 0; $i < 30; $i++) {
            $hasTransaction = DB::table('transactions')
                ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
                ->where('wallets.user_id', $user->id)
                ->whereDate('transactions.transaction_date', $currentDate)
                ->exists();

            if ($hasTransaction) {
                $streak++;
                $currentDate = $currentDate->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Get highest single transaction this month
     */
    protected function getHighestTransaction(User $user)
    {
        return DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('wallets.user_id', $user->id)
            ->where('transactions.type', 'expense')
            ->whereYear('transactions.transaction_date', now()->year)
            ->whereMonth('transactions.transaction_date', now()->month)
            ->select(
                'transactions.amount',
                'transactions.transaction_date',
                'categories.name as category_name'
            )
            ->orderByDesc('transactions.amount')
            ->first();
    }

    /**
     * Get number of days without spending this week
     */
    protected function getNoSpendingDaysThisWeek(User $user): int
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $daysWithSpending = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->where('wallets.user_id', $user->id)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.transaction_date', [$startOfWeek, $endOfWeek])
            ->select(DB::raw('DISTINCT DATE(transactions.transaction_date) as date'))
            ->count();

        $totalDaysThisWeek = min(now()->diffInDays($startOfWeek) + 1, 7);

        return max(0, $totalDaysThisWeek - $daysWithSpending);
    }
}
