<?php

namespace App\Http\Controllers\Api;

use App\Models\RagEvaluation;
use App\Services\Contracts\KnowledgeQaServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RagEvaluationController
{
    public function __construct(private readonly KnowledgeQaServiceInterface $qa) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => RagEvaluation::query()->latest()->limit(100)->get()]);
    }

    public function run(Request $request): JsonResponse
    {
        $data = $request->validate(['question' => ['required', 'string', 'max:1000'], 'expected_keyword' => ['nullable', 'string', 'max:200']]);
        $started = hrtime(true);
        $result = $this->qa->ask($data['question']);
        $latency = (int) round((hrtime(true) - $started) / 1000000);
        $keyword = trim((string) ($data['expected_keyword'] ?? ''));
        $passed = count($result['citations']) > 0 && ($keyword === '' || mb_stripos($result['answer'], $keyword) !== false);
        $evaluation = RagEvaluation::create([
            'question' => $data['question'], 'expected_keyword' => $keyword ?: null,
            'answer' => $result['answer'], 'citations_count' => count($result['citations']),
            'latency_ms' => $latency, 'passed' => $passed, 'provider' => $result['provider'],
        ]);
        return response()->json(['data' => $evaluation, 'citations' => $result['citations']], 201);
    }
}
