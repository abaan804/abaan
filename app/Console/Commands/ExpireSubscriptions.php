<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\VideoDownloader\Models\VdDownload;

class ExpireSubscriptions extends Command
{
    protected $signature   = 'subscriptions:expire';
    protected $description = 'Mark expired trial and paid subscriptions as expired';

    public function handle(): int
    {
        // Expire trials whose trial_ends_at has passed
        $trialExpired = \App\Models\Subscription::where('status', 'trial')
            ->where('trial_ends_at', '<', now())
            ->update(['status' => 'expired']);

        // Expire paid subscriptions whose ends_at has passed
        $paidExpired = \App\Models\Subscription::where('status', 'active')
            ->where('ends_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Expired: {$trialExpired} trial(s), {$paidExpired} paid subscription(s).");
        return Command::SUCCESS;
    }
}