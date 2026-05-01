@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
    <div class="max-w-7xl mx-auto pb-20 lg:pb-6">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kategori Transaksi</h1>
                <p class="text-sm text-gray-600 mt-1">Kelola kategori pemasukan dan pengeluaran</p>
            </div>
            <a href="{{ route('categories.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                Tambah Kategori
            </a>
        </div>

        @if ($categories->isEmpty())
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-md p-12 text-center">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="tag" class="w-10 h-10 text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Belum Ada Kategori</h3>
                <p class="text-gray-600 mb-6 max-w-md mx-auto">
                    Buat kategori untuk mengorganisir transaksi keuanganmu dengan lebih baik.
                </p>
                <a href="{{ route('categories.create') }}"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
                    Buat Kategori Baru
                </a>
            </div>
        @else
            <!-- Kategori Pengeluaran -->
            @if ($categories->has('expense'))
                <div class="mb-8">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                            <i data-lucide="arrow-up-circle" class="w-5 h-5 text-red-600"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Kategori Pengeluaran</h2>
                            <p class="text-sm text-gray-600">{{ $categories->get('expense')->count() }} kategori</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach ($categories->get('expense') as $category)
                            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-5 hover:shadow-lg transition-shadow"
                                x-data="{ showMenu: false }">

                                <!-- Category Header -->
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center flex-1">
                                        <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-3"
                                            style="background-color: {{ $category->color }}20;">
                                            <i data-lucide="{{ $category->icon ?? 'tag' }}" class="w-6 h-6"
                                                style="color: {{ $category->color }};"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                                            <p class="text-xs text-gray-500">
                                                {{ $category->transactions_count }} transaksi
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Dropdown Menu -->
                                    <div class="relative">
                                        <button @click="showMenu = !showMenu"
                                            class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                            <i data-lucide="more-vertical" class="w-5 h-5"></i>
                                        </button>
                                        <div x-show="showMenu" @click.away="showMenu = false" x-cloak
                                            class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10">
                                            <a href="{{ route('categories.edit', $category) }}"
                                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <i data-lucide="edit" class="w-4 h-4 mr-3"></i>
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                                onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                    <i data-lucide="trash-2" class="w-4 h-4 mr-3"></i>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Category Color Badge -->
                                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 rounded-full border-2 border-gray-200"
                                            style="background-color: {{ $category->color }};"></div>
                                        <span class="text-xs text-gray-500 font-mono">{{ $category->color }}</span>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full">
                                        Expense
                                    </span>
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Kategori Pemasukan -->
            @if ($categories->has('income'))
                <div>
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i data-lucide="arrow-down-circle" class="w-5 h-5 text-green-600"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Kategori Pemasukan</h2>
                            <p class="text-sm text-gray-600">{{ $categories->get('income')->count() }} kategori</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach ($categories->get('income') as $category)
                            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-5 hover:shadow-lg transition-shadow"
                                x-data="{ showMenu: false }">

                                <!-- Category Header -->
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center flex-1">
                                        <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-3"
                                            style="background-color: {{ $category->color }}20;">
                                            <i data-lucide="{{ $category->icon ?? 'tag' }}" class="w-6 h-6"
                                                style="color: {{ $category->color }};"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                                            <p class="text-xs text-gray-500">
                                                {{ $category->transactions_count }} transaksi
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Dropdown Menu -->
                                    <div class="relative">
                                        <button @click="showMenu = !showMenu"
                                            class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                            <i data-lucide="more-vertical" class="w-5 h-5"></i>
                                        </button>
                                        <div x-show="showMenu" @click.away="showMenu = false" x-cloak
                                            class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10">
                                            <a href="{{ route('categories.edit', $category) }}"
                                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <i data-lucide="edit" class="w-4 h-4 mr-3"></i>
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                                onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                    <i data-lucide="trash-2" class="w-4 h-4 mr-3"></i>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Category Color Badge -->
                                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 rounded-full border-2 border-gray-200"
                                            style="background-color: {{ $category->color }};"></div>
                                        <span class="text-xs text-gray-500 font-mono">{{ $category->color }}</span>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                        Income
                                    </span>
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Info Box -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-5">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-900">Tips Menggunakan Kategori</h3>
                        <div class="mt-2 text-sm text-blue-800">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Buat kategori yang sesuai dengan kebutuhan keuanganmu</li>
                                <li>Gunakan warna yang berbeda untuk memudahkan identifikasi</li>
                                <li>Kategori dengan transaksi tidak bisa dihapus</li>
                                <li>Pilih icon yang representatif untuk setiap kategori</li>
                            </ul>
                        </div>
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
