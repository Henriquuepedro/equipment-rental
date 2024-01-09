<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('admin', function (User $user) {
            return $user->type_user == 1;
        });

        Gate::define('admin-master', function (User $user) {
            return $user->type_user == 2;
        });

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Verifique endereço de e-mail')
                ->line('Por favor clique no botão abaixo para verificar seu endereço de e-mail.')
                ->action('Verificar endereço de e-mail', $url)
                ->line('Se você não criou uma conta, nenhuma ação adicional será necessária.');
        });

        ResetPassword::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Notificação de redefinição de senha')
                ->line('Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para sua conta.')
                ->action('Redefinir senha', $url)
                ->line('Este link de redefinição de senha irá expirar em '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' minutos.')
                ->line('Se você não solicitou uma redefinição de senha, nenhuma ação adicional será necessária.')
                ->view('mail.welcome', ['mail_title' => 'Bem vindo']);
        });
    }
}
