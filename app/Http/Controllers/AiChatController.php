<?php

namespace App\Http\Controllers;

use App\Services\AiChatContextService;
use App\Services\GeminiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AiChatController extends Controller
{
    public function __construct(
        protected GeminiChatService $geminiChatService,
        protected AiChatContextService $aiChatContextService
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Bạn cần đăng nhập để sử dụng AI chatbox.',
            ], 401);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'history' => ['nullable', 'array', 'max:12'],
            'history.*.role' => ['required', 'string', Rule::in(['user', 'assistant'])],
            'history.*.content' => ['required', 'string', 'max:4000'],
        ]);

        if (! $this->geminiChatService->isConfigured()) {
            return response()->json([
                'message' => 'Gemini API chưa được cấu hình. Hãy thêm GEMINI_API_KEY vào file .env.',
            ], 503);
        }

        try {
            $context = $this->aiChatContextService->buildForUser(
                $user,
                (string) $validated['message']
            );

            $reply = $this->geminiChatService->generateReply(
                $user,
                (string) $validated['message'],
                $context,
                $validated['history'] ?? []
            );

            return response()->json([
                'reply' => $reply,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Không thể lấy phản hồi từ AI ở thời điểm này. Vui lòng thử lại sau.',
            ], 500);
        }
    }
}
