<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiCallLog extends Model
{
    protected $fillable = ['provider', 'model', 'latency_ms', 'status', 'error_message'];
}
