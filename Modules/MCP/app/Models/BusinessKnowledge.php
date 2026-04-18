<?php

namespace Modules\MCP\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class BusinessKnowledge extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'business_knowledge_base';

    public $translatable = ['question', 'answer'];

    protected $fillable = [
        'knowledgeable_type',
        'knowledgeable_id',
        'category',
        'subcategory',
        'question',
        'answer',
        'keywords',
        'tags',
        'priority',
        'is_active',
        'usage_count',
        'last_used_at',
        'metadata',
    ];

    protected $casts = [
        'keywords' => 'array',
        'tags' => 'array',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the owning knowledgeable model
     */
    public function knowledgeable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for active knowledge
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by subcategory
     */
    public function scopeBySubcategory($query, string $subcategory)
    {
        return $query->where('subcategory', $subcategory);
    }

    /**
     * Scope for high priority
     */
    public function scopeHighPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Scope for popular knowledge
     */
    public function scopePopular($query)
    {
        return $query->orderBy('usage_count', 'desc');
    }

    /**
     * Search knowledge base
     */
    public function scopeSearch($query, string $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('question', 'like', "%{$searchTerm}%")
                ->orWhere('answer', 'like', "%{$searchTerm}%")
                ->orWhereJsonContains('keywords', $searchTerm)
                ->orWhereJsonContains('tags', $searchTerm);
        });
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Toggle active status
     */
    public function toggleActive(): bool
    {
        return $this->update(['is_active' => ! $this->is_active]);
    }
}
