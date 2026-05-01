<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Buat transaksi baru
     */
    public function createTransaction(Wallet $wallet, array $data): Transaction
    {
        // Validasi saldo untuk expense
        if ($data['type'] === 'expense' && $wallet->current_balance < $data['amount']) {
            throw new \Exception('Saldo tidak mencukupi untuk transaksi ini');
        }

        return DB::transaction(function () use ($wallet, $data) {
            // Buat transaksi
            $transaction = $wallet->transactions()->create([
                'category_id' => $data['category_id'] ?? null,
                'type' => $data['type'],
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'transaction_date' => $data['transaction_date'] ?? now(),
            ]);

            // Update saldo dompet
            $this->updateWalletBalance($wallet, $transaction);

            return $transaction;
        });
    }

    /**
     * Update transaksi
     */
    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $wallet = $transaction->wallet;

            // Revert balance dari transaksi lama
            $this->revertWalletBalance($wallet, $transaction);

            // Update transaksi
            $transaction->update([
                'category_id' => $data['category_id'] ?? $transaction->category_id,
                'type' => $data['type'] ?? $transaction->type,
                'amount' => $data['amount'] ?? $transaction->amount,
                'description' => $data['description'] ?? $transaction->description,
                'transaction_date' => $data['transaction_date'] ?? $transaction->transaction_date,
            ]);

            // Apply balance baru
            $this->updateWalletBalance($wallet, $transaction->fresh());

            return $transaction->fresh();
        });
    }

    /**
     * Hapus transaksi
     */
    public function deleteTransaction(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            $wallet = $transaction->wallet;

            // Revert balance
            $this->revertWalletBalance($wallet, $transaction);

            // Hapus transaksi
            return $transaction->delete();
        });
    }

    /**
     * Update saldo dompet berdasarkan transaksi
     */
    protected function updateWalletBalance(Wallet $wallet, Transaction $transaction): void
    {
        if ($transaction->type === 'income') {
            $wallet->increment('current_balance', $transaction->amount);
        } else {
            $wallet->decrement('current_balance', $transaction->amount);
        }
    }

    /**
     * Revert saldo dompet (untuk update/delete transaksi)
     */
    protected function revertWalletBalance(Wallet $wallet, Transaction $transaction): void
    {
        if ($transaction->type === 'income') {
            $wallet->decrement('current_balance', $transaction->amount);
        } else {
            $wallet->increment('current_balance', $transaction->amount);
        }
    }

    /**
     * Get transaksi dengan filter
     */
    public function getTransactions(
        Wallet $wallet,
        ?string $type = null,
        ?int $categoryId = null,
        ?int $month = null,
        ?int $year = null
    ) {
        $query = $wallet->transactions()->with('category')->latest('transaction_date');

        if ($type) {
            $query->where('type', $type);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($month && $year) {
            $query->byMonth($month, $year);
        }

        return $query->paginate(20);
    }

    /**
     * Get statistik pengeluaran per kategori
     */
    public function getExpenseByCategory(Wallet $wallet, ?int $month = null, ?int $year = null): array
    {
        $query = $wallet->transactions()
            ->with('category')
            ->where('type', 'expense');

        if ($month && $year) {
            $query->byMonth($month, $year);
        }

        return $query->get()
            ->groupBy('category_id')
            ->map(function ($transactions) {
                return [
                    'category' => $transactions->first()->category,
                    'total' => $transactions->sum('amount'),
                    'count' => $transactions->count(),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->toArray();
    }
}
