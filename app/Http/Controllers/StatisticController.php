<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticController extends Controller
{
    /**
     * Display statistics page
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get selected wallet (default: all wallets)
        $walletId = $request->input('wallet_id');
        
        // Get selected period (default: current month)
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        
        // Get user's wallets
        $wallets = $user->wallets()
            ->where('is_active', true)
            ->get();
        
        // Build base query
        $transactionsQuery = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->where('wallets.user_id', $user->id)
            ->where('wallets.is_active', true);
        
        // Filter by wallet if specified
        if ($walletId) {
            $transactionsQuery->where('transactions.wallet_id', $walletId);
        }
        
        // Filter by month and year
        $transactionsQuery->whereYear('transactions.transaction_date', $year)
            ->whereMonth('transactions.transaction_date', $month);
        
        // Get monthly summary
        $monthlySummary = (clone $transactionsQuery)
            ->select(
                'transactions.type',
                DB::raw('SUM(transactions.amount) as total'),
                DB::raw('COUNT(transactions.id) as count')
            )
            ->groupBy('transactions.type')
            ->get()
            ->pluck('total', 'type');
        
        $monthlyIncome = $monthlySummary->get('income', 0);
        $monthlyExpense = $monthlySummary->get('expense', 0);
        $netIncome = $monthlyIncome - $monthlyExpense;
        
        // Get expense by category
        $expenseByCategory = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('wallets.user_id', $user->id)
            ->where('transactions.type', 'expense')
            ->whereYear('transactions.transaction_date', $year)
            ->whereMonth('transactions.transaction_date', $month);
        
        if ($walletId) {
            $expenseByCategory->where('transactions.wallet_id', $walletId);
        }
        
        $expenseByCategory = $expenseByCategory
            ->select(
                'categories.id',
                'categories.name',
                'categories.icon',
                'categories.color',
                DB::raw('SUM(transactions.amount) as total'),
                DB::raw('COUNT(transactions.id) as count')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.icon', 'categories.color')
            ->orderBy('total', 'desc')
            ->get();
        
        // Calculate percentage for each category
        $totalExpense = $expenseByCategory->sum('total');
        $expenseByCategory = $expenseByCategory->map(function ($item) use ($totalExpense) {
            $item->percentage = $totalExpense > 0 ? ($item->total / $totalExpense) * 100 : 0;
            return $item;
        });
        
        // Get daily trend (for selected month)
        $dailyTrend = $this->getDailyTrend($user->id, $walletId, $month, $year);
        
        // Get monthly comparison (last 6 months)
        $monthlyComparison = $this->getMonthlyComparison($user->id, $walletId);
        
        // Get top expenses
        $topExpenses = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('wallets.user_id', $user->id)
            ->where('transactions.type', 'expense')
            ->whereYear('transactions.transaction_date', $year)
            ->whereMonth('transactions.transaction_date', $month);
        
        if ($walletId) {
            $topExpenses->where('transactions.wallet_id', $walletId);
        }
        
        $topExpenses = $topExpenses
            ->select(
                'transactions.*',
                'categories.name as category_name',
                'categories.icon as category_icon',
                'categories.color as category_color',
                'wallets.name as wallet_name'
            )
            ->orderBy('transactions.amount', 'desc')
            ->limit(10)
            ->get();
        
        // Get income sources
        $incomeSources = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('wallets.user_id', $user->id)
            ->where('transactions.type', 'income')
            ->whereYear('transactions.transaction_date', $year)
            ->whereMonth('transactions.transaction_date', $month);
        
        if ($walletId) {
            $incomeSources->where('transactions.wallet_id', $walletId);
        }
        
        $incomeSources = $incomeSources
            ->select(
                'categories.id',
                'categories.name',
                'categories.icon',
                'categories.color',
                DB::raw('SUM(transactions.amount) as total'),
                DB::raw('COUNT(transactions.id) as count')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.icon', 'categories.color')
            ->orderBy('total', 'desc')
            ->get();
        
        // Calculate savings rate
        $savingsRate = $monthlyIncome > 0 ? (($monthlyIncome - $monthlyExpense) / $monthlyIncome) * 100 : 0;
        
        return view('statistics.index', compact(
            'wallets',
            'walletId',
            'month',
            'year',
            'monthlyIncome',
            'monthlyExpense',
            'netIncome',
            'expenseByCategory',
            'dailyTrend',
            'monthlyComparison',
            'topExpenses',
            'incomeSources',
            'savingsRate'
        ));
    }
    
    /**
     * Get daily trend data for the selected month
     */
    protected function getDailyTrend(int $userId, $walletId, int $month, int $year): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $query = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->where('wallets.user_id', $userId)
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate]);
        
        if ($walletId) {
            $query->where('transactions.wallet_id', $walletId);
        }
        
        $dailyData = $query
            ->select(
                DB::raw('DATE(transactions.transaction_date) as date'),
                'transactions.type',
                DB::raw('SUM(transactions.amount) as total')
            )
            ->groupBy('date', 'transactions.type')
            ->get();
        
        // Format data untuk chart
        $dates = [];
        $income = [];
        $expense = [];
        
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $dates[] = $currentDate->format('d M');
            
            $dayIncome = $dailyData->where('date', $dateStr)->where('type', 'income')->sum('total');
            $dayExpense = $dailyData->where('date', $dateStr)->where('type', 'expense')->sum('total');
            
            $income[] = (float) $dayIncome;
            $expense[] = (float) $dayExpense;
            
            $currentDate->addDay();
        }
        
        return [
            'labels' => $dates,
            'income' => $income,
            'expense' => $expense,
        ];
    }
    
    /**
     * Get monthly comparison for last 6 months
     */
    protected function getMonthlyComparison(int $userId, $walletId): array
    {
        $months = [];
        $income = [];
        $expense = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->month;
            $year = $date->year;
            
            $months[] = $date->format('M Y');
            
            $query = DB::table('transactions')
                ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
                ->where('wallets.user_id', $userId)
                ->whereYear('transactions.transaction_date', $year)
                ->whereMonth('transactions.transaction_date', $month);
            
            if ($walletId) {
                $query->where('transactions.wallet_id', $walletId);
            }
            
            $monthData = $query
                ->select(
                    'transactions.type',
                    DB::raw('SUM(transactions.amount) as total')
                )
                ->groupBy('transactions.type')
                ->get()
                ->pluck('total', 'type');
            
            $income[] = (float) $monthData->get('income', 0);
            $expense[] = (float) $monthData->get('expense', 0);
        }
        
        return [
            'labels' => $months,
            'income' => $income,
            'expense' => $expense,
        ];
    }
}
