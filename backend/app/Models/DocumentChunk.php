<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentChunk extends Model
{
    protected $fillable = ['document_id', 'content', 'sequence', 'vector_json', 'embedding_dimensions', 'character_count'];
    protected $hidden = ['vector_json'];
    protected $casts = ['vector_json' => 'array', 'sequence' => 'integer', 'embedding_dimensions' => 'integer', 'character_count' => 'integer'];
    public function document(): BelongsTo { return $this->belongsTo(Document::class); }
}
