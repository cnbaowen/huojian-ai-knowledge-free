<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeSearchFeedback extends Model
{
    protected $table = 'knowledge_search_feedback';
    protected $fillable = ['message_id', 'rating', 'note'];
}
