<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\KnowledgeChatController;
use App\Http\Controllers\Api\KnowledgeQualityController;
use App\Http\Controllers\Api\ModelConfigController;
use App\Http\Controllers\Api\RagEvaluationController;
use App\Http\Controllers\Api\WeComConfigController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('free.auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/dashboard', DashboardController::class);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{knowledgeCategory}', [CategoryController::class, 'update']);
    Route::delete('/categories/{knowledgeCategory}', [CategoryController::class, 'destroy']);
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{document}', [DocumentController::class, 'show']);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download']);
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);
    Route::get('/knowledge-quality', KnowledgeQualityController::class);
    Route::get('/rag-evaluations', [RagEvaluationController::class, 'index']);
    Route::post('/rag-evaluations/run', [RagEvaluationController::class, 'run']);
    Route::get('/wecom-configs', [WeComConfigController::class, 'index']);
    Route::post('/wecom-configs', [WeComConfigController::class, 'store']);
    Route::put('/wecom-configs/{weComConfig}', [WeComConfigController::class, 'update']);
    Route::delete('/wecom-configs/{weComConfig}', [WeComConfigController::class, 'destroy']);
    Route::post('/wecom-configs/{weComConfig}/test', [WeComConfigController::class, 'test']);
    Route::get('/model-configs', [ModelConfigController::class, 'index']);
    Route::post('/knowledge-chat/ask', [KnowledgeChatController::class, 'ask']);
    Route::post('/knowledge-chat/feedback', [KnowledgeChatController::class, 'feedback']);
    Route::get('/knowledge-chat/sessions', [KnowledgeChatController::class, 'sessions']);
});
