<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * Tampilkan semua dompet user
     */
    public function index(Request $request)
    {
        $wallets = $request->user()
            ->wallets()
            ->withCount('transactions')
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('wallets.index', compact('wallets'));
    }

    /**
     * Form tambah dompet baru
     */
    public function create()
    {
        return view('wallets.create');
    }

    /**
     * Simpan dompet baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'initial_balance' => 'required|numeric|min:0',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
        ]);

        $wallet = $this->walletService->createWallet(
            $request->user(),
            $validated
        );

        return redirect()
            ->route('wallets.show', $wallet)
            ->with('success', 'Dompet berhasil dibuat!');
    }

    /**
     * Tampilkan detail dompet & transaksinya
     */
    public function show(Request $request, Wallet $wallet)
    {
        // Pastikan user hanya bisa akses dompetnya sendiri
        $this->authorize('view', $wallet);

        // Get filter dari request
        $type = $request->input('type');
        $categoryId = $request->input('category_id');
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Get transaksi dengan filter
        $query = $wallet->transactions()
            ->with('category')
            ->latest('transaction_date')
            ->latest('created_at');

        if ($type) {
            $query->where('type', $type);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($month && $year) {
            $query->byMonth($month, $year);
        }

        $transactions = $query->paginate(20);

        // Get categories untuk filter
        $categories = $request->user()->categories;

        // Get statistik
        $stats = $this->walletService->getWalletStatistics($wallet, $month, $year);

        return view('wallets.show', compact(
            'wallet',
            'transactions',
            'categories',
            'stats',
            'month',
            'year'
        ));
    }

    /**
     * Form edit dompet
     */
    public function edit(Wallet $wallet)
    {
        $this->authorize('update', $wallet);
        
        return view('wallets.edit', compact('wallet'));
    }

    /**
     * Update dompet
     */
    public function update(Request $request, Wallet $wallet)
    {
        $this->authorize('update', $wallet);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'initial_balance' => 'required|numeric|min:0',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $this->walletService->updateWallet($wallet, $validated);

        return redirect()
            ->route('wallets.show', $wallet)
            ->with('success', 'Dompet berhasil diupdate!');
    }

    /**
     * Arsipkan dompet (soft delete)
     */
    public function destroy(Wallet $wallet)
    {
        $this->authorize('delete', $wallet);

        $this->walletService->archiveWallet($wallet);

        return redirect()
            ->route('wallets.index')
            ->with('success', 'Dompet berhasil diarsipkan!');
    }
}
