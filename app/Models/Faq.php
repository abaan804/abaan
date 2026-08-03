<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_en', 'question_ur', 'question_ar',
        'answer_en', 'answer_ur', 'answer_ar',
        'category', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function translatedQuestion(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->{"question_{$locale}"} ?? $this->question_en;
    }

    public function translatedAnswer(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->{"answer_{$locale}"} ?? $this->answer_en;
    }
}