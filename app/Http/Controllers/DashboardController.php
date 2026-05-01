<?php

namespace App\Http\Controllers;

use App\Services\AlertService;
use App\Services\InsightService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected AlertService $alertService,
        protected InsightService $insightService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        // Get alerts and insights
        $alerts = $this->alertService->getAlerts($user);
        $insights = $this->insightService->generateInsights($user);

        // Get semua dompet aktif
        $wallets = $user->wallets()
            ->where('is_active', true)
            ->withCount('transactions')
            ->get();

        // Get transaksi terbaru (5 terakhir)
        $recentTransactions = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('wallets.user_id', $user->id)
            ->select(
                'transactions.*',
                'wallets.name as wallet_name',
                'categories.name as category_name',
                'categories.icon as category_icon',
                'categories.color as category_color'
            )
            ->orderBy('transactions.transaction_date', 'desc')
            ->orderBy('transactions.created_at', 'desc')
            ->limit(5)
            ->get();

        // Hitung total keseluruhan
        $totalBalance = $wallets->sum('current_balance');

        // Hitung income & expense bulan ini
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $monthlyStats = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->where('wallets.user_id', $user->id)
            ->whereYear('transactions.transaction_date', $currentYear)
            ->whereMonth('transactions.transaction_date', $currentMonth)
            ->select(
                'transactions.type',
                DB::raw('SUM(transactions.amount) as total')
            )
            ->groupBy('transactions.type')
            ->get()
            ->pluck('total', 'type');

        $monthlyIncome = $monthlyStats->get('income', 0);
        $monthlyExpense = $monthlyStats->get('expense', 0);

        // Data untuk chart (6 bulan terakhir)
        $chartData = $this->getChartData($user->id);

        return view('dashboard', compact(
            'wallets',
            'recentTransactions',
            'totalBalance',
            'monthlyIncome',
            'monthlyExpense',
            'chartData',
            'alerts',
            'insights'
        ));
    }

    /**
     * Get data untuk chart income vs expense (6 bulan terakhir)
     */
    protected function getChartData(int $userId): array
    {
        $months = [];
        $income = [];
        $expense = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->month;
            $year = $date->year;

            $months[] = $date->format('M Y');

            // Query income untuk bulan ini
            $monthIncome = DB::table('transactions')
                ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
                ->where('wallets.user_id', $userId)
                ->where('transactions.type', 'income')
                ->whereYear('transactions.transaction_date', $year)
                ->whereMonth('transactions.transaction_date', $month)
                ->sum('transactions.amount');

            // Query expense untuk bulan ini
            $monthExpense = DB::table('transactions')
                ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
                ->where('wallets.user_id', $userId)
                ->where('transactions.type', 'expense')
                ->whereYear('transactions.transaction_date', $year)
                ->whereMonth('transactions.transaction_date', $month)
                ->sum('transactions.amount');

            $income[] = (float) $monthIncome;
            $expense[] = (float) $monthExpense;
        }

        return [
            'labels' => $months,
            'income' => $income,
            'expense' => $expense,
        ];
    }
}
