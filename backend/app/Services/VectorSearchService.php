<?php

namespace App\Services;

use App\Models\DocumentChunk;

class VectorSearchService
{
    public function __construct(private readonly LocalVectorizer $vectorizer) {}

    public function search(string $question, ?int $categoryId = null): array
    {
        $queryVector = $this->vectorizer->embed($question);
        $queryTokens = array_values(array_unique($this->vectorizer->tokens($question)));
        $query = DocumentChunk::query()->with(['document.category'])->whereHas('document', function ($builder) use ($categoryId): void {
            $builder->where('status', 'indexed');
            if ($categoryId !== null) { $builder->where('category_id', $categoryId); }
        });

        $ranked = [];
        foreach ($query->limit(2000)->get() as $chunk) {
            $vectorScore = $this->vectorizer->cosine($queryVector, $chunk->vector_json ?? []);
            $hits = 0;
            foreach ($queryTokens as $token) { if ($token !== '' && mb_stripos($chunk->content, $token) !== false) { $hits++; } }
            $lexicalScore = count($queryTokens) > 0 ? $hits / count($queryTokens) : 0.0;
            $score = max(0.0, $vectorScore) * 0.72 + $lexicalScore * 0.28;
            if ($lexicalScore > 0.0 || $vectorScore >= (float) config('knowledge.min_vector_score', 0.20)) {
                $ranked[] = ['chunk' => $chunk, 'score' => $score, 'vector_score' => $vectorScore, 'lexical_score' => $lexicalScore];
            }
        }
        usort($ranked, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);
        return array_slice($ranked, 0, max(1, (int) config('knowledge.search_limit', 5)));
    }
}
