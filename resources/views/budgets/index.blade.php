@extends('layouts.app')

@section('title', 'Budget Bulanan')

@section('content')
    <div class="max-w-6xl mx-auto pb-20 lg:pb-6">

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Budget Bulanan</h1>
            <p class="text-sm text-gray-600 mt-1">Kelola budget per kategori pengeluaran</p>
        </div>

        <!-- Period Filter & Actions -->
        <div class="bg-white rounded-xl shadow-md p-4 sm:p-5 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- Period Filter -->
                <form method="GET" class="flex items-center space-x-3">
                    <select name="month" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                    <select name="year" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        @for ($y = now()->year; $y >= now()->year - 2; $y--)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}
                            </option>
                        @endfor
                    </select>
                </form>

                <!-- Copy Budget Action -->
                <form method="POST" action="{{ route('budgets.copy') }}"
                    onsubmit="return confirm('Salin budget dari bulan sebelumnya?')">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                        <i data-lucide="copy" class="w-4 h-4 mr-2"></i>
                        Salin dari Bulan Lalu
                    </button>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Budget -->
            <div class="bg-white rounded-xl shadow-md p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600">Total Budget</span>
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="wallet" class="w-4 h-4 text-blue-600"></i>
                    </div>
                </div>
                <p class="text-xl font-bold text-gray-900">
                    Rp {{ number_format($summary['total_budget'], 0, ',', '.') }}
                </p>
            </div>

            <!-- Total Spending -->
            <div class="bg-white rounded-xl shadow-md p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600">Total Pengeluaran</span>
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="trending-up" class="w-4 h-4 text-red-600"></i>
                    </div>
                </div>
                <p class="text-xl font-bold text-gray-900">
                    Rp {{ number_format($summary['total_spending'], 0, ',', '.') }}
                </p>
            </div>

            <!-- Categories with Budget -->
            <div class="bg-white rounded-xl shadow-md p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600">Kategori Teratur</span>
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                    </div>
                </div>
                <p class="text-xl font-bold text-gray-900">
                    {{ $summary['categories_with_budget'] }} / {{ $categories->count() }}
                </p>
            </div>

            <!-- Over Budget -->
            <div class="bg-white rounded-xl shadow-md p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600">Melebihi Budget</span>
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-orange-600"></i>
                    </div>
                </div>
                <p class="text-xl font-bold text-gray-900">
                    {{ $summary['over_budget_count'] }} kategori
                </p>
            </div>
        </div>

        @if ($categories->isEmpty())
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-md p-12 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="tag" class="w-10 h-10 text-gray-400"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Belum Ada Kategori Pengeluaran</h3>
                <p class="text-gray-600 mb-6">Buat kategori pengeluaran terlebih dahulu untuk mengatur budget.</p>
                <a href="{{ route('categories.create') }}"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
                    Buat Kategori
                </a>
            </div>
        @else
            <!-- Budget Cards -->
            <div class="space-y-4">
                @foreach ($categories as $category)
                    <div class="bg-white rounded-xl shadow-md p-5 sm:p-6" x-data="{ editing: false, budget: {{ $category->budget?->monthly_limit ?? 0 }} }">

                        <!-- Category Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center flex-1">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-lg flex items-center justify-center mr-3 sm:mr-4"
                                    style="background-color: {{ $category->color }}20;">
                                    <i data-lucide="{{ $category->icon ?? 'tag' }}" class="w-6 h-6 sm:w-7 sm:h-7"
                                        style="color: {{ $category->color }};"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-900 text-base sm:text-lg">{{ $category->name }}</h3>
                                    <p class="text-xs sm:text-sm text-gray-500">
                                        Pengeluaran: Rp {{ number_format($category->spending, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Budget Edit Section -->
                            <div class="ml-4 flex-shrink-0">
                                <!-- View Mode -->
                                <div x-show="!editing" class="text-right">
                                    @if ($category->budget)
                                        <p class="text-xs sm:text-sm text-gray-600">Budget:</p>
                                        <p class="font-bold text-gray-900 text-sm sm:text-base">
                                            Rp {{ number_format($category->budget->monthly_limit, 0, ',', '.') }}
                                        </p>
                                        <button type="button" @click="editing = true"
                                            class="text-xs text-blue-600 hover:underline mt-1">
                                            Ubah
                                        </button>
                                    @else
                                        <p class="text-xs sm:text-sm text-gray-500 mb-2">Belum ada budget</p>
                                        <button type="button" @click="editing = true"
                                            class="text-xs sm:text-sm text-blue-600 hover:underline font-medium">
                                            + Atur Budget
                                        </button>
                                    @endif
                                </div>

                                <!-- Edit Mode -->
                                <form x-show="editing" x-cloak method="POST"
                                    action="{{ route('budgets.update', $category) }}"
                                    class="flex flex-col sm:flex-row items-end sm:items-center space-y-2 sm:space-y-0 sm:space-x-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="month" value="{{ $month }}">
                                    <input type="hidden" name="year" value="{{ $year }}">

                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Budget (Rp)</label>
                                        <input type="number" name="monthly_limit" x-model="budget" placeholder="0"
                                            required min="0" step="1000"
                                            class="w-32 px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="flex space-x-2">
                                        <button type="submit"
                                            class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                                            Simpan
                                        </button>
                                        <button type="button"
                                            @click="editing = false; budget = {{ $category->budget?->monthly_limit ?? 0 }}"
                                            class="px-3 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition-colors">
                                            Batal
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        @if ($category->budget)
                            @php
                                $barColor = match ($category->status) {
                                    'danger' => 'bg-red-500',
                                    'warning' => 'bg-yellow-500',
                                    'safe' => 'bg-green-500',
                                    default => 'bg-gray-400',
                                };

                                $textColor = match ($category->status) {
                                    'danger' => 'text-red-600',
                                    'warning' => 'text-yellow-600',
                                    'safe' => 'text-green-600',
                                    default => 'text-gray-600',
                                };
                            @endphp

                            <div class="mt-4">
                                <!-- Progress Info -->
                                <div class="flex items-center justify-between text-xs sm:text-sm mb-2">
                                    <span class="text-gray-600">Progress</span>
                                    <span class="font-bold {{ $textColor }}">
                                        {{ $category->percentage }}%
                                    </span>
                                </div>

                                <!-- Progress Bar -->
                                <div class="w-full bg-gray-200 rounded-full h-3 sm:h-4 overflow-hidden">
                                    <div class="{{ $barColor }} h-full rounded-full transition-all duration-500"
                                        style="width: {{ min($category->percentage, 100) }}%"></div>
                                </div>

                                <!-- Remaining Budget -->
                                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                                    <span class="text-xs sm:text-sm text-gray-600">
                                        @if ($category->remaining >= 0)
                                            Sisa Budget:
                                        @else
                                            Melebihi Budget:
                                        @endif
                                    </span>
                                    <span
                                        class="text-sm sm:text-base font-bold {{ $category->remaining >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                                        Rp {{ number_format(abs($category->remaining), 0, ',', '.') }}
                                    </span>
                                </div>

                                <!-- Delete Budget (Small Link) -->
                                <form method="POST" action="{{ route('budgets.destroy', $category) }}"
                                    onsubmit="return confirm('Hapus budget untuk kategori ini?')" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="month" value="{{ $month }}">
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <button type="submit" class="text-xs text-red-600 hover:underline">
                                        Hapus Budget
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Info Box -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-5">
                <div class="flex items-start">
                    <i data-lucide="info" class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <h3 class="text-sm font-medium text-blue-900 mb-2">Tips Mengatur Budget</h3>
                        <ul class="text-xs sm:text-sm text-blue-800 space-y-1">
                            <li>• Gunakan metode <strong>50/30/20</strong>: 50% kebutuhan, 30% keinginan, 20% tabungan</li>
                            <li>• Review budget setiap bulan dan sesuaikan jika perlu</li>
                            <li>• Gunakan fitur "Salin dari Bulan Lalu" untuk menghemat waktu</li>
                            <li>• Budget yang terlampaui akan muncul sebagai alert di dashboard</li>
                        </ul>
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
