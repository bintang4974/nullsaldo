<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlertService
{
    /**
     * Get alerts untuk user
     */
    public function getAlerts(User $user): array
    {
        $alerts = [];

        // 1. Belum ada transaksi hari ini (hanya tampil setelah jam 12 siang)
        if (now()->hour >= 12 && $this->hasNoTransactionsToday($user)) {
            $alerts[] = [
                'key' => 'no_transactions_today',
                'type' => 'info',
                'icon' => 'calendar-clock',
                'title' => 'Belum Ada Transaksi Hari Ini',
                'message' => 'Jangan lupa catat pengeluaran dan pemasukan hari ini!',
                'action' => [
                    'text' => 'Tambah Transaksi',
                    'url' => route('transactions.create'),
                ],
            ];
        }

        // 2. Pengeluaran mendekati pemasukan (>80%)
        $ratio = $this->getExpenseIncomeRatio($user);
        if ($ratio >= 80 && $ratio < 100) {
            $alerts[] = [
                'key' => 'high_expense_ratio',
                'type' => 'warning',
                'icon' => 'alert-triangle',
                'title' => 'Pengeluaran Mendekati Pemasukan!',
                'message' => "Pengeluaran bulan ini sudah mencapai {$ratio}% dari pemasukan. Pertimbangkan untuk mengurangi pengeluaran.",
                'action' => [
                    'text' => 'Lihat Detail',
                    'url' => route('statistics.index'),
                ],
            ];
        }

        // 2b. Pengeluaran melebihi pemasukan (>100%)
        if ($ratio >= 100) {
            $alerts[] = [
                'key' => 'expense_exceeds_income',
                'type' => 'danger',
                'icon' => 'alert-circle',
                'title' => 'Pengeluaran Melebihi Pemasukan!',
                'message' => "Pengeluaran bulan ini sudah {$ratio}% dari pemasukan. Segera evaluasi pengeluaran Anda!",
                'action' => [
                    'text' => 'Lihat Statistik',
                    'url' => route('statistics.index'),
                ],
            ];
        }

        // 3. Belum ada pemasukan bulan ini (hanya tampil setelah tanggal 5)
        if (now()->day >= 5 && $this->hasNoIncomeThisMonth($user)) {
            $alerts[] = [
                'key' => 'no_income_this_month',
                'type' => 'info',
                'icon' => 'trending-down',
                'title' => 'Belum Ada Pemasukan Bulan Ini',
                'message' => 'Belum ada pemasukan tercatat untuk bulan ' . now()->format('F Y') . '.',
                'action' => [
                    'text' => 'Catat Pemasukan',
                    'url' => route('transactions.create', ['type' => 'income']),
                ],
            ];
        }

        // 4. Saldo negatif di salah satu dompet
        $negativeWallets = $this->getNegativeWallets($user);
        if ($negativeWallets->isNotEmpty()) {
            $walletNames = $negativeWallets->pluck('name')->take(2)->implode(', ');
            $moreCount = $negativeWallets->count() - 2;

            $message = "Dompet \"{$walletNames}\"";
            if ($moreCount > 0) {
                $message .= " dan {$moreCount} lainnya";
            }
            $message .= " memiliki saldo negatif. Segera isi ulang!";

            $alerts[] = [
                'key' => 'negative_balance',
                'type' => 'danger',
                'icon' => 'wallet',
                'title' => 'Saldo Dompet Negatif!',
                'message' => $message,
                'action' => [
                    'text' => 'Lihat Dompet',
                    'url' => route('wallets.index'),
                ],
            ];
        }

        // 5. Budget kategori terlampaui
        $overBudgetCategories = $this->getOverBudgetCategories($user);
        if ($overBudgetCategories->isNotEmpty()) {
            $categoryNames = $overBudgetCategories->pluck('name')->take(2)->implode(', ');
            $moreCount = $overBudgetCategories->count() - 2;

            $message = "Kategori \"{$categoryNames}\"";
            if ($moreCount > 0) {
                $message .= " dan {$moreCount} lainnya";
            }
            $message .= " sudah melebihi budget yang ditetapkan.";

            $alerts[] = [
                'key' => 'over_budget',
                'type' => 'warning',
                'icon' => 'alert-triangle',
                'title' => 'Budget Terlampaui!',
                'message' => $message,
                'action' => [
                    'text' => 'Lihat Budget',
                    'url' => route('budgets.index'),
                ],
            ];
        }

        // 6. Belum ada dompet aktif
        if ($this->hasNoActiveWallet($user)) {
            $alerts[] = [
                'key' => 'no_active_wallet',
                'type' => 'warning',
                'icon' => 'wallet',
                'title' => 'Belum Ada Dompet Aktif',
                'message' => 'Buat dompet untuk mulai mencatat transaksi keuangan.',
                'action' => [
                    'text' => 'Buat Dompet',
                    'url' => route('wallets.create'),
                ],
            ];
        }

        // Limit alerts to maximum 3 (prioritas: danger > warning > info)
        usort($alerts, function ($a, $b) {
            $priority = ['danger' => 1, 'warning' => 2, 'info' => 3];
            return ($priority[$a['type']] ?? 99) <=> ($priority[$b['type']] ?? 99);
        });

        return array_slice($alerts, 0, 3);
    }

    /**
     * Check if user has no transactions today
     */
    protected function hasNoTransactionsToday(User $user): bool
    {
        $count = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->where('wallets.user_id', $user->id)
            ->whereDate('transactions.transaction_date', today())
            ->count();

        return $count === 0;
    }

    /**
     * Get expense to income ratio (percentage)
     */
    protected function getExpenseIncomeRatio(User $user): float
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
            return round(($stats->expense / $stats->income) * 100, 0);
        }

        return 0;
    }

    /**
     * Check if user has no income this month
     */
    protected function hasNoIncomeThisMonth(User $user): bool
    {
        $count = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->where('wallets.user_id', $user->id)
            ->where('transactions.type', 'income')
            ->whereYear('transactions.transaction_date', now()->year)
            ->whereMonth('transactions.transaction_date', now()->month)
            ->count();

        return $count === 0;
    }

    /**
     * Get wallets with negative balance
     */
    protected function getNegativeWallets(User $user)
    {
        return $user->wallets()
            ->where('is_active', true)
            ->where('current_balance', '<', 0)
            ->get();
    }

    /**
     * Check if user has no active wallet
     */
    protected function hasNoActiveWallet(User $user): bool
    {
        return $user->wallets()->where('is_active', true)->count() === 0;
    }

    /**
     * Get categories that are over budget this month
     */
    protected function getOverBudgetCategories(User $user)
    {
        return DB::table('category_budgets')
            ->join('categories', 'category_budgets.category_id', '=', 'categories.id')
            ->where('categories.user_id', $user->id)
            ->where('category_budgets.month', now()->month)
            ->where('category_budgets.year', now()->year)
            ->whereRaw('(
                SELECT COALESCE(SUM(transactions.amount), 0)
                FROM transactions
                JOIN wallets ON transactions.wallet_id = wallets.id
                WHERE transactions.category_id = categories.id
                AND transactions.type = "expense"
                AND wallets.user_id = ?
                AND YEAR(transactions.transaction_date) = category_budgets.year
                AND MONTH(transactions.transaction_date) = category_budgets.month
            ) > category_budgets.monthly_limit', [$user->id])
            ->select('categories.id', 'categories.name')
            ->get();
    }
}
