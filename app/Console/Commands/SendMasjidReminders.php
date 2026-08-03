<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidSeasonMember;
use Modules\Masjid\Services\MasjidNotificationService;
use Modules\Masjid\Services\MasjidSettingService;

class SendMasjidReminders extends Command
{
    protected $signature = 'masjid:send-reminders
                            {--mosque= : Restrict to a specific mosque ID}
                            {--dry-run : Log what would be sent without actually dispatching}';

    protected $description = 'Send balance reminders to all pending/partial Masjid members whose season end date is approaching';

    public function handle(
        MasjidNotificationService $notificationService,
        MasjidSettingService $settingService
    ): int {
        $mosqueId = $this->option('mosque');
        $dryRun = $this->option('dry-run');

        $mosqueQuery = MasjidMosque::where('status', 'active');
        if ($mosqueId) {
            $mosqueQuery->where('id', $mosqueId);
        }

        $mosques = $mosqueQuery->get();

        if ($mosques->isEmpty()) {
            $this->info('No active mosques found.');
            return Command::SUCCESS;
        }

        $totalDispatched = 0;

        foreach ($mosques as $mosque) {
            $setting = $settingService->forMosque($mosque);

            // Skip mosque if no notification channel is enabled
            if (! $setting->notification_email && ! $setting->notification_sms && ! $setting->notification_whatsapp) {
                $this->line("  Skipping {$mosque->mosque_name} — no notification channels enabled.");
                continue;
            }

            $reminderDays = $setting->default_reminder_days;
            $cutoffDate = now()->addDays($reminderDays)->toDateString();

            // Find active seasons for this mosque whose end_date is within reminder window
            $seasons = $mosque->seasons()
                ->where('status', 'active')
                ->where('end_date', '<=', $cutoffDate)
                ->get();

            if ($seasons->isEmpty()) {
                $this->line("  {$mosque->mosque_name}: No seasons within reminder window ({$reminderDays} days).");
                continue;
            }

            $this->info("Processing {$mosque->mosque_name}...");

            foreach ($seasons as $season) {
                $assignments = MasjidSeasonMember::where('season_id', $season->id)
                    ->whereIn('status', ['pending', 'partial'])
                    ->with(['member', 'season'])
                    ->get();

                if ($assignments->isEmpty()) {
                    $this->line("    Season '{$season->name}': No pending members.");
                    continue;
                }

                $this->line("    Season '{$season->name}': {$assignments->count()} pending/partial members.");

                foreach ($assignments as $sm) {
                    $member = $sm->member;

                    if (! $member || ! $member->mobile && ! $member->email) {
                        $this->warn('Skipping ' . ($sm->member?->name ?? 'unknown') . ' — no contact info.');
                        continue;
                    }

                    if ($dryRun) {
                        $this->line("[DRY RUN] Would remind: {$member->name} — Balance: {$sm->balance()}");
                        continue;
                    }

                    $notificationService->sendBalanceReminder(
                        $mosque,
                        $member,
                        $season,
                        $sm->balance()
                    );

                    $totalDispatched++;
                    $this->line("      Dispatched reminder for: {$member->name}");
                }
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info('Dry run complete — no notifications dispatched.');
        } else {
            $this->info("Done. {$totalDispatched} reminder notification(s) dispatched to queue.");
        }

        return Command::SUCCESS;
    }
}