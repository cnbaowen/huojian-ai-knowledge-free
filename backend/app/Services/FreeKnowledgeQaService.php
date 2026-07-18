<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\Contracts\KnowledgeQaServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FreeKnowledgeQaService implements KnowledgeQaServiceInterface
{
    public function __construct(
        private readonly VectorSearchService $search,
        private readonly ModelAnswerService $answerer,
    ) {}

    public function ask(string $question, ?int $categoryId = null, ?string $sessionId = null): array
    {
        $matches = $this->search->search($question, $categoryId);
        $result = $this->answerer->answer($question, $matches);
        $citations = [];
        foreach ($matches as $index => $match) {
            $chunk = $match['chunk'];
            $citations[] = [
                'number' => $index + 1,
                'document_id' => $chunk->document_id,
                'document_name' => $chunk->document->name,
                'category' => $chunk->document->category?->name,
                'chunk_id' => $chunk->id,
                'sequence' => $chunk->sequence,
                'score' => round($match['score'], 4),
                'excerpt' => mb_substr(trim(preg_replace('/\s+/u', ' ', $chunk->content) ?? $chunk->content), 0, 260),
            ];
        }

        return DB::transaction(function () use ($question, $sessionId, $result, $citations): array {
            $session = $sessionId ? ChatSession::find($sessionId) : null;
            if (! $session) {
                $session = ChatSession::create(['id' => (string) Str::uuid(), 'title' => mb_substr($question, 0, 60)]);
            }
            ChatMessage::create(['id' => (string) Str::uuid(), 'session_id' => $session->id, 'role' => 'user', 'content' => $question, 'citations' => null]);
            $assistant = ChatMessage::create([
                'id' => (string) Str::uuid(), 'session_id' => $session->id, 'role' => 'assistant',
                'content' => $result['answer'], 'citations' => $citations,
            ]);
            return [
                'ready' => true,
                'session_id' => $session->id,
                'message_id' => $assistant->id,
                'answer' => $result['answer'],
                'citations' => $citations,
                'provider' => $result['provider'],
                'model' => $result['model'],
                'retrieved_chunks' => count($citations),
            ];
        });
    }
}
