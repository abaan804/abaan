<?php

namespace Modules\FamilyTree\Services;

use App\Services\Notifications\NotificationManager;
use Modules\FamilyTree\Models\FtMember;
use Modules\FamilyTree\Repositories\FtMemberRepository;

class FamilyTreeNotificationService
{
    public function __construct(
        protected NotificationManager $notificationManager,
        protected FtMemberRepository $memberRepo
    ) {
    }

    public function sendBirthdayReminder(FtMember $member): void
    {
        if (! $member->contact_number && ! $member->email) return;

        $age = $member->age ? ' — ' . $member->age . ' ' . __('years old') : '';
        $subject = __('Birthday Reminder — :name', ['name' => $member->full_name]);
        $message = __(
            "Happy Birthday :name!:age\nFamily: :family\nDate of Birth: :dob\n\n:family Family Tree Manager",
            [
                'name' => $member->full_name,
                'age' => $age,
                'family' => $member->family?->name ?? '',
                'dob' => $member->date_of_birth?->format('d M Y') ?? '',
            ]
        );

        $this->sendToMember($member, $subject, $message);
    }

    public function sendDeathAnniversaryReminder(FtMember $member): void
    {
        $subject = __('Death Anniversary — :name', ['name' => $member->full_name]);
        $message = __(
            "Today is the death anniversary of :name.\nDate of Death: :date\nMay their soul rest in peace. Ameen.\n\n:family Family Tree Manager",
            [
                'name' => $member->full_name,
                'date' => $member->date_of_death?->format('d M Y') ?? '',
                'family' => $member->family?->name ?? '',
            ]
        );

        // Death anniversary reminders go to family admin (created_by), not the deceased
        if ($member->createdBy && $member->createdBy->email) {
            $this->notificationManager->send('email', $member->createdBy->email, $subject, $message);
        }
    }

    public function sendUpcomingBirthdays(int $companyId, int $days = 7): void
    {
        $upcoming = $this->memberRepo->upcomingBirthdays($companyId, $days);
        foreach ($upcoming as $member) {
            $this->sendBirthdayReminder($member);
        }
    }

    protected function sendToMember(FtMember $member, string $subject, string $message): void
    {
        if ($member->email) {
            $this->notificationManager->send('email', $member->email, $subject, $message);
        }
        if ($member->contact_number) {
            $this->notificationManager->send('sms', $member->contact_number, $subject, $message);
        }
        if ($member->whatsapp_number) {
            $this->notificationManager->send('whatsapp', $member->whatsapp_number, $subject, $message);
        }
    }
}