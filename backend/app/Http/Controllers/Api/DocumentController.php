<?php

namespace App\Http\Controllers\Api;

use App\Models\Document;
use App\Services\DocumentIngestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController
{
    public function __construct(private readonly DocumentIngestionService $ingestion) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => Document::query()->with('category')->latest()->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $maxKb = max(1, intdiv((int) config('knowledge.max_document_bytes', 10485760), 1024));
        $data = $request->validate([
            'file' => ['required', 'file', 'max:'.$maxKb, 'extensions:txt,md,markdown,log,csv,json,docx,xlsx,pdf'],
            'category_id' => ['nullable', 'integer', 'exists:knowledge_categories,id'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);
        $document = $this->ingestion->ingest($data['file'], $data['category_id'] ?? null, $data['name'] ?? null);
        return response()->json(['data' => $document, 'message' => '文档已解析、切片并建立向量索引。'], 201);
    }

    public function show(Document $document): JsonResponse
    {
        return response()->json(['data' => $document->load(['category', 'chunks' => fn ($query) => $query->orderBy('sequence')])]);
    }

    public function download(Document $document): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($document->stored_path), 404, '原始文档不存在。');
        return Storage::disk('local')->download($document->stored_path, $document->original_name);
    }

    public function destroy(Document $document): JsonResponse
    {
        $this->ingestion->delete($document);
        return response()->json(['message' => '文档及其知识切片已删除。']);
    }
}
