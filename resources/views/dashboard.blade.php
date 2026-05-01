@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="max-w-7xl mx-auto pb-20 lg:pb-6">

        <!-- Alerts Section -->
        <x-alert-banner :alerts="$alerts" />

        <!-- Insights Section -->
        <x-insight-card :insights="$insights" />

        <a href="{{ route('budgets.index') }}" class="inline-flex items-center mb-6 text-sm text-yellow-600 hover:text-yellow-700 font-medium">
            <i data-lucide="target"></i>
            Budget Bulanan
        </a>

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Halo, {{ auth()->user()->name }}! 👋</h1>
            <p class="text-sm text-gray-600 mt-1">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Total Saldo -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium opacity-90">Total Saldo</span>
                    <i data-lucide="wallet" class="w-5 h-5 opacity-75"></i>
                </div>
                <p class="text-3xl font-bold mb-1">Rp {{ number_format($totalBalance, 0, ',', '.') }}</p>
                <p class="text-xs opacity-75">Dari {{ $wallets->count() }} dompet</p>
            </div>

            <!-- Pemasukan Bulan Ini -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-600">Pemasukan</span>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="trending-up" class="w-5 h-5 text-green-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900 mb-1">Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500">{{ now()->format('F Y') }}</p>
            </div>

            <!-- Pengeluaran Bulan Ini -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-600">Pengeluaran</span>
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="trending-down" class="w-5 h-5 text-red-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900 mb-1">Rp {{ number_format($monthlyExpense, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500">{{ now()->format('F Y') }}</p>
            </div>
        </div>

        <!-- Dompet Aktif -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">Dompet Saya</h2>
                <a href="{{ route('wallets.create') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    + Tambah Dompet
                </a>
            </div>

            @if ($wallets->isEmpty())
                <div class="bg-white rounded-xl shadow-md p-8 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="wallet" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Dompet</h3>
                    <p class="text-sm text-gray-600 mb-4">Buat dompet pertamamu untuk mulai mencatat keuangan</p>
                    <a href="{{ route('wallets.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Buat Dompet Baru
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($wallets as $wallet)
                        <a href="{{ route('wallets.show', $wallet) }}"
                            class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow p-6 border border-gray-100 group">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-3"
                                        style="background-color: {{ $wallet->color }}20;">
                                        <i data-lucide="{{ $wallet->icon ?? 'wallet' }}" class="w-6 h-6"
                                            style="color: {{ $wallet->color }};"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                            {{ $wallet->name }}
                                        </h3>
                                        <p class="text-xs text-gray-500">{{ $wallet->transactions_count }} transaksi</p>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-100">
                                <p class="text-sm text-gray-600 mb-1">Saldo Saat Ini</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    Rp {{ number_format($wallet->current_balance, 0, ',', '.') }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <a href="{{ route('transactions.create') }}?type=income"
                class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium">Tambah</span>
                    <i data-lucide="arrow-down-circle" class="w-6 h-6"></i>
                </div>
                <p class="text-lg font-bold">Pemasukan</p>
            </a>

            <a href="{{ route('transactions.create') }}?type=expense"
                class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium">Tambah</span>
                    <i data-lucide="arrow-up-circle" class="w-6 h-6"></i>
                </div>
                <p class="text-lg font-bold">Pengeluaran</p>
            </a>
        </div>

        <!-- Transaksi Terkini -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">Transaksi Terkini</h2>
            </div>

            @if ($recentTransactions->isEmpty())
                <div class="text-center py-8">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="receipt" class="w-6 h-6 text-gray-400"></i>
                    </div>
                    <p class="text-sm text-gray-600">Belum ada transaksi</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($recentTransactions as $transaction)
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                            <div class="flex items-center flex-1">
                                <div
                                    class="w-10 h-10 rounded-lg flex items-center justify-center mr-3 {{ $transaction->type === 'income' ? 'bg-green-100' : 'bg-red-100' }}">
                                    @if ($transaction->category_icon)
                                        <i data-lucide="{{ $transaction->category_icon }}"
                                            class="w-5 h-5 {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}"></i>
                                    @else
                                        <i data-lucide="{{ $transaction->type === 'income' ? 'arrow-down' : 'arrow-up' }}"
                                            class="w-5 h-5 {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}"></i>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $transaction->category_name ?? ($transaction->type === 'income' ? 'Pemasukan' : 'Pengeluaran') }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $transaction->wallet_name }} •
                                        {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p
                                    class="text-sm font-bold {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $transaction->type === 'income' ? '+' : '-' }} Rp
                                    {{ number_format($transaction->amount, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Chart -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Grafik Keuangan (6 Bulan Terakhir)</h2>
            <div class="relative" style="height: 300px;">
                <canvas id="financialChart"></canvas>
            </div>
        </div>

    </div>

    <script>
        // Chart configuration
        const ctx = document.getElementById('financialChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartData['labels']),
                datasets: [{
                        label: 'Pemasukan',
                        data: @json($chartData['income']),
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Pengeluaran',
                        data: @json($chartData['expense']),
                        borderColor: '#EF4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        // Initialize icons after chart
        lucide.createIcons();
    </script>
@endsection
