<?php

namespace App\Http\Controllers\Api;

use App\Models\Document;
use Illuminate\Http\JsonResponse;

class KnowledgeQualityController
{
    public function __invoke(): JsonResponse
    {
        $documents = Document::query()->with('category')->latest()->get();
        $issues = $documents->map(function (Document $document): ?array {
            $reasons = [];
            if (! $document->category_id) $reasons[] = '未设置知识分类';
            if ($document->status !== 'indexed') $reasons[] = $document->status === 'failed' ? '解析失败' : '尚未完成索引';
            if ($document->status === 'indexed' && $document->chunks_count < 1) $reasons[] = '没有有效知识切片';
            if ($document->chunks_count > 200) $reasons[] = '切片数量异常偏高，建议检查文档结构';
            return $reasons ? ['document_id' => $document->id, 'document_name' => $document->name, 'category' => $document->category?->name, 'reasons' => $reasons] : null;
        })->filter()->values();
        $healthy = max(0, $documents->count() - $issues->count());
        return response()->json(['data' => [
            'documents' => $documents->count(),
            'healthy_documents' => $healthy,
            'issues_count' => $issues->count(),
            'quality_score' => $documents->count() ? round($healthy / $documents->count() * 100, 1) : 100,
            'issues' => $issues,
        ]]);
    }
}
