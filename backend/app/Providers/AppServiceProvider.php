<?php

namespace App\Providers;

use App\Services\Contracts\KnowledgeQaServiceInterface;
use App\Services\FreeKnowledgeQaService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(KnowledgeQaServiceInterface::class, FreeKnowledgeQaService::class);
    }
}
