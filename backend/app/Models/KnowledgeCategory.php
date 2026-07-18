<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeCategory extends Model
{
    protected $fillable = ['name'];
    public function documents(): HasMany { return $this->hasMany(Document::class, 'category_id'); }
}
