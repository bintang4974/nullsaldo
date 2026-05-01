@extends('layouts.app')

@section('title', 'Statistik Keuangan')

@section('content')
    <div class="max-w-7xl mx-auto pb-20 lg:pb-6">

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Statistik Keuangan</h1>
            <p class="text-sm text-gray-600 mt-1">Analisis dan visualisasi keuangan Anda</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <!-- Wallet Filter -->
                <div>
                    <label for="wallet_id" class="block text-sm font-medium text-gray-700 mb-2">Dompet</label>
                    <select name="wallet_id" id="wallet_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Dompet</option>
                        @foreach ($wallets as $wallet)
                            <option value="{{ $wallet->id }}" {{ $walletId == $wallet->id ? 'selected' : '' }}>
                                {{ $wallet->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

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

                <!-- Filter Button -->
                <div class="sm:col-span-3">
                    <button type="submit"
                        class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i data-lucide="filter" class="w-4 h-4 inline mr-2"></i>
                        Terapkan Filter
                    </button>
                    <a href="{{ route('statistics.index') }}"
                        class="ml-2 inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="flex items-center justify-between mb-6">
            <h1>Statistik Keuangan</h1>
            <a href="{{ route('export.index') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg">
                <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                Export Data
            </a>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <!-- Total Income -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-600">Pemasukan</span>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="trending-up" class="w-5 h-5 text-green-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900 mb-1">
                    Rp {{ number_format($monthlyIncome, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::create($year, $month)->format('F Y') }}</p>
            </div>

            <!-- Total Expense -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-600">Pengeluaran</span>
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="trending-down" class="w-5 h-5 text-red-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900 mb-1">
                    Rp {{ number_format($monthlyExpense, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::create($year, $month)->format('F Y') }}</p>
            </div>

            <!-- Net Income -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-600">Sisa</span>
                    <div
                        class="w-10 h-10 {{ $netIncome >= 0 ? 'bg-blue-100' : 'bg-orange-100' }} rounded-lg flex items-center justify-center">
                        <i data-lucide="{{ $netIncome >= 0 ? 'wallet' : 'alert-circle' }}"
                            class="w-5 h-5 {{ $netIncome >= 0 ? 'text-blue-600' : 'text-orange-600' }}"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold {{ $netIncome >= 0 ? 'text-blue-600' : 'text-orange-600' }} mb-1">
                    {{ $netIncome >= 0 ? '+' : '' }} Rp {{ number_format($netIncome, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500">Income - Expense</p>
            </div>

            <!-- Savings Rate -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-600">Tingkat Tabungan</span>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="percent" class="w-5 h-5 text-purple-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900 mb-1">
                    {{ number_format($savingsRate, 1) }}%
                </p>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-purple-600 h-2 rounded-full" style="width: {{ min($savingsRate, 100) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            <!-- Daily Trend Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Tren Harian</h2>
                <div class="relative h-64 sm:h-72 lg:h-80">
                    <canvas id="dailyTrendChart"></canvas>
                </div>
            </div>

            <!-- Expense by Category (Pie Chart) -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Pengeluaran per Kategori</h2>
                <div class="relative h-64 sm:h-72 lg:h-80">
                    <canvas id="categoryPieChart"></canvas>
                </div>
            </div>

        </div>

        <!-- Monthly Comparison Chart -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Perbandingan 6 Bulan Terakhir</h2>
            <div class="relative h-72 sm:h-80 lg:h-96">
                <canvas id="monthlyComparisonChart"></canvas>
            </div>
        </div>

        <!-- Expense by Category Table -->
        <div class="bg-white rounded-xl shadow-md mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">Detail Pengeluaran per Kategori</h2>
            </div>

            @if ($expenseByCategory->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="pie-chart" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <p class="text-gray-600">Belum ada pengeluaran pada periode ini</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Transaksi</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Persentase
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($expenseByCategory as $category)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-normal md:whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3"
                                                style="background-color: {{ $category->color }}20;">
                                                <i data-lucide="{{ $category->icon ?? 'tag' }}" class="w-5 h-5"
                                                    style="color: {{ $category->color }};"></i>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">{{ $category->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-normal md:whitespace-nowrap text-right">
                                        <span class="text-sm font-bold text-red-600">
                                            Rp {{ number_format($category->total, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-normal md:whitespace-nowrap text-right">
                                        <span class="text-sm text-gray-600">{{ $category->count }} kali</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-normal md:whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end">
                                            <div class="w-24 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="h-2 rounded-full"
                                                    style="width: {{ $category->percentage }}%; background-color: {{ $category->color }};">
                                                </div>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ number_format($category->percentage, 1) }}%
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Two Columns: Top Expenses & Income Sources -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Top Expenses -->
            <div class="bg-white rounded-xl shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Top 10 Pengeluaran</h2>
                </div>

                @if ($topExpenses->isEmpty())
                    <div class="p-8 text-center">
                        <i data-lucide="trending-down" class="w-12 h-12 text-gray-400 mx-auto mb-2"></i>
                        <p class="text-sm text-gray-600">Belum ada pengeluaran</p>
                    </div>
                @else
                    <div class="p-6 space-y-3">
                        @foreach ($topExpenses as $expense)
                            <div
                                class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex items-center flex-1">
                                    @if ($expense->category_icon)
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3"
                                            style="background-color: {{ $expense->category_color }}20;">
                                            <i data-lucide="{{ $expense->category_icon }}" class="w-5 h-5"
                                                style="color: {{ $expense->category_color }};"></i>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                            <i data-lucide="arrow-up" class="w-5 h-5 text-red-600"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $expense->category_name ?? 'Pengeluaran' }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($expense->transaction_date)->format('d M Y') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-red-600">
                                        Rp {{ number_format($expense->amount, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Income Sources -->
            <div class="bg-white rounded-xl shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Sumber Pemasukan</h2>
                </div>

                @if ($incomeSources->isEmpty())
                    <div class="p-8 text-center">
                        <i data-lucide="trending-up" class="w-12 h-12 text-gray-400 mx-auto mb-2"></i>
                        <p class="text-sm text-gray-600">Belum ada pemasukan</p>
                    </div>
                @else
                    <div class="p-6 space-y-3">
                        @foreach ($incomeSources as $source)
                            <div
                                class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex items-center flex-1">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3"
                                        style="background-color: {{ $source->color }}20;">
                                        <i data-lucide="{{ $source->icon ?? 'dollar-sign' }}" class="w-5 h-5"
                                            style="color: {{ $source->color }};"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $source->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $source->count }} transaksi</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-green-600">
                                        Rp {{ number_format($source->total, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </div>

    <script>
        // Daily Trend Chart
        const dailyTrendCtx = document.getElementById('dailyTrendChart').getContext('2d');
        new Chart(dailyTrendCtx, {
            type: 'line',
            data: {
                labels: @json($dailyTrend['labels']),
                datasets: [{
                        label: 'Pemasukan',
                        data: @json($dailyTrend['income']),
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Pengeluaran',
                        data: @json($dailyTrend['expense']),
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
                        position: 'bottom'
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

        // Category Pie Chart
        const categoryPieCtx = document.getElementById('categoryPieChart').getContext('2d');
        const categoryPieChart = new Chart(categoryPieCtx, {
            type: 'doughnut',
            data: {
                labels: @json($expenseByCategory->pluck('name')),
                datasets: [{
                    data: @json($expenseByCategory->pluck('total')),
                    backgroundColor: @json($expenseByCategory->pluck('color')),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        // default, will be adjusted on init and resize
                        position: 'bottom',
                        labels: {
                            boxWidth: 15,
                            padding: 12,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return context.label + ': Rp ' + value.toLocaleString('id-ID') + ' (' +
                                    percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Adjust legend position for pie chart based on viewport width
        function updatePieLegend() {
            if (!categoryPieChart) return;
            const isLarge = window.innerWidth >= 1024; // lg breakpoint
            categoryPieChart.options.plugins.legend.position = isLarge ? 'right' : 'bottom';
            categoryPieChart.update();
        }
        updatePieLegend();
        window.addEventListener('resize', function() {
            // debounce simple
            clearTimeout(window._pieResizeTimer);
            window._pieResizeTimer = setTimeout(updatePieLegend, 150);
        });

        // Monthly Comparison Chart
        const monthlyComparisonCtx = document.getElementById('monthlyComparisonChart').getContext('2d');
        new Chart(monthlyComparisonCtx, {
            type: 'bar',
            data: {
                labels: @json($monthlyComparison['labels']),
                datasets: [{
                        label: 'Pemasukan',
                        data: @json($monthlyComparison['income']),
                        backgroundColor: '#10B981',
                        borderRadius: 8
                    },
                    {
                        label: 'Pengeluaran',
                        data: @json($monthlyComparison['expense']),
                        backgroundColor: '#EF4444',
                        borderRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
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

        // Initialize Lucide icons
        lucide.createIcons();
    </script>
@endsection
