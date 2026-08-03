<?php

namespace Modules\Masjid\Policies;

use App\Models\User;
use Modules\Masjid\Models\MasjidMosque;

class MasjidMosquePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('masjid.view-dashboard');
    }

    public function manage(User $user, MasjidMosque $mosque): bool
    {
        return $user->company_id === $mosque->company_id
            && $user->can('masjid.manage-mosque-profile');
    }
}