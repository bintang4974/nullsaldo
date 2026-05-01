@extends('layouts.app')

@section('title', 'Export Data')

@section('content')
    <div class="max-w-2xl mx-auto pb-20 lg:pb-6">

        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center mb-2">
                <a href="{{ url()->previous() }}" class="mr-3 text-gray-600 hover:text-gray-900">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Export Data</h1>
            </div>
            <p class="text-sm text-gray-600">Download laporan transaksi dalam format PDF atau Excel</p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-xl shadow-md p-6">

            <!-- Info Box -->
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i data-lucide="info" class="w-5 h-5 text-blue-600 mr-3 mt-0.5"></i>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1">Tentang Export Data</p>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            <li><strong>PDF:</strong> Laporan terformat dengan ringkasan dan grafik</li>
                            <li><strong>Excel:</strong> Data mentah untuk analisis lebih lanjut</li>
                            <li>Pilih filter sesuai kebutuhan sebelum export</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form method="POST" id="exportForm" x-data="{ format: 'pdf' }">
                @csrf

                <!-- Format Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Format Export</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="format = 'pdf'"
                            :class="format === 'pdf' ? 'bg-red-600 text-white ring-2 ring-red-600' :
                                'bg-white text-gray-700 border-2 border-gray-200'"
                            class="flex items-center justify-center px-4 py-3 rounded-lg font-medium transition-all">
                            <i data-lucide="file-text" class="w-5 h-5 mr-2"></i>
                            PDF
                        </button>
                        <button type="button" @click="format = 'excel'"
                            :class="format === 'excel' ? 'bg-green-600 text-white ring-2 ring-green-600' :
                                'bg-white text-gray-700 border-2 border-gray-200'"
                            class="flex items-center justify-center px-4 py-3 rounded-lg font-medium transition-all">
                            <i data-lucide="file-spreadsheet" class="w-5 h-5 mr-2"></i>
                            Excel
                        </button>
                    </div>
                </div>

                <!-- Period Filter -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Periode <span
                            class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="month" class="block text-xs text-gray-600 mb-1">Bulan</label>
                            <select name="month" id="month" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label for="year" class="block text-xs text-gray-600 mb-1">Tahun</label>
                            <select name="year" id="year" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>
                                        {{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Wallet Filter -->
                <div class="mb-6">
                    <label for="wallet_id" class="block text-sm font-medium text-gray-700 mb-2">Dompet (Opsional)</label>
                    <select name="wallet_id" id="wallet_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Dompet</option>
                        @foreach ($wallets as $wallet)
                            <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Type Filter -->
                <div class="mb-6">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Jenis Transaksi
                        (Opsional)</label>
                    <select name="type" id="type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Jenis</option>
                        <option value="income">Pemasukan</option>
                        <option value="expense">Pengeluaran</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="mb-6">
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Kategori
                        (Opsional)</label>
                    <select name="category_id" id="category_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}
                                ({{ $category->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit Buttons -->
                <div class="flex space-x-3">
                    <button type="submit"
                        @click="document.getElementById('exportForm').action = (format === 'pdf' ? '{{ route('export.pdf') }}' : '{{ route('export.excel') }}')"
                        class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center justify-center">
                        <i data-lucide="download" class="w-5 h-5 mr-2"></i>
                        <span x-text="format === 'pdf' ? 'Download PDF' : 'Download Excel'"></span>
                    </button>
                    <a href="{{ url()->previous() }}"
                        class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors flex items-center justify-center">
                        Batal
                    </a>
                </div>

            </form>
        </div>

    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
@endsection
