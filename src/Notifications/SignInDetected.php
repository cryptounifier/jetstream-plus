<?php

namespace CryptoUnifier\JetstreamPlus\Notifications;

use CryptoUnifier\JetstreamPlus\{IpAddress, UserAgent};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SignInDetected extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $ipInfo      = IpAddress::currentUser();
        $userAgent   = UserAgent::currentUser();
        $currentDate = '**' . now() . '**';

        $mail = (new MailMessage())
            ->subject(__('Sign in to your Account Detected'))
            ->line(__('We just detected a new sign in to your account. We\'re sending you this e-mail to make sure it was you.'))
            ->line('SKIP_LINE');

        // add location only if ip address service is online
        $mail = ($ipInfo->location)
            ? $mail->line(__('Request made from: :location at :date.', ['location' => "**{$ipInfo->location}**", 'date' => $currentDate]))
            : $mail->line(__('Request made at :date.', ['date' => $currentDate]));

        return $mail->line(__('Device: :platform (:browser).', ['platform' => "**{$userAgent->platformName()}**", 'browser' => "**{$userAgent->browserName()}**"]))
            ->line(__('IP Address: :ip.', ['ip' => "**{$ipInfo->ip_address}**"]))
            ->action(__('Secure Your Account'), route('profile.show'))
            ->line(__('If you did not initiate this request, please secure your account immediately.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
        ];
    }
}
