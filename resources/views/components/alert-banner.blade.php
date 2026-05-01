@props(['alerts'])

@php
// Helper closures for alert styling (defined before usage)
$getAlertClass = function($type) {
    $classes = [
        'info' => 'bg-blue-50 border border-blue-200',
        'warning' => 'bg-yellow-50 border border-yellow-200',
        'danger' => 'bg-red-50 border border-red-200',
        'success' => 'bg-green-50 border border-green-200',
    ];
    return $classes[$type] ?? 'bg-gray-50 border border-gray-200';
};

$getIconBg = function($type) {
    $classes = [
        'info' => 'bg-blue-100',
        'warning' => 'bg-yellow-100',
        'danger' => 'bg-red-100',
        'success' => 'bg-green-100',
    ];
    return $classes[$type] ?? 'bg-gray-100';
};

$getIconColor = function($type) {
    $classes = [
        'info' => 'text-blue-600',
        'warning' => 'text-yellow-600',
        'danger' => 'text-red-600',
        'success' => 'text-green-600',
    ];
    return $classes[$type] ?? 'text-gray-600';
};

$getTextColor = function($type) {
    $classes = [
        'info' => 'text-blue-900',
        'warning' => 'text-yellow-900',
        'danger' => 'text-red-900',
        'success' => 'text-green-900',
    ];
    return $classes[$type] ?? 'text-gray-900';
};

$getTextColorLight = function($type) {
    $classes = [
        'info' => 'text-blue-800',
        'warning' => 'text-yellow-800',
        'danger' => 'text-red-800',
        'success' => 'text-green-800',
    ];
    return $classes[$type] ?? 'text-gray-700';
};

$getLinkColor = function($type) {
    $classes = [
        'info' => 'text-blue-700',
        'warning' => 'text-yellow-700',
        'danger' => 'text-red-700',
        'success' => 'text-green-700',
    ];
    return $classes[$type] ?? 'text-gray-900';
};
@endphp

@if(!empty($alerts))
    @foreach($alerts as $alert)
    <div x-data="{ show: true }" 
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="mb-6 rounded-xl shadow-md overflow-hidden {{ $getAlertClass($alert['type']) }}">
        <div class="p-4 sm:p-5">
            <div class="flex items-start">
                <!-- Icon -->
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg flex items-center justify-center {{ $getIconBg($alert['type']) }}">
                        <i data-lucide="{{ $alert['icon'] }}" class="w-5 h-5 sm:w-6 sm:h-6 {{ $getIconColor($alert['type']) }}"></i>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="ml-4 flex-1 min-w-0">
                    <h3 class="text-sm sm:text-base font-bold {{ $getTextColor($alert['type']) }}">
                        {{ $alert['title'] }}
                    </h3>
                    <p class="text-xs sm:text-sm mt-1 {{ $getTextColorLight($alert['type']) }}">
                        {{ $alert['message'] }}
                    </p>
                    
                    @if(isset($alert['action']))
                    <a href="{{ $alert['action']['url'] }}" 
                       class="inline-flex items-center mt-3 text-xs sm:text-sm font-medium hover:underline {{ $getLinkColor($alert['type']) }}">
                        {{ $alert['action']['text'] }}
                        <i data-lucide="arrow-right" class="w-3 h-3 sm:w-4 sm:h-4 ml-1"></i>
                    </a>
                    @endif
                </div>
                
                <!-- Close Button -->
                <button @click="show = false" 
                        class="ml-4 flex-shrink-0 p-1 rounded-lg hover:bg-black hover:bg-opacity-5 transition-colors"
                        aria-label="Tutup">
                    <i data-lucide="x" class="w-5 h-5 {{ $getTextColorLight($alert['type']) }}"></i>
                </button>
            </div>
        </div>
    </div>
    @endforeach
@endif

@php
// (helpers defined above)
@endphp