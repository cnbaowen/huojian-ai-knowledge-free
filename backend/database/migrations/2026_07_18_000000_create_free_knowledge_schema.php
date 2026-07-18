<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('knowledge_categories', function (Blueprint $table): void {
            $table->id(); $table->string('name')->unique(); $table->timestamps();
        });
        Schema::create('documents', function (Blueprint $table): void {
            $table->id(); $table->foreignId('category_id')->nullable()->constrained('knowledge_categories')->nullOnDelete();
            $table->string('name'); $table->string('original_name'); $table->string('stored_path');
            $table->string('mime_type')->nullable(); $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('status')->default('pending'); $table->text('error_message')->nullable();
            $table->unsignedInteger('chunks_count')->default(0); $table->timestamps();
        });
        Schema::create('document_chunks', function (Blueprint $table): void {
            $table->id(); $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->longText('content'); $table->unsignedInteger('sequence');
            $table->json('vector_json'); $table->unsignedInteger('embedding_dimensions');
            $table->unsignedInteger('character_count'); $table->timestamps();
            $table->unique(['document_id', 'sequence']);
        });
        Schema::create('chat_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->string('title')->nullable(); $table->timestamps();
        });
        Schema::create('chat_messages', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->uuid('session_id'); $table->string('role');
            $table->longText('content'); $table->json('citations')->nullable(); $table->timestamps();
            $table->foreign('session_id')->references('id')->on('chat_sessions')->cascadeOnDelete();
        });
        Schema::create('model_configs', function (Blueprint $table): void {
            $table->id(); $table->string('provider'); $table->string('model'); $table->text('encrypted_secret')->nullable(); $table->boolean('enabled')->default(false); $table->timestamps();
        });
        Schema::create('knowledge_search_feedback', function (Blueprint $table): void {
            $table->id(); $table->uuid('message_id'); $table->string('rating'); $table->text('note')->nullable(); $table->timestamps();
        });
        Schema::create('ai_call_logs', function (Blueprint $table): void {
            $table->id(); $table->string('provider')->nullable(); $table->string('model')->nullable();
            $table->unsignedInteger('latency_ms')->nullable(); $table->string('status');
            $table->text('error_message')->nullable(); $table->timestamps();
        });
        Schema::create('rag_evaluations', function (Blueprint $table): void {
            $table->id(); $table->text('question'); $table->string('expected_keyword')->nullable();
            $table->text('answer')->nullable(); $table->unsignedInteger('citations_count')->default(0);
            $table->unsignedInteger('latency_ms')->nullable(); $table->boolean('passed')->default(false);
            $table->string('provider')->nullable(); $table->timestamps();
        });
        Schema::create('we_com_configs', function (Blueprint $table): void {
            $table->id(); $table->string('name');
            $table->longText('encrypted_webhook');
            $table->boolean('enabled')->default(false); $table->timestamp('last_tested_at')->nullable();
            $table->string('last_test_status')->nullable(); $table->text('last_test_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('we_com_configs');
        Schema::dropIfExists('rag_evaluations');
        Schema::dropIfExists('ai_call_logs');
        Schema::dropIfExists('knowledge_search_feedback');
        Schema::dropIfExists('model_configs');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_sessions');
        Schema::dropIfExists('document_chunks');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('knowledge_categories');
    }
};
