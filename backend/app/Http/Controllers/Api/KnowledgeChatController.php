<?php

namespace App\Http\Controllers\Api;

use App\Services\Contracts\KnowledgeQaServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeChatController
{
    public function __construct(private readonly KnowledgeQaServiceInterface $qa) {}

    public function ask(Request $request): JsonResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:4000'],
            'category_id' => ['nullable', 'integer', 'exists:knowledge_categories,id'],
            'session_id' => ['nullable', 'uuid'],
        ]);
        return response()->json($this->qa->ask($data['question'], $data['category_id'] ?? null, $data['session_id'] ?? null));
    }

    public function feedback(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message_id' => ['required', 'uuid', 'exists:chat_messages,id'],
            'rating' => ['required', 'in:up,down'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        $feedback = \App\Models\KnowledgeSearchFeedback::create($data);
        return response()->json(['accepted' => true, 'feedback' => $feedback], 201);
    }

    public function sessions(): JsonResponse
    {
        $sessions = \App\Models\ChatSession::query()->with(['messages' => fn ($query) => $query->latest()->limit(20)])->latest()->limit(50)->get();
        return response()->json(['data' => $sessions]);
    }
}
