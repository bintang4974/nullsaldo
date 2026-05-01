<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryBudget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    /**
     * Display budget management page
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Get expense categories with budgets
        $categories = $user->categories()
            ->where('type', 'expense')
            ->with(['budgets' => function ($query) use ($month, $year) {
                $query->where('month', $month)
                    ->where('year', $year);
            }])
            ->orderBy('name')
            ->get();

        // Calculate spending for each category
        $categories->each(function ($category) use ($month, $year, $user) {
            $spending = DB::table('transactions')
                ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
                ->where('wallets.user_id', $user->id)
                ->where('transactions.category_id', $category->id)
                ->where('transactions.type', 'expense')
                ->whereYear('transactions.transaction_date', $year)
                ->whereMonth('transactions.transaction_date', $month)
                ->sum('transactions.amount');

            $category->spending = (float) $spending;
            $category->budget = $category->budgets->first();

            // Calculate stats
            if ($category->budget) {
                $category->remaining = $category->budget->monthly_limit - $category->spending;
                $category->percentage = $category->budget->monthly_limit > 0
                    ? round(($category->spending / $category->budget->monthly_limit) * 100, 1)
                    : 0;
                $category->status = $this->getStatus($category->percentage);
            } else {
                $category->remaining = 0;
                $category->percentage = 0;
                $category->status = 'none';
            }
        });

        // Calculate summary
        $summary = [
            'total_budget' => $categories->sum(fn($c) => $c->budget?->monthly_limit ?? 0),
            'total_spending' => $categories->sum('spending'),
            'categories_with_budget' => $categories->filter(fn($c) => $c->budget)->count(),
            'over_budget_count' => $categories->filter(fn($c) => $c->status === 'danger')->count(),
        ];

        return view('budgets.index', compact('categories', 'month', 'year', 'summary'));
    }

    /**
     * Update or create budget for a category
     */
    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);

        $validated = $request->validate([
            'monthly_limit' => 'required|numeric|min:0',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        // Only allow budget for expense categories
        if ($category->type !== 'expense') {
            return back()->with('error', 'Budget hanya bisa diatur untuk kategori pengeluaran.');
        }

        try {
            CategoryBudget::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'month' => $validated['month'],
                    'year' => $validated['year'],
                ],
                [
                    'monthly_limit' => $validated['monthly_limit'],
                ]
            );

            return back()->with('success', 'Budget berhasil diatur!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan budget: ' . $e->getMessage());
        }
    }

    /**
     * Delete budget
     */
    public function destroy(Request $request, Category $category)
    {
        $this->authorize('update', $category);

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        CategoryBudget::where('category_id', $category->id)
            ->where('month', $month)
            ->where('year', $year)
            ->delete();

        return back()->with('success', 'Budget berhasil dihapus!');
    }

    /**
     * Copy budget from previous month
     */
    public function copyFromPreviousMonth(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        $currentMonth = $validated['month'];
        $currentYear = $validated['year'];

        // Calculate previous month
        if ($currentMonth == 1) {
            $previousMonth = 12;
            $previousYear = $currentYear - 1;
        } else {
            $previousMonth = $currentMonth - 1;
            $previousYear = $currentYear;
        }

        // Get budgets from previous month
        $previousBudgets = CategoryBudget::whereHas('category', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('month', $previousMonth)
            ->where('year', $previousYear)
            ->get();

        if ($previousBudgets->isEmpty()) {
            return back()->with('error', 'Tidak ada budget di bulan sebelumnya untuk disalin.');
        }

        $copiedCount = 0;

        DB::transaction(function () use ($previousBudgets, $currentMonth, $currentYear, &$copiedCount) {
            foreach ($previousBudgets as $budget) {
                CategoryBudget::updateOrCreate(
                    [
                        'category_id' => $budget->category_id,
                        'month' => $currentMonth,
                        'year' => $currentYear,
                    ],
                    [
                        'monthly_limit' => $budget->monthly_limit,
                    ]
                );
                $copiedCount++;
            }
        });

        return back()->with('success', "Berhasil menyalin {$copiedCount} budget dari bulan sebelumnya!");
    }

    /**
     * Get status based on percentage
     */
    protected function getStatus(float $percentage): string
    {
        if ($percentage >= 100) {
            return 'danger';
        }

        if ($percentage >= 80) {
            return 'warning';
        }

        return 'safe';
    }
}
