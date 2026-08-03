<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    use HasFactory;

    protected $fillable = [
        'seoable_type', 'seoable_id',
        'meta_title_en', 'meta_title_ur', 'meta_title_ar',
        'meta_description_en', 'meta_description_ur', 'meta_description_ar',
        'keywords_en', 'keywords_ur', 'keywords_ar', 'og_image',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}