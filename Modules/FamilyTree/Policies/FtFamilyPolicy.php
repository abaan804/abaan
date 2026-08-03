<?php

namespace Modules\FamilyTree\Policies;

use App\Models\User;
use Modules\FamilyTree\Models\FtFamily;

class FtFamilyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('familytree.view-dashboard');
    }

    public function manage(User $user, FtFamily $family): bool
    {
        return $user->company_id === $family->company_id
            && $user->can('familytree.manage-families');
    }
}