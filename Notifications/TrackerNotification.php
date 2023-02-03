<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

class TrackerNotification extends Notification
{
    use Queueable;

    /**
     * The notifiable user.
     *
     * @var array $data
     */
    public array $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(mixed $notifiable)
    {
        $channels = ['database'];

        if ($notifiable->is_get_notifications) {
            $channels[] = FcmChannel::class;
        }

        return $channels;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase(mixed $notifiable)
    {
        return [
            'type'              => $this->data['type'],
            'icon'              => $this->data['icon'],
            'title_ru'          => __($this->data['title_code'], $this->data['title_params'], 'ru'),
            'body_ru'           => __($this->data['body_code'], $this->data['body_params'], 'ru'),
            'title_uz_latin'    => __($this->data['title_code'], $this->data['title_params'], 'uz_latin'),
            'body_uz_latin'     => __($this->data['body_code'], $this->data['body_params'], 'uz_latin'),
            'title_uz_cyrillic' => __($this->data['title_code'], $this->data['title_params'], 'uz_cyrillic'),
            'body_uz_cyrillic'  => __($this->data['body_code'], $this->data['body_params'], 'uz_cyrillic'),
        ];
    }

    /**
     * Отправить push-уведомление посредством Firebase.
     *
     * @param mixed $notifiable
     *
     * @return FcmMessage
     */
    public function toFcm(mixed $notifiable): FcmMessage
    {
        return FcmMessage::create()
            ->setNotification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->setTitle(
                        __(
                            $this->data['title_code'],
                            $this->data['title_params'],
                            'ru'
                        )
                    )
                    ->setBody(
                        __(
                            $this->data['body_code'],
                            $this->data['body_params'],
                            'ru'
                        )
                    )
            );
    }
}
