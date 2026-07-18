<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RagEvaluation extends Model
{
    protected $fillable = ['question', 'expected_keyword', 'answer', 'citations_count', 'latency_ms', 'passed', 'provider'];
    protected $casts = ['citations_count' => 'integer', 'latency_ms' => 'integer', 'passed' => 'boolean'];
}
