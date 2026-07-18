<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'session_id', 'role', 'content', 'citations'];
    protected $casts = ['citations' => 'array'];
}
