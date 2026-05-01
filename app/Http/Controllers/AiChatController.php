<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AiChatService;
use App\Services\FinancialSummaryService;

class AiChatController extends Controller
{
    protected AiChatService $aiService;
    protected FinancialSummaryService $summaryService;

    public function __construct(AiChatService $aiService, FinancialSummaryService $summaryService)
    {
        $this->aiService = $aiService;
        $this->summaryService = $summaryService;
    }

    public function index()
    {
        $sampleQuestions = $this->aiService->generateSampleQuestions('');
        return view('ai-chat.index', compact('sampleQuestions'));
    }

    public function getSummary(Request $request)
    {
        $user = Auth::user();
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $summary = $this->summaryService->generateSummary($user, $month, $year);

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer',
        ]);

        $user = Auth::user();
        $message = $request->input('message');
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $financialSummary = $this->summaryService->generateSummary($user, (int)$month, (int)$year);

        $result = $this->aiService->chat($message, $financialSummary);

        if ($result['success'] ?? false) {
            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? '',
                'timestamp' => now()->toIso8601String(),
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? 'Terjadi kesalahan.',
        ], 500);
    }
}
