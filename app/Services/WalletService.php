<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Buat dompet baru untuk user
     */
    public function createWallet(User $user, array $data): Wallet
    {
        return DB::transaction(function () use ($user, $data) {
            $wallet = $user->wallets()->create([
                'name' => $data['name'],
                'initial_balance' => $data['initial_balance'] ?? 0,
                'current_balance' => $data['initial_balance'] ?? 0,
                'icon' => $data['icon'] ?? 'wallet',
                'color' => $data['color'] ?? '#3B82F6',
                'is_active' => true,
            ]);

            return $wallet;
        });
    }

    /**
     * Update dompet
     */
    public function updateWallet(Wallet $wallet, array $data): Wallet
    {
        return DB::transaction(function () use ($wallet, $data) {
            // Jika initial balance berubah, hitung ulang current balance
            if (isset($data['initial_balance']) && $data['initial_balance'] != $wallet->initial_balance) {
                $difference = $data['initial_balance'] - $wallet->initial_balance;
                $data['current_balance'] = $wallet->current_balance + $difference;
            }

            $wallet->update($data);
            return $wallet->fresh();
        });
    }

    /**
     * Hapus dompet (soft delete dengan is_active)
     */
    public function archiveWallet(Wallet $wallet): bool
    {
        return $wallet->update(['is_active' => false]);
    }

    /**
     * Transfer saldo antar dompet
     */
    public function transferBetweenWallets(
        Wallet $fromWallet,
        Wallet $toWallet,
        float $amount,
        string $description = null
    ): bool {
        if ($fromWallet->current_balance < $amount) {
            throw new \Exception('Saldo tidak mencukupi untuk transfer');
        }

        return DB::transaction(function () use ($fromWallet, $toWallet, $amount, $description) {
            // Kurangi saldo dari dompet asal
            $fromWallet->decrement('current_balance', $amount);

            // Tambah saldo ke dompet tujuan
            $toWallet->increment('current_balance', $amount);

            // Catat transaksi expense di dompet asal
            $fromWallet->transactions()->create([
                'type' => 'expense',
                'amount' => $amount,
                'description' => $description ?? "Transfer ke {$toWallet->name}",
                'transaction_date' => now(),
            ]);

            // Catat transaksi income di dompet tujuan
            $toWallet->transactions()->create([
                'type' => 'income',
                'amount' => $amount,
                'description' => $description ?? "Transfer dari {$fromWallet->name}",
                'transaction_date' => now(),
            ]);

            return true;
        });
    }

    /**
     * Get statistik untuk dompet
     */
    public function getWalletStatistics(Wallet $wallet, ?int $month = null, ?int $year = null): array
    {
        $query = $wallet->transactions();

        if ($month && $year) {
            $query->byMonth($month, $year);
        }

        $income = $query->clone()->income()->sum('amount');
        $expense = $query->clone()->expense()->sum('amount');

        return [
            'total_income' => $income,
            'total_expense' => $expense,
            'net_income' => $income - $expense,
            'current_balance' => $wallet->current_balance,
        ];
    }
}