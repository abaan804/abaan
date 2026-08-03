<?php

namespace Modules\FamilyTree\Policies;

use App\Models\User;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Models\FtMember;

class FtMemberPolicy
{
    public function manage(User $user, FtFamily $family): bool
    {
        return $user->company_id === $family->company_id
            && $user->can('familytree.manage-members');
    }

    public function manageRecord(User $user, FtMember $member): bool
    {
        return $user->company_id === $member->company_id
            && $user->can('familytree.manage-members');
    }
}