<?php

namespace Modules\Masjid\Policies;

use App\Models\User;
use Modules\Masjid\Models\MasjidMember;
use Modules\Masjid\Models\MasjidMosque;

class MasjidMemberPolicy
{
    public function manage(User $user, MasjidMosque $mosque): bool
    {
        return $user->company_id === $mosque->company_id
            && $user->can('masjid.manage-members');
    }

    public function manageRecord(User $user, MasjidMember $member): bool
    {
        return $user->company_id === $member->company_id
            && $user->can('masjid.manage-members');
    }
}