@extends('layouts.app')

@section('title', $wallet->name)

@section('content')
    <div class="max-w-7xl mx-auto pb-20 lg:pb-6">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <a href="{{ route('wallets.index') }}" class="mr-3 text-gray-600 hover:text-gray-900">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $wallet->name }}</h1>
                    <p class="text-sm text-gray-600 mt-1">Detail dompet dan riwayat transaksi</p>
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('wallets.edit', $wallet) }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                    Edit
                </a>
                <a href="{{ route('transactions.create', ['wallet_id' => $wallet->id]) }}"
                    class="inline-flex items-center px-4 py-2 text-white text-sm font-medium rounded-lg hover:opacity-90 transition-colors"
                    style="background-color: {{ $wallet->color }};">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Transaksi
                </a>
                <a href="{{ route('export.index', ['wallet_id' => $wallet->id]) }}"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg">
                    <i data-lucide="file-down" class="w-4 h-4 mr-2"></i>
                    Export
                </a>
            </div>
        </div>

        <!-- Wallet Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Current Balance -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-600">Saldo Saat Ini</span>
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                        style="background-color: {{ $wallet->color }}20;">
                        <i data-lucide="{{ $wallet->icon ?? 'wallet' }}" class="w-5 h-5"
                            style="color: {{ $wallet->color }};"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900 mb-1">
                    Rp {{ number_format($wallet->current_balance, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500">
                    Saldo awal: Rp {{ number_format($wallet->initial_balance, 0, ',', '.') }}
                </p>
            </div>

            <!-- Total Income -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-600">Total Pemasukan</span>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="trending-up" class="w-5 h-5 text-green-600"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-green-600 mb-1">
                    Rp {{ number_format($stats['total_income'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500">
                    {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
                </p>
            </div>

            <!-- Total Expense -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-600">Total Pengeluaran</span>
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="trending-down" class="w-5 h-5 text-red-600"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-red-600 mb-1">
                    Rp {{ number_format($stats['total_expense'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500">
                    {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
                </p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Month Filter -->
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                    <select name="month" id="month"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- Year Filter -->
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <select name="year" id="year"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @for ($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- Type Filter -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Jenis</label>
                    <select name="type" id="type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua</option>
                        <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Pemasukan</option>
                        <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                    <select name="category_id" id="category_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Button -->
                <div class="sm:col-span-2 lg:col-span-4">
                    <button type="submit"
                        class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i data-lucide="filter" class="w-4 h-4 inline mr-2"></i>
                        Terapkan Filter
                    </button>
                    <a href="{{ route('wallets.show', $wallet) }}"
                        class="ml-2 inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Transactions List -->
        <div class="bg-white rounded-xl shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">Riwayat Transaksi</h2>
                <p class="text-sm text-gray-600 mt-1">{{ $transactions->total() }} transaksi ditemukan</p>
            </div>

            @if ($transactions->isEmpty())
                <!-- Empty State -->
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Transaksi</h3>
                    <p class="text-gray-600 mb-6">Mulai catat transaksi pertamamu di dompet ini</p>
                    <a href="{{ route('transactions.create', ['wallet_id' => $wallet->id]) }}"
                        class="inline-flex items-center px-6 py-3 text-white font-medium rounded-lg hover:opacity-90 transition-colors"
                        style="background-color: {{ $wallet->color }};">
                        <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
                        Tambah Transaksi
                    </a>
                </div>
            @else
                <!-- Transactions Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Deskripsi</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jumlah</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($transactions as $transaction)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $transaction->transaction_date->format('d M Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $transaction->transaction_date->format('H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($transaction->category)
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-2"
                                                    style="background-color: {{ $transaction->category->color }}20;">
                                                    <i data-lucide="{{ $transaction->category->icon ?? 'tag' }}"
                                                        class="w-4 h-4"
                                                        style="color: {{ $transaction->category->color }};"></i>
                                                </div>
                                                <span
                                                    class="text-sm text-gray-900">{{ $transaction->category->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">Tanpa Kategori</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{ $transaction->description ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div
                                            class="text-sm font-bold {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transaction->type === 'income' ? '+' : '-' }}
                                            Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('transactions.edit', $transaction) }}"
                                                class="text-blue-600 hover:text-blue-900">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                            </a>
                                            <form method="POST"
                                                action="{{ route('transactions.destroy', $transaction) }}"
                                                onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($transactions->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $transactions->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
@endsection
