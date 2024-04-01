<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

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
        return (new MailMessage)
            ->greeting('Caro, ' . $notifiable->contact)
            ->subject('Boas-vindas ao sistema ' . env('APP_NAME'))
            ->line(' ')
            ->line('É com grande prazer que damos as boas-vindas a você ao nosso novo sistema! Estamos entusiasmados por tê-lo(a) a bordo e esperamos que esta plataforma torne sua experiência ainda mais satisfatória e produtiva.')
            ->line(' ')
            ->line('Neste sistema, você encontrará uma variedade de recursos projetados para simplificar suas tarefas diárias, otimizar processos e melhorar a comunicação interna sobre as locações.')
            ->line(' ')
            ->line('Para ajudá-lo(a) a começar, fornecemos um breve guia de uso, que inclui instruções sobre como fazer a primeira locação. Além disso, nossa equipe de suporte estará disponível para ajudá-lo(a) sempre que necessário. Sinta-se à vontade para entrar em contato conosco através do ícone de atendimento no canto superior direito.')
            ->line(' ')
            ->line('Estamos comprometidos em garantir sua satisfação e em fornecer um ambiente de trabalho eficiente e colaborativo. Esperamos que você aproveite ao máximo esta nova ferramenta e que ela contribua significativamente para o seu sucesso e o de nossa organização.')
            ->line(' ')
            ->line('Mais uma vez, seja bem-vindo(a) ao nosso novo sistema! Estamos ansiosos para trabalhar juntos e alcançar grandes conquistas.')
            ->action('Acessar manual do usuário', route('guide.index'));
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
