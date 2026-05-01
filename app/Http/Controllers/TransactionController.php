<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    /**
     * Form tambah transaksi baru
     */
    public function create(Request $request)
    {
        $walletId = $request->input('wallet_id');

        // Get dompet user
        $wallets = $request->user()
            ->wallets()
            ->where('is_active', true)
            ->get();

        // Get kategori
        $categories = $request->user()->categories;

        // Jika ada wallet_id, ambil dompet tersebut
        $selectedWallet = $walletId
            ? $wallets->firstWhere('id', $walletId)
            : $wallets->first();

        return view('transactions.create', compact(
            'wallets',
            'categories',
            'selectedWallet'
        ));
    }

    /**
     * Simpan transaksi baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string|max:500',
            'transaction_date' => 'required|date',
        ]);

        // Get wallet dan pastikan milik user
        $wallet = Wallet::findOrFail($validated['wallet_id']);
        $this->authorize('view', $wallet);

        try {
            $transaction = $this->transactionService->createTransaction($wallet, $validated);

            return redirect()
                ->route('wallets.show', $wallet)
                ->with('success', 'Transaksi berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Form edit transaksi
     */
    public function edit(Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $wallet = $transaction->wallet;
        $categories = $wallet->user->categories;

        return view('transactions.edit', compact(
            'transaction',
            'wallet',
            'categories'
        ));
    }

    /**
     * Update transaksi
     */
    public function update(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string|max:500',
            'transaction_date' => 'required|date',
        ]);

        try {
            $this->transactionService->updateTransaction($transaction, $validated);

            return redirect()
                ->route('wallets.show', $transaction->wallet)
                ->with('success', 'Transaksi berhasil diupdate!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus transaksi
     */
    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        $wallet = $transaction->wallet;

        try {
            $this->transactionService->deleteTransaction($transaction);

            return redirect()
                ->route('wallets.show', $wallet)
                ->with('success', 'Transaksi berhasil dihapus!');
        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage());
        }
    }
}
