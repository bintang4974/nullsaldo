<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'icon',
        'color',
        'type',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    /**
     * Get the user that owns the category
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get transactions for the category
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get budgets for the category
     */
    public function budgets()
    {
        return $this->hasMany(CategoryBudget::class);
    }

    /**
     * Get budget for specific month/year
     */
    public function budgetFor(int $month, int $year)
    {
        return $this->budgets()
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    }

    /**
     * Scope: Get expense categories
     */
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope: Get income categories
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }
}
