@extends('layouts.app')

@section('title', 'Buat Dompet Baru')

@section('content')
    <div class="max-w-2xl mx-auto pb-20 lg:pb-6">

        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center mb-2">
                <a href="{{ route('wallets.index') }}" class="mr-3 text-gray-600 hover:text-gray-900">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Buat Dompet Baru</h1>
            </div>
            <p class="text-sm text-gray-600">Pisahkan keuanganmu dengan membuat dompet baru</p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-xl shadow-md p-6" x-data="walletForm()">
            <form method="POST" action="{{ route('wallets.store') }}">
                @csrf

                <!-- Wallet Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Dompet <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" required
                        placeholder="Contoh: Dompet Bulanan, Tabungan, Dana Darurat" value="{{ old('name') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Berikan nama yang mudah diingat untuk dompet ini</p>
                </div>

                <!-- Initial Balance -->
                <div class="mb-6">
                    <label for="initial_balance" class="block text-sm font-medium text-gray-700 mb-2">
                        Saldo Awal <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-500">Rp</span>
                        </div>
                        <input type="number" name="initial_balance" id="initial_balance" required step="0.01"
                            min="0" placeholder="0" value="{{ old('initial_balance', 0) }}"
                            @input="initialBalance = $event.target.value"
                            class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    @error('initial_balance')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Masukkan jumlah uang yang ada di dompet saat ini</p>
                </div>

                <!-- Icon Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Pilih Icon
                    </label>
                    <div class="grid grid-cols-6 sm:grid-cols-8 gap-3">
                        <template x-for="iconOption in icons" :key="iconOption">
                            <button type="button" @click="icon = iconOption"
                                :class="icon === iconOption ? 'bg-blue-100 border-blue-500 ring-2 ring-blue-500' :
                                    'bg-gray-50 border-gray-200 hover:bg-gray-100'"
                                class="aspect-square flex items-center justify-center border-2 rounded-lg transition-all p-3">
                                <i :data-lucide="iconOption"
                                    :class="icon === iconOption ? 'text-blue-600' : 'text-gray-600'" class="w-6 h-6"></i>
                            </button>
                        </template>
                    </div>
                    <input type="hidden" name="icon" x-model="icon">
                    <p class="mt-2 text-xs text-gray-500">Icon yang dipilih: <span x-text="icon || 'wallet'"
                            class="font-medium"></span></p>
                </div>

                <!-- Color Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Pilih Warna
                    </label>
                    <div class="grid grid-cols-6 sm:grid-cols-10 gap-3">
                        <template x-for="colorOption in colors" :key="colorOption">
                            <button type="button" @click="color = colorOption"
                                :class="color === colorOption ? 'ring-2 ring-offset-2 ring-gray-400 scale-110' : ''"
                                class="aspect-square rounded-lg transition-all hover:scale-105"
                                :style="`background-color: ${colorOption}`">
                            </button>
                        </template>
                    </div>
                    <input type="hidden" name="color" x-model="color" required>
                    <p class="mt-2 text-xs text-gray-500">Warna yang dipilih: <span x-text="color"
                            class="font-mono font-medium"></span></p>
                </div>

                <!-- Preview -->
                <div
                    class="mb-6 p-5 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border-2 border-dashed border-gray-300">
                    <p class="text-sm font-medium text-gray-700 mb-4">Preview Dompet:</p>
                    <div class="bg-white rounded-xl shadow-md p-5">
                        <div class="flex items-start mb-4">
                            <div class="w-14 h-14 rounded-xl flex items-center justify-center mr-4"
                                :style="`background-color: ${color}20`">
                                <i :data-lucide="icon || 'wallet'" class="w-7 h-7" :style="`color: ${color}`"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900"
                                    x-text="document.getElementById('name').value || 'Nama Dompet'"></h3>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 rounded-full mt-1">
                                    Aktif
                                </span>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-gray-100">
                            <p class="text-sm text-gray-600 mb-1">Saldo Awal</p>
                            <p class="text-2xl font-bold text-gray-900" x-text="formatCurrency(initialBalance)"></p>
                        </div>
                    </div>
                </div>

                <!-- Tips Box -->
                <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i data-lucide="lightbulb" class="w-5 h-5 text-blue-600 mr-3 mt-0.5"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-medium mb-1">Tips Membuat Dompet:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Buat dompet terpisah untuk kebutuhan berbeda (Bulanan, Tabungan, Darurat)</li>
                                <li>Gunakan warna yang berbeda untuk memudahkan identifikasi</li>
                                <li>Masukkan saldo awal sesuai dengan uang yang ada saat ini</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex space-x-3">
                    <button type="submit"
                        class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center justify-center">
                        <i data-lucide="check" class="w-5 h-5 mr-2"></i>
                        Buat Dompet
                    </button>
                    <a href="{{ route('wallets.index') }}"
                        class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors flex items-center justify-center">
                        Batal
                    </a>
                </div>

            </form>
        </div>

    </div>

    <script>
        function walletForm() {
            return {
                icon: 'wallet',
                color: '#3B82F6',
                initialBalance: 0,
                icons: [
                    'wallet', 'piggy-bank', 'banknote', 'credit-card', 'dollar-sign',
                    'briefcase', 'home', 'shield', 'heart', 'star',
                    'zap', 'target', 'trending-up', 'gift', 'award',
                    'package', 'shopping-cart', 'archive', 'bookmark', 'folder'
                ],
                colors: [
                    '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                    '#EC4899', '#06B6D4', '#14B8A6', '#F97316', '#84CC16',
                    '#6366F1', '#A855F7', '#D946EF', '#F43F5E', '#FB923C',
                    '#FBBF24', '#4ADE80', '#22D3EE', '#818CF8', '#C084FC'
                ],

                formatCurrency(value) {
                    if (!value) return 'Rp 0';
                    return 'Rp ' + parseFloat(value).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                },

                init() {
                    // Watch for changes and re-initialize Lucide icons
                    this.$watch('icon', () => {
                        setTimeout(() => lucide.createIcons(), 50);
                    });
                }
            }
        }

        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
@endsection
