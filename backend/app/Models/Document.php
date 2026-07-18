<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = ['category_id', 'name', 'original_name', 'stored_path', 'mime_type', 'size_bytes', 'status', 'error_message', 'chunks_count'];
    protected $casts = ['size_bytes' => 'integer', 'chunks_count' => 'integer'];
    protected $hidden = ['stored_path'];
    public function category(): BelongsTo { return $this->belongsTo(KnowledgeCategory::class, 'category_id'); }
    public function chunks(): HasMany { return $this->hasMany(DocumentChunk::class); }
}
