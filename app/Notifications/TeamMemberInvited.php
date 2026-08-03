<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamMemberInvited extends Notification
{
    use Queueable;

    public function __construct(
        protected string $companyName,
        protected string $temporaryPassword
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('You have been added to :company on Abaan', ['company' => $this->companyName]))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('You have been added as a team member for :company.', ['company' => $this->companyName]))
            ->line(__('Your temporary password is: :password', ['password' => $this->temporaryPassword]))
            ->line(__('Please log in and change your password as soon as possible.'))
            ->action(__('Log In'), route('login'))
            ->line(__('If you did not expect this invitation, please contact support.'));
    }
}