@props(['insights'])

@php
// Helper closures to map insight color keys to Tailwind classes
$getInsightBg = function($color) {
    $classes = [
        'red' => 'bg-red-50',
        'orange' => 'bg-orange-50',
        'yellow' => 'bg-yellow-50',
        'green' => 'bg-green-50',
        'blue' => 'bg-blue-50',
        'purple' => 'bg-purple-50',
    ];
    return $classes[$color] ?? 'bg-gray-50';
};

$getInsightBorder = function($color) {
    $classes = [
        'red' => 'border-red-200',
        'orange' => 'border-orange-200',
        'yellow' => 'border-yellow-200',
        'green' => 'border-green-200',
        'blue' => 'border-blue-200',
        'purple' => 'border-purple-200',
    ];
    return $classes[$color] ?? 'border-gray-200';
};

$getInsightIconBg = function($color) {
    $classes = [
        'red' => 'bg-red-100',
        'orange' => 'bg-orange-100',
        'yellow' => 'bg-yellow-100',
        'green' => 'bg-green-100',
        'blue' => 'bg-blue-100',
        'purple' => 'bg-purple-100',
    ];
    return $classes[$color] ?? 'bg-gray-100';
};

$getInsightIconColor = function($color) {
    $classes = [
        'red' => 'text-red-600',
        'orange' => 'text-orange-600',
        'yellow' => 'text-yellow-600',
        'green' => 'text-green-600',
        'blue' => 'text-blue-600',
        'purple' => 'text-purple-600',
    ];
    return $classes[$color] ?? 'text-gray-600';
};
@endphp

@if(!empty($insights))
<div class="bg-white rounded-xl shadow-md p-5 sm:p-6 mb-6">
    <div class="flex items-center mb-4">
        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
            <i data-lucide="lightbulb" class="w-5 h-5 text-yellow-600"></i>
        </div>
        <div>
            <h2 class="text-base sm:text-lg font-bold text-gray-900">Insight Keuangan</h2>
            <p class="text-xs text-gray-500">Analisis otomatis dari transaksi Anda</p>
        </div>
    </div>
    
    <div class="space-y-3">
        @foreach($insights as $insight)
        <div class="flex items-start p-3 sm:p-4 rounded-lg {{ $getInsightBg($insight['color']) }} border {{ $getInsightBorder($insight['color']) }}">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $getInsightIconBg($insight['color']) }}">
                    <i data-lucide="{{ $insight['icon'] }}" 
                       class="w-4 h-4 {{ $getInsightIconColor($insight['color']) }}"></i>
                </div>
            </div>
            <p class="ml-3 text-xs sm:text-sm text-gray-800 flex-1">
                {!! $insight['text'] !!}
            </p>
        </div>
        @endforeach
    </div>
    
    <!-- Footer Info -->
    <div class="mt-4 pt-4 border-t border-gray-200">
        <p class="text-xs text-gray-500 flex items-center">
            <i data-lucide="info" class="w-3 h-3 mr-1"></i>
            Insight diperbarui secara otomatis berdasarkan aktivitas transaksi Anda
        </p>
    </div>
</div>
@endif

@php
// (helpers moved above)
@endphp