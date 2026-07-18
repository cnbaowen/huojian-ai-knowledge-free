<?php

namespace App\Http\Controllers\Api;

use App\Models\ChatMessage;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\KnowledgeCategory;
use App\Models\WeComConfig;
use Illuminate\Http\JsonResponse;

class DashboardController
{
    public function __invoke(): JsonResponse
    {
        $answers = ChatMessage::query()->where('role', 'assistant')->get(['citations']);
        $withCitations = $answers->filter(fn ($message) => count($message->citations ?? []) > 0)->count();
        return response()->json(['data' => [
            'categories' => KnowledgeCategory::query()->count(),
            'documents' => Document::query()->count(),
            'indexed_documents' => Document::query()->where('status', 'indexed')->count(),
            'chunks' => DocumentChunk::query()->count(),
            'questions' => ChatMessage::query()->where('role', 'user')->count(),
            'citation_rate' => $answers->count() ? round($withCitations / $answers->count() * 100, 1) : 0,
            'wecom_enabled' => WeComConfig::query()->where('enabled', true)->exists(),
        ]]);
    }
}
