<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    protected string $apiKey;
    protected string $model;
    protected string $provider;
    protected string $baseUrl;

    public function __construct()
    {
        // Default to Gemini (FREE!)
        $this->provider = config('services.ai.provider', 'gemini');

        if ($this->provider === 'gemini') {
            $this->apiKey = config('services.gemini.api_key');
            $this->model = config('services.gemini.model', 'gemini-1.5-flash');
            // Use v1beta2 which matches the public Generative Language examples
            $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta2';
        } elseif ($this->provider === 'openai') {
            $this->apiKey = config('services.openai.api_key');
            $this->model = config('services.openai.model', 'gpt-4o-mini');
            $this->baseUrl = 'https://api.openai.com/v1';
        } else {
            // Fallback to Anthropic
            $this->apiKey = config('services.anthropic.api_key');
            $this->model = config('services.anthropic.model', 'claude-sonnet-4-20250514');
            $this->baseUrl = 'https://api.anthropic.com/v1';
        }
    }

    /**
     * Main chat method - routes to appropriate provider
     */
    public function chat(string $userMessage, string $financialSummary): array
    {
        if ($this->provider === 'gemini') {
            return $this->chatGemini($userMessage, $financialSummary);
        } elseif ($this->provider === 'openai') {
            return $this->chatOpenAI($userMessage, $financialSummary);
        } else {
            return $this->chatAnthropic($userMessage, $financialSummary);
        }
    }

    /**
     * Chat using Google Gemini (FREE!)
     */
    protected function chatGemini(string $userMessage, string $financialSummary): array
    {
        try {
            $systemPrompt = $this->buildSystemPrompt($financialSummary);

            if (empty($this->apiKey)) {
                Log::error('Gemini API Key Missing');
                return [
                    'success' => false,
                    'error' => 'API Key Gemini tidak ditemukan. Silakan set GEMINI_API_KEY di .env',
                ];
            }

            // Build a single prompt text
            $fullPrompt = $systemPrompt . "\n\nUser: " . $userMessage;

            // Use generateText which is compatible with v1beta2 examples
            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/models/{$this->model}:generateText?key={$this->apiKey}", [
                    'prompt' => [
                        'text' => $fullPrompt,
                    ],
                    'temperature' => 0.7,
                    'maxOutputTokens' => 500,
                ]);

            if ($response->failed()) {
                Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                $errorBody = null;
                try {
                    $errorBody = $response->json();
                } catch (\Throwable $t) {
                    $errorBody = $response->body();
                }

                $result = [
                    'success' => false,
                    'error' => 'Maaf, terjadi kesalahan saat menghubungi AI. Silakan coba lagi.',
                ];

                if (config('app.debug')) {
                    $result['debug'] = $errorBody;
                }

                return $result;
            }

            $data = $response->json();

            // Try to extract response in multiple possible shapes
            $aiResponse = '';
            if (isset($data['candidates']) && is_array($data['candidates']) && !empty($data['candidates'])) {
                // Common: text-bison style -> candidates[0]['output']
                if (isset($data['candidates'][0]['output'])) {
                    $aiResponse = $data['candidates'][0]['output'];
                } elseif (isset($data['candidates'][0]['content'])) {
                    // generateContent style
                    $aiResponse = $data['candidates'][0]['content'][0]['parts'][0]['text'] ?? '';
                }
            }

            // Fallbacks
            if (empty($aiResponse) && isset($data['output'])) {
                $aiResponse = is_string($data['output']) ? $data['output'] : ($data['output'][0]['content'] ?? '');
            }

            return [
                'success' => true,
                'message' => trim($aiResponse),
            ];
        } catch (\Exception $e) {
            Log::error('Gemini Chat Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $result = [
                'success' => false,
                'error' => 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.',
            ];

            if (config('app.debug')) {
                $result['debug'] = [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ];
            }

            return $result;
        }
    }

    /**
     * Chat using OpenAI GPT
     */
    protected function chatOpenAI(string $userMessage, string $financialSummary): array
    {
        try {
            $systemPrompt = $this->buildSystemPrompt($financialSummary);

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(30)->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $userMessage,
                    ],
                ],
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            if ($response->failed()) {
                Log::error('OpenAI API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Maaf, terjadi kesalahan saat menghubungi AI. Silakan coba lagi.',
                ];
            }

            $data = $response->json();
            $aiResponse = $data['choices'][0]['message']['content'] ?? '';

            return [
                'success' => true,
                'message' => trim($aiResponse),
                'usage' => $data['usage'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI Chat Exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.',
            ];
        }
    }

    /**
     * Chat using Anthropic Claude
     */
    protected function chatAnthropic(string $userMessage, string $financialSummary): array
    {
        try {
            $systemPrompt = $this->buildSystemPrompt($financialSummary);

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(30)->post("{$this->baseUrl}/messages", [
                'model' => $this->model,
                'max_tokens' => 1024,
                'system' => $systemPrompt,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $userMessage,
                    ],
                ],
            ]);

            if ($response->failed()) {
                Log::error('Anthropic API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Maaf, terjadi kesalahan saat menghubungi AI. Silakan coba lagi.',
                ];
            }

            $data = $response->json();

            $aiResponse = '';
            if (isset($data['content']) && is_array($data['content'])) {
                foreach ($data['content'] as $content) {
                    if ($content['type'] === 'text') {
                        $aiResponse .= $content['text'];
                    }
                }
            }

            return [
                'success' => true,
                'message' => trim($aiResponse),
                'usage' => $data['usage'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Anthropic Chat Exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.',
            ];
        }
    }

    /**
     * Build system prompt for AI
     */
    protected function buildSystemPrompt(string $financialSummary): string
    {
        return <<<PROMPT
Kamu adalah asisten keuangan pribadi yang membantu user memahami kondisi keuangan mereka dengan bahasa yang sederhana dan ramah.

ATURAN PENTING:
1. Jawab HANYA berdasarkan data keuangan yang diberikan di bawah
2. JANGAN mengarang atau membuat asumsi tentang data yang tidak ada
3. Gunakan bahasa Indonesia yang natural dan ramah
4. Jawaban maksimal 3-5 kalimat
5. Berikan insight atau saran jika relevan
6. Format angka dengan jelas (gunakan Rp dan titik sebagai pemisah ribuan)
7. Jika user bertanya tentang data yang tidak tersedia, katakan dengan jujur

TONE & STYLE:
- Ramah dan supportif (seperti berbicara dengan teman)
- Hindari jargon keuangan yang kompleks
- Gunakan emoji secara natural (tapi jangan berlebihan)
- Fokus pada solusi dan actionable advice

DATA KEUANGAN USER:
{$financialSummary}

Sekarang bantu user dengan pertanyaan mereka tentang keuangan. Jawab dengan singkat, informatif, dan ramah!
PROMPT;
    }

    /**
     * Generate sample questions
     */
    public function generateSampleQuestions(string $financialSummary): array
    {
        return [
            "Pengeluaran terbesar saya bulan ini apa?",
            "Berapa total pengeluaran saya bulan ini?",
            "Apakah pengeluaran saya terlalu besar?",
            "Bagaimana perbandingan pengeluaran bulan ini dengan bulan lalu?",
            "Kategori mana yang paling banyak menghabiskan uang saya?",
            "Berapa sisa budget saya?",
            "Apakah saya bisa menabung lebih banyak?",
            "Berapa rata-rata pengeluaran harian saya?",
        ];
    }
}
