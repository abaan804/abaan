<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PageContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_key', 'section_key', 'title_en', 'title_ur', 'title_ar',
        'content_en', 'content_ur', 'content_ar', 'image',
        'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function seoMeta(): MorphMany
    {
        return $this->morphMany(SeoMeta::class, 'seoable');
    }

    public function translatedTitle(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->{"title_{$locale}"} ?? $this->title_en;
    }

    public function translatedContent(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->{"content_{$locale}"} ?? $this->content_en;
    }

    /**
     * Fetch all active sections for a page, keyed by section_key, with safe fallback access.
     */
    public static function forPage(string $pageKey): \Illuminate\Support\Collection
    {
        return static::where('page_key', $pageKey)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('section_key');
    }
}