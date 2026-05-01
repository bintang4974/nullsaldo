@extends('layouts.app')

@section('title', 'Edit Transaksi')

@section('content')
    <div class="max-w-2xl mx-auto pb-20 lg:pb-6">

        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center mb-2">
                <a href="{{ route('wallets.show', $wallet) }}" class="mr-3 text-gray-600 hover:text-gray-900">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Edit Transaksi</h1>
            </div>
            <p class="text-sm text-gray-600">Ubah detail transaksi</p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-xl shadow-md p-6" x-data="transactionForm()">
            <form method="POST" action="{{ route('transactions.update', $transaction) }}">
                @csrf
                @method('PUT')

                <!-- Wallet Info (Read Only) -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-3"
                            style="background-color: {{ $wallet->color }}20;">
                            <i data-lucide="{{ $wallet->icon ?? 'wallet' }}" class="w-6 h-6"
                                style="color: {{ $wallet->color }};"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Dompet</p>
                            <p class="font-semibold text-gray-900">{{ $wallet->name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Type Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Jenis Transaksi</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="type = 'income'"
                            :class="type === 'income' ? 'bg-green-600 text-white' :
                                'bg-white text-gray-700 border-2 border-gray-200'"
                            class="flex items-center justify-center px-4 py-3 rounded-lg font-medium transition-all">
                            <i data-lucide="arrow-down-circle" class="w-5 h-5 mr-2"></i>
                            Pemasukan
                        </button>
                        <button type="button" @click="type = 'expense'"
                            :class="type === 'expense' ? 'bg-red-600 text-white' :
                                'bg-white text-gray-700 border-2 border-gray-200'"
                            class="flex items-center justify-center px-4 py-3 rounded-lg font-medium transition-all">
                            <i data-lucide="arrow-up-circle" class="w-5 h-5 mr-2"></i>
                            Pengeluaran
                        </button>
                    </div>
                    <input type="hidden" name="type" x-model="type">
                </div>

                <!-- Amount -->
                <div class="mb-6">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                        Nominal <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-500">Rp</span>
                        </div>
                        <input type="number" name="amount" id="amount" required step="0.01" min="0.01"
                            placeholder="0" value="{{ old('amount', $transaction->amount) }}"
                            class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div class="mb-6" x-show="type !== null">
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Kategori
                    </label>
                    <select name="category_id" id="category_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Pilih Kategori --</option>
                        <template x-for="category in filteredCategories" :key="category.id">
                            <option :value="category.id" x-text="category.name"
                                :selected="category.id == {{ old('category_id', $transaction->category_id ?? 'null') }}">
                            </option>
                        </template>
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Transaction Date -->
                <div class="mb-6">
                    <label for="transaction_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Transaksi <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="transaction_date" id="transaction_date" required
                        value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}"
                        max="{{ now()->format('Y-m-d') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('transaction_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan (Opsional)
                    </label>
                    <textarea name="description" id="description" rows="3" placeholder="Tambahkan catatan untuk transaksi ini..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none">{{ old('description', $transaction->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Warning Info -->
                <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600 mr-3 mt-0.5"></i>
                        <div class="text-sm text-yellow-800">
                            <p class="font-medium">Perhatian!</p>
                            <p class="mt-1">Mengubah transaksi akan mempengaruhi saldo dompet. Pastikan data yang diinput
                                sudah benar.</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex space-x-3">
                    <button type="submit"
                        class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('wallets.show', $wallet) }}"
                        class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                </div>

            </form>
        </div>

    </div>

    <script>
        function transactionForm() {
            return {
                type: '{{ old('type', $transaction->type) }}',
                categories: @json($categories),

                get filteredCategories() {
                    return this.categories.filter(cat => cat.type === this.type);
                },

                init() {
                    this.$watch('type', () => {
                        // Reset category when type changes
                        const categorySelect = document.getElementById('category_id');
                        const currentCategoryId = {{ old('category_id', $transaction->category_id ?? 'null') }};

                        // Check if current category matches new type
                        const currentCategory = this.categories.find(c => c.id == currentCategoryId);
                        if (currentCategory && currentCategory.type !== this.type) {
                            categorySelect.value = '';
                        }

                        // Re-initialize Lucide icons
                        setTimeout(() => lucide.createIcons(), 100);
                    });
                }
            }
        }

        // Initialize Lucide icons
        lucide.createIcons();
    </script>
@endsection
