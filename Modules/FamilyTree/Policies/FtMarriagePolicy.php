<?php

namespace Modules\FamilyTree\Policies;

use App\Models\User;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Models\FtMarriage;

class FtMarriagePolicy
{
    public function manage(User $user, FtFamily $family): bool
    {
        return $user->company_id === $family->company_id
            && $user->can('familytree.manage-relationships');
    }

    public function manageRecord(User $user, FtMarriage $marriage): bool
    {
        return $user->company_id === $marriage->company_id
            && $user->can('familytree.manage-relationships');
    }
}