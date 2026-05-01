<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'MyWallet') }} - @yield('title')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js untuk interaktivitas -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js untuk grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen antialiased">
    
    <!-- Mobile Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 lg:hidden">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-bold text-gray-900">MyWallet</h1>
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-gray-600">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="flex" x-data="{ mobileMenuOpen: false }">
        
        <!-- Sidebar Desktop -->
        <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 bg-white border-r border-gray-200">
            <div class="flex-1 flex flex-col overflow-y-auto">
                <!-- Logo -->
                <div class="px-6 py-5 border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-900">MyWallet</h1>
                    <p class="text-sm text-gray-500 mt-1">Kelola Keuanganmu</p>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 space-y-1">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <i data-lucide="home" class="w-5 h-5 mr-3"></i>
                        Dashboard
                    </a>
                    
                    <a href="{{ route('wallets.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('wallets.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <i data-lucide="wallet" class="w-5 h-5 mr-3"></i>
                        Dompet
                    </a>
                    
                    <a href="{{ route('transactions.create') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('transactions.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <i data-lucide="plus-circle" class="w-5 h-5 mr-3"></i>
                        Transaksi Baru
                    </a>
                    
                    <a href="{{ route('categories.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('categories.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <i data-lucide="tag" class="w-5 h-5 mr-3"></i>
                        Kategori
                    </a>
                    
                    <a href="{{ route('statistics.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('statistics.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <i data-lucide="bar-chart-3" class="w-5 h-5 mr-3"></i>
                        Statistik
                    </a>

                    <a href="{{ route('ai-chat.index') }}" 
   class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
    <i data-lucide="sparkles" class="w-5 h-5 mr-2"></i>
    AI Assistant
</a>
                </nav>

                <!-- User Profile -->
                <div class="p-4 border-t border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-blue-700 font-semibold text-sm">
                                    {{ substr(auth()->user()->name, 0, 2) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-gray-600">
                                <i data-lucide="log-out" class="w-5 h-5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile Sidebar -->
        <div x-show="mobileMenuOpen" 
             x-cloak
             @click.away="mobileMenuOpen = false"
             class="fixed inset-0 z-40 lg:hidden">
            <div class="fixed inset-0 bg-gray-600 bg-opacity-75"></div>
            <aside class="fixed inset-y-0 left-0 flex flex-col w-64 bg-white">
                <!-- Content sama seperti desktop sidebar -->
                <div class="flex-1 flex flex-col overflow-y-auto">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h1 class="text-2xl font-bold text-gray-900">MyWallet</h1>
                        <p class="text-sm text-gray-500 mt-1">Kelola Keuanganmu</p>
                    </div>

                    <nav class="flex-1 px-4 py-6 space-y-1">
                        <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                            <i data-lucide="home" class="w-5 h-5 mr-3"></i>
                            Dashboard
                        </a>
                        <a href="{{ route('wallets.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700">
                            <i data-lucide="wallet" class="w-5 h-5 mr-3"></i>
                            Dompet
                        </a>
                        <a href="{{ route('transactions.create') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700">
                            <i data-lucide="plus-circle" class="w-5 h-5 mr-3"></i>
                            Transaksi Baru
                        </a>
                        <a href="{{ route('categories.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700">
                            <i data-lucide="tag" class="w-5 h-5 mr-3"></i>
                            Kategori
                        </a>
                        <a href="{{ route('statistics.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700">
                            <i data-lucide="bar-chart-3" class="w-5 h-5 mr-3"></i>
                            Statistik
                        </a>
                    </nav>

                    <div class="p-4 border-t border-gray-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-700 font-semibold text-sm">
                                        {{ substr(auth()->user()->name, 0, 2) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-gray-400 hover:text-gray-600">
                                    <i data-lucide="log-out" class="w-5 h-5"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <!-- Main Content -->
        <main class="flex-1 lg:ml-64">
            <div class="py-6 px-4 sm:px-6 lg:px-8 pb-24 lg:pb-6">
                
                <!-- Flash Messages -->
                @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-500 mr-3"></i>
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 mr-3"></i>
                        <p class="text-sm text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Bottom Navigation (Mobile Only) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50" style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="grid grid-cols-5 h-16">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-600' }}">
                <i data-lucide="home" class="w-6 h-6"></i>
                <span class="text-xs mt-1">Home</span>
            </a>
            <a href="{{ route('wallets.index') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('wallets.*') ? 'text-blue-600' : 'text-gray-600' }}">
                <i data-lucide="wallet" class="w-6 h-6"></i>
                <span class="text-xs mt-1">Dompet</span>
            </a>
            <a href="{{ route('transactions.create') }}" class="flex flex-col items-center justify-center -mt-6">
                <div class="w-14 h-14 bg-blue-600 rounded-full flex items-center justify-center shadow-lg">
                    <i data-lucide="plus" class="w-7 h-7 text-white"></i>
                </div>
            </a>
            <a href="{{ route('categories.index') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('categories.*') ? 'text-blue-600' : 'text-gray-600' }}">
                <i data-lucide="tag" class="w-6 h-6"></i>
                <span class="text-xs mt-1">Kategori</span>
            </a>
            <a href="{{ route('statistics.index') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('statistics.*') ? 'text-blue-600' : 'text-gray-600' }}">
                <i data-lucide="bar-chart-3" class="w-6 h-6"></i>
                <span class="text-xs mt-1">Stats</span>
            </a>
        </div>
    </nav>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>