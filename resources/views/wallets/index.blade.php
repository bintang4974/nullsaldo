@extends('layouts.app')

@section('title', 'Dompet Saya')

@section('content')
    <div class="max-w-7xl mx-auto pb-20 lg:pb-6">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dompet Saya</h1>
                <p class="text-sm text-gray-600 mt-1">Kelola semua dompet keuanganmu</p>
            </div>
            <a href="{{ route('wallets.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                Tambah Dompet
            </a>
        </div>

        @if ($wallets->isEmpty())
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-md p-12 text-center">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="wallet" class="w-10 h-10 text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Belum Ada Dompet</h3>
                <p class="text-gray-600 mb-6 max-w-md mx-auto">
                    Buat dompet pertamamu untuk mulai mencatat transaksi keuangan dengan rapi dan terstruktur.
                </p>
                <a href="{{ route('wallets.create') }}"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
                    Buat Dompet Baru
                </a>
            </div>
        @else
            <!-- Wallets Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($wallets as $wallet)
                    <div
                        class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">

                        <!-- Wallet Header -->
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-14 h-14 rounded-xl flex items-center justify-center mr-4"
                                        style="background-color: {{ $wallet->color }}20;">
                                        <i data-lucide="{{ $wallet->icon ?? 'wallet' }}" class="w-7 h-7"
                                            style="color: {{ $wallet->color }};"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg text-gray-900">{{ $wallet->name }}</h3>
                                        <div class="flex items-center mt-1">
                                            @if ($wallet->is_active)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                    <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                                    Aktif
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">
                                                    Diarsipkan
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Dropdown Menu -->
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open"
                                        class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                        <i data-lucide="more-vertical" class="w-5 h-5"></i>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-cloak
                                        class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10">
                                        <a href="{{ route('wallets.edit', $wallet) }}"
                                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <i data-lucide="edit" class="w-4 h-4 mr-3"></i>
                                            Edit Dompet
                                        </a>
                                        <form method="POST" action="{{ route('wallets.destroy', $wallet) }}"
                                            onsubmit="return confirm('Yakin ingin mengarsipkan dompet ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                <i data-lucide="archive" class="w-4 h-4 mr-3"></i>
                                                Arsipkan
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Balance -->
                            <div class="mb-3">
                                <p class="text-sm text-gray-600 mb-1">Saldo Saat Ini</p>
                                <p class="text-3xl font-bold text-gray-900">
                                    Rp {{ number_format($wallet->current_balance, 0, ',', '.') }}
                                </p>
                            </div>

                            <!-- Stats -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Saldo Awal</p>
                                    <p class="text-sm font-medium text-gray-900">
                                        Rp {{ number_format($wallet->initial_balance, 0, ',', '.') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Transaksi</p>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $wallet->transactions_count }} data
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Wallet Actions -->
                        <div class="p-4 bg-gray-50">
                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('wallets.show', $wallet) }}"
                                    class="inline-flex items-center justify-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                                    <i data-lucide="eye" class="w-4 h-4 mr-2"></i>
                                    Lihat Detail
                                </a>
                                <a href="{{ route('transactions.create', ['wallet_id' => $wallet->id]) }}"
                                    class="inline-flex items-center justify-center px-4 py-2 text-white text-sm font-medium rounded-lg transition-colors"
                                    style="background-color: {{ $wallet->color }};">
                                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                    Tambah
                                </a>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Summary -->
            <div class="mt-8 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90 mb-1">Total Saldo Semua Dompet</p>
                        <p class="text-3xl font-bold">
                            Rp {{ number_format($wallets->where('is_active', true)->sum('current_balance'), 0, ',', '.') }}
                        </p>
                        <p class="text-xs opacity-75 mt-2">
                            Dari {{ $wallets->where('is_active', true)->count() }} dompet aktif
                        </p>
                    </div>
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i data-lucide="wallet" class="w-8 h-8"></i>
                    </div>
                </div>
            </div>
        @endif

    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
@endsection
