<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (Auth::check() && Auth::user()->company_id && ! Auth::user()->is_super_admin) {
                $builder->where($builder->getModel()->getTable() . '.company_id', Auth::user()->company_id);
            }
        });

        static::creating(function ($model) {
            if (empty($model->company_id) && Auth::check() && Auth::user()->company_id) {
                $model->company_id = Auth::user()->company_id;
            }
        });
    }

    /**
     * Allow super admins or specific jobs to bypass the company scope when needed.
     */
    public static function withoutCompanyScope(): Builder
    {
        return static::withoutGlobalScope('company');
    }
}