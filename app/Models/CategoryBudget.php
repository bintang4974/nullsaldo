<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CategoryBudget extends Model
{
    protected $fillable = [
        'category_id',
        'monthly_limit',
        'month',
        'year',
    ];

    protected $casts = [
        'monthly_limit' => 'decimal:2',
        'month' => 'integer',
        'year' => 'integer',
    ];

    protected $appends = [
        'spending',
        'remaining',
        'percentage',
        'status',
    ];

    /**
     * Relasi ke Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get total spending untuk budget ini
     */
    public function getSpendingAttribute(): float
    {
        $spending = DB::table('transactions')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->where('transactions.category_id', $this->category_id)
            ->where('transactions.type', 'expense')
            ->whereYear('transactions.transaction_date', $this->year)
            ->whereMonth('transactions.transaction_date', $this->month)
            ->sum('transactions.amount');

        return (float) $spending;
    }

    /**
     * Get remaining budget
     */
    public function getRemainingAttribute(): float
    {
        return $this->monthly_limit - $this->spending;
    }

    /**
     * Get percentage used (0-100+)
     */
    public function getPercentageAttribute(): float
    {
        if ($this->monthly_limit == 0) {
            return $this->spending > 0 ? 100 : 0;
        }

        return round(($this->spending / $this->monthly_limit) * 100, 1);
    }

    /**
     * Get status based on percentage
     * - safe: 0-79%
     * - warning: 80-99%
     * - danger: 100%+
     */
    public function getStatusAttribute(): string
    {
        $percentage = $this->percentage;

        if ($percentage >= 100) {
            return 'danger';
        }

        if ($percentage >= 80) {
            return 'warning';
        }

        return 'safe';
    }

    /**
     * Get color based on status
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'danger' => '#EF4444',
            'warning' => '#F59E0B',
            'safe' => '#10B981',
            default => '#6B7280',
        };
    }

    /**
     * Check if budget is over limit
     */
    public function isOverBudget(): bool
    {
        return $this->spending > $this->monthly_limit;
    }

    /**
     * Check if budget is near limit (>= 80%)
     */
    public function isNearLimit(): bool
    {
        return $this->percentage >= 80 && $this->percentage < 100;
    }

    /**
     * Scope: Get budgets for specific month/year
     */
    public function scopeForPeriod($query, int $month, int $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    /**
     * Scope: Get budgets that are over limit
     */
    public function scopeOverBudget($query)
    {
        return $query->whereRaw('(
            SELECT SUM(transactions.amount)
            FROM transactions
            JOIN wallets ON transactions.wallet_id = wallets.id
            WHERE transactions.category_id = category_budgets.category_id
            AND transactions.type = "expense"
            AND YEAR(transactions.transaction_date) = category_budgets.year
            AND MONTH(transactions.transaction_date) = category_budgets.month
        ) > category_budgets.monthly_limit');
    }
}
