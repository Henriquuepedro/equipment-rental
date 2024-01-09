<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisterCompanyNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)->view('mail.welcome', [
            'mail_title'        => 'Bem-vindo',
            'header_title'      => 'Bem-vindo',
            'description_title' => '',
            'body_title'        => 'Título do corpo do e-mail',
            'body_contents'     => [
                [
                    'title'         => 'Fique por dentro de tudo',
                    'description'   => 'A cada nova atualização, será enviado uma nova mensagem via e-mail. Não perca nada.',
                    'icon'          => [
                        'name'  => 'fa fa-home',
                        'style' => 'color: #000; font-size: 50px'
                    ]
                ]
            ],
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            //
        ];
    }
}
