<?php

namespace CryptoUnifier\JetstreamPlus\Notifications;

use CryptoUnifier\Helpers\{IpAddress, UserAgent};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLocationConfirmation extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Request $request, protected string $confirmationCode)
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
        $ipInfo      = IpAddress::find($this->request->ip());
        $userAgent   = new UserAgent($this->request->userAgent());
        $currentDate = '**' . now() . '**';

        $mail = (new MailMessage())
            ->subject(__('New Location Confirmation'))
            ->line(__('We noticed there was an attempt to access your account from a new location. For successful login, please authorize using the following verification code:'))
            ->line(' ')
            ->line('# ' . $this->confirmationCode)
            ->line(' ');

        // add location only if ip address service is online
        $mail = ($ipInfo->location)
            ? $mail->line(__('Request made from: :location at :date.', ['location' => "**{$ipInfo->location}**", 'date' => $currentDate]))
            : $mail->line(__('Request made at :date.', ['date' => $currentDate]));

        return $mail->line(__('Device: :platform (:browser).', ['platform' => "**{$userAgent->platformName()}**", 'browser' => "**{$userAgent->browserName()}**"]))
            ->line(__('IP Address: :ip.', ['ip' => "**{$ipInfo->ip_address}**"]))
            ->line(__('If you did not initiate this request, please change your account password immediately.'));
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
