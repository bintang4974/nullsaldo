@extends('layouts.app')

@section('title', 'AI Assistant')

@section('content')
    <div class="max-w-4xl mx-auto pb-20 lg:pb-6 h-[calc(100vh-8rem)]" x-data="aiChat()">

        <!-- Header -->
        <div class="mb-4 sm:mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mr-3">
                        <i data-lucide="sparkles" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">AI Assistant</h1>
                        <p class="text-xs sm:text-sm text-gray-600">Tanya apa saja tentang keuanganmu</p>
                    </div>
                </div>

                <!-- Period Selector (Small) -->
                <div class="flex items-center space-x-2 text-xs">
                    <select x-model="month" @change="updatePeriod()"
                        class="px-2 py-1 border border-gray-300 rounded-lg text-xs">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->format('M') }}
                            </option>
                        @endfor
                    </select>
                    <select x-model="year" @change="updatePeriod()"
                        class="px-2 py-1 border border-gray-300 rounded-lg text-xs">
                        @for ($y = now()->year; $y >= now()->year - 2; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>

        <!-- Chat Container -->
        <div class="bg-white rounded-xl shadow-md flex flex-col h-[calc(100%-5rem)]">

            <!-- Chat Messages Area -->
            <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4" x-ref="chatContainer">

                <!-- Welcome Message -->
                <div x-show="messages.length === 0" class="text-center py-12">
                    <div
                        class="w-20 h-20 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="bot" class="w-10 h-10 text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Halo! 👋</h3>
                    <p class="text-sm text-gray-600 mb-6 max-w-md mx-auto">
                        Saya AI Assistant yang siap membantu kamu memahami kondisi keuanganmu.
                        Tanya apa saja tentang pengeluaran, pemasukan, atau budget kamu!
                    </p>

                    <!-- Sample Questions -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-2xl mx-auto">
                        @foreach ($sampleQuestions as $question)
                            <button @click="sendMessage('{{ $question }}')"
                                class="p-3 text-left text-sm bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-lg transition-all group">
                                <i data-lucide="message-circle"
                                    class="w-4 h-4 text-gray-400 group-hover:text-blue-600 inline mr-2"></i>
                                <span class="text-gray-700 group-hover:text-blue-700">{{ $question }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Chat Messages -->
                <template x-for="(msg, index) in messages" :key="index">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div
                            :class="msg.role === 'user' ?
                                'bg-blue-600 text-white rounded-2xl rounded-tr-sm px-4 py-3 max-w-[80%] sm:max-w-[70%]' :
                                'bg-gray-100 text-gray-900 rounded-2xl rounded-tl-sm px-4 py-3 max-w-[80%] sm:max-w-[70%]'">

                            <!-- AI Typing Indicator -->
                            <div x-show="msg.role === 'assistant' && msg.typing" class="flex space-x-2 py-1">
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s">
                                </div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s">
                                </div>
                            </div>

                            <!-- Message Text -->
                            <p x-show="!msg.typing" class="text-sm whitespace-pre-wrap" x-html="formatMessage(msg.text)">
                            </p>

                            <!-- Timestamp -->
                            <p x-show="!msg.typing" :class="msg.role === 'user' ? 'text-blue-200' : 'text-gray-500'"
                                class="text-xs mt-1">
                                <span x-text="msg.timestamp"></span>
                            </p>
                        </div>
                    </div>
                </template>

                <!-- Error Message -->
                <div x-show="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 mr-3 mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-sm text-red-800" x-text="errorMessage"></p>
                    </div>
                    <button @click="errorMessage = ''" class="text-red-600 hover:text-red-800">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Input Area -->
            <div class="border-t border-gray-200 p-4 sm:p-6">
                <form @submit.prevent="sendMessage()" class="flex items-end space-x-3">
                    <div class="flex-1">
                        <textarea x-model="inputMessage" @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()" :disabled="loading"
                            placeholder="Tanya tentang keuanganmu..." rows="1"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            style="min-height: 48px; max-height: 120px;"></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            <i data-lucide="info" class="w-3 h-3 inline"></i>
                            Tekan Enter untuk kirim, Shift+Enter untuk baris baru
                        </p>
                    </div>
                    <button type="submit" :disabled="loading || !inputMessage.trim()"
                        :class="loading || !inputMessage.trim() ?
                            'bg-gray-300 cursor-not-allowed' :
                            'bg-blue-600 hover:bg-blue-700'"
                        class="px-6 py-3 text-white rounded-xl font-medium transition-colors flex items-center">
                        <i data-lucide="send" class="w-5 h-5" x-show="!loading"></i>
                        <svg x-show="loading" class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </button>
                </form>
            </div>

        </div>

    </div>

    <script>
        function aiChat() {
            return {
                messages: [],
                inputMessage: '',
                loading: false,
                errorMessage: '',
                month: {{ now()->month }},
                year: {{ now()->year }},

                async sendMessage(predefinedMessage = null) {
                    const message = predefinedMessage || this.inputMessage.trim();

                    if (!message || this.loading) return;

                    // Add user message
                    this.messages.push({
                        role: 'user',
                        text: message,
                        timestamp: this.formatTime(new Date()),
                    });

                    // Clear input
                    this.inputMessage = '';

                    // Show typing indicator
                    this.messages.push({
                        role: 'assistant',
                        text: '',
                        typing: true,
                        timestamp: '',
                    });

                    this.scrollToBottom();
                    this.loading = true;
                    this.errorMessage = '';

                    try {
                        const response = await fetch('{{ route('ai-chat.send') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({
                                message: message,
                                month: this.month,
                                year: this.year,
                            }),
                        });

                        const data = await response.json();

                        // Remove typing indicator
                        this.messages.pop();

                        if (data.success) {
                            this.messages.push({
                                role: 'assistant',
                                text: data.message,
                                timestamp: this.formatTime(new Date(data.timestamp)),
                            });
                        } else {
                            this.errorMessage = data.error || 'Terjadi kesalahan. Silakan coba lagi.';
                        }

                    } catch (error) {
                        // Remove typing indicator
                        this.messages.pop();
                        this.errorMessage = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
                    } finally {
                        this.loading = false;
                        this.scrollToBottom();
                        lucide.createIcons();
                    }
                },

                updatePeriod() {
                    this.messages = [];
                    this.errorMessage = '';
                },

                formatMessage(text) {
                    // Convert **bold** to <strong>
                    text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
                    // Convert newlines to <br>
                    text = text.replace(/\n/g, '<br>');
                    return text;
                },

                formatTime(date) {
                    return date.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                scrollToBottom() {
                    setTimeout(() => {
                        const container = this.$refs.chatContainer;
                        container.scrollTop = container.scrollHeight;
                    }, 100);
                },
            }
        }

        // Initialize Lucide icons
        lucide.createIcons();
    </script>

    <style>
        /* Custom scrollbar */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }

        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
@endsection
