<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToMosque
{
    protected static function bootBelongsToMosque(): void
    {
        static::addGlobalScope('mosque', function (Builder $builder) {
            if (request()->route('mosque')) {
                $mosqueId = request()->route('mosque') instanceof \Modules\Masjid\Models\MasjidMosque
                    ? request()->route('mosque')->id
                    : request()->route('mosque');

                $builder->where($builder->getModel()->getTable() . '.mosque_id', $mosqueId);
            }
        });

        static::creating(function ($model) {
            if (empty($model->mosque_id) && request()->route('mosque')) {
                $mosque = request()->route('mosque');
                $model->mosque_id = $mosque instanceof \Modules\Masjid\Models\MasjidMosque
                    ? $mosque->id
                    : $mosque;
            }
        });
    }

    public static function withoutMosqueScope(): Builder
    {
        return static::withoutGlobalScope('mosque');
    }
}