<?php

namespace Modules\Masjid\Policies;

use App\Models\User;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidPayment;

class MasjidPaymentPolicy
{
    public function manage(User $user, MasjidMosque $mosque): bool
    {
        return $user->company_id === $mosque->company_id
            && $user->can('masjid.manage-payments');
    }

    public function manageRecord(User $user, MasjidPayment $payment): bool
    {
        return $user->company_id === $payment->company_id
            && $user->can('masjid.manage-payments');
    }
}