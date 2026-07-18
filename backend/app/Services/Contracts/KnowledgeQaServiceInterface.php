<?php

namespace App\Services\Contracts;

interface KnowledgeQaServiceInterface
{
    public function ask(string $question, ?int $categoryId = null, ?string $sessionId = null): array;
}
