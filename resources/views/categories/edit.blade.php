@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('content')
<div class="max-w-2xl mx-auto pb-20 lg:pb-6">
    
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center mb-2">
            <a href="{{ route('categories.index') }}" class="mr-3 text-gray-600 hover:text-gray-900">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Edit Kategori</h1>
        </div>
        <p class="text-sm text-gray-600">Ubah informasi kategori</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-md p-6" x-data="categoryForm()">
        <form method="POST" action="{{ route('categories.update', $category) }}">
            @csrf
            @method('PUT')

            <!-- Type Display (Read Only) -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Jenis Kategori
                </label>
                <div class="flex items-center px-4 py-3 rounded-lg border-2 border-gray-200 bg-gray-50">
                    @if($category->type === 'income')
                    <div class="flex items-center text-green-700">
                        <i data-lucide="arrow-down-circle" class="w-5 h-5 mr-2"></i>
                        <span class="font-medium">Pemasukan</span>
                    </div>
                    @else
                    <div class="flex items-center text-red-700">
                        <i data-lucide="arrow-up-circle" class="w-5 h-5 mr-2"></i>
                        <span class="font-medium">Pengeluaran</span>
                    </div>
                    @endif
                </div>
                <p class="mt-1 text-xs text-gray-500">Tipe kategori tidak bisa diubah</p>
            </div>

            <!-- Category Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Kategori <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name"
                       required
                       value="{{ old('name', $category->name) }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Icon Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Pilih Icon
                </label>
                <div class="grid grid-cols-6 sm:grid-cols-8 gap-3">
                    <template x-for="iconOption in icons" :key="iconOption">
                        <button type="button"
                                @click="icon = iconOption"
                                :class="icon === iconOption ? 'bg-blue-100 border-blue-500 ring-2 ring-blue-500' : 'bg-gray-50 border-gray-200 hover:bg-gray-100'"
                                class="aspect-square flex items-center justify-center border-2 rounded-lg transition-all p-3">
                            <i :data-lucide="iconOption" 
                               :class="icon === iconOption ? 'text-blue-600' : 'text-gray-600'"
                               class="w-6 h-6"></i>
                        </button>
                    </template>
                </div>
                <input type="hidden" name="icon" x-model="icon">
                <p class="mt-2 text-xs text-gray-500">Icon yang dipilih: <span x-text="icon || 'Belum dipilih'" class="font-medium"></span></p>
            </div>

            <!-- Color Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Pilih Warna
                </label>
                <div class="grid grid-cols-6 sm:grid-cols-10 gap-3">
                    <template x-for="colorOption in colors" :key="colorOption">
                        <button type="button"
                                @click="color = colorOption"
                                :class="color === colorOption ? 'ring-2 ring-offset-2 ring-gray-400 scale-110' : ''"
                                class="aspect-square rounded-lg transition-all hover:scale-105"
                                :style="`background-color: ${colorOption}`">
                        </button>
                    </template>
                </div>
                <input type="hidden" name="color" x-model="color" required>
                <p class="mt-2 text-xs text-gray-500">Warna yang dipilih: <span x-text="color" class="font-mono font-medium"></span></p>
            </div>

            <!-- Preview -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                <p class="text-sm font-medium text-gray-700 mb-3">Preview Kategori:</p>
                <div class="flex items-center">
                    <div class="w-14 h-14 rounded-lg flex items-center justify-center mr-4" 
                         :style="`background-color: ${color}20`">
                        <i :data-lucide="icon || 'tag'" 
                           class="w-7 h-7"
                           :style="`color: ${color}`"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900" x-text="document.getElementById('name').value || 'Nama Kategori'"></p>
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full mt-1 {{ $category->type === 'income' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $category->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Statistics Info -->
            @if($category->transactions_count > 0)
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i data-lucide="info" class="w-5 h-5 text-blue-600 mr-3 mt-0.5"></i>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium">Kategori ini memiliki {{ $category->transactions_count }} transaksi</p>
                        <p class="mt-1">Perubahan akan mempengaruhi tampilan semua transaksi dengan kategori ini.</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Submit Button -->
            <div class="flex space-x-3">
                <button type="submit" 
                        class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                    Simpan Perubahan
                </button>
                <a href="{{ route('categories.index') }}" 
                   class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                    Batal
                </a>
            </div>

        </form>
    </div>

</div>

<script>
    function categoryForm() {
        return {
            icon: '{{ old('icon', $category->icon ?? 'tag') }}',
            color: '{{ old('color', $category->color ?? '#6B7280') }}',
            icons: [
                'tag', 'utensils', 'car', 'shopping-bag', 'film', 'home', 
                'wifi', 'heart', 'book', 'briefcase', 'gift', 'award',
                'coffee', 'plane', 'smartphone', 'shirt', 'zap', 'droplet',
                'gamepad', 'music', 'headphones', 'camera', 'umbrella', 'bicycle',
                'bus', 'train', 'fuel', 'pill', 'dumbbell', 'baby',
                'pet', 'scissors', 'wrench', 'lightbulb', 'package', 'credit-card',
                'banknote', 'piggy-bank', 'wallet', 'chart-line', 'trending-up', 'target',
                'code', 'palette', 'pen-tool', 'server', 'monitor', 'dollar-sign'
            ],
            colors: [
                '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', 
                '#EC4899', '#06B6D4', '#14B8A6', '#F97316', '#84CC16',
                '#6366F1', '#A855F7', '#D946EF', '#F43F5E', '#FB923C',
                '#FBBF24', '#4ADE80', '#22D3EE', '#818CF8', '#C084FC',
                '#E879F9', '#FB7185', '#6B7280', '#374151', '#1F2937'
            ],
            
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