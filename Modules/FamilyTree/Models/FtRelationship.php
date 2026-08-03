<?php

namespace Modules\FamilyTree\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FtRelationship extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'ft_relationships';

    protected $fillable = [
        'company_id',
        'member_id',
        'related_member_id',
        'relationship_type',
        'label',
        'notes',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function member(): BelongsTo
    {
        return $this->belongsTo(FtMember::class, 'member_id');
    }

    public function relatedMember(): BelongsTo
    {
        return $this->belongsTo(FtMember::class, 'related_member_id');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    /**
     * Human-readable label for this relationship.
     * Uses custom label for 'custom' type, otherwise returns the enum display name.
     */
    public function getDisplayLabelAttribute(): string
    {
        if ($this->relationship_type === 'custom' && $this->label) {
            return $this->label;
        }

        return match ($this->relationship_type) {
            'adoptive_father' => __('Adoptive Father'),
            'adoptive_mother' => __('Adoptive Mother'),
            'step_father' => __('Step Father'),
            'step_mother' => __('Step Mother'),
            'guardian' => __('Guardian'),
            'foster_child' => __('Foster Child'),
            default => ucfirst(str_replace('_', ' ', $this->relationship_type)),
        };
    }
}