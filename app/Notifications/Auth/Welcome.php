<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Welcome extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ?string $provider = null,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to '.config('app.name'))
            ->greeting("Welcome, {$notifiable->name}!")
            ->line($this->provider
                ? 'Your account was created using your '.ucfirst($this->provider).' account.'
                : 'Your account has been created - welcome aboard!')
            ->action('Get started', url('/'))
            ->line('If you have any questions, feel free to reach out.');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'provider' => $this->provider,
        ];
    }
}
