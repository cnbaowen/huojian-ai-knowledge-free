<?php

namespace App\Http\Controllers\Api;

use App\Models\KnowledgeCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => KnowledgeCategory::query()->withCount('documents')->orderBy('name')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:knowledge_categories,name']]);
        return response()->json(['data' => KnowledgeCategory::create($data)], 201);
    }

    public function update(Request $request, KnowledgeCategory $knowledgeCategory): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:knowledge_categories,name,'.$knowledgeCategory->id]]);
        $knowledgeCategory->update($data);
        return response()->json(['data' => $knowledgeCategory]);
    }

    public function destroy(KnowledgeCategory $knowledgeCategory): JsonResponse
    {
        if ($knowledgeCategory->documents()->exists()) return response()->json(['message' => '该分类下仍有文档，不能删除。'], 422);
        $knowledgeCategory->delete();
        return response()->json(['message' => '知识分类已删除。']);
    }
}
