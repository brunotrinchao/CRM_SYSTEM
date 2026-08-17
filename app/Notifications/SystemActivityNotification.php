<?php

namespace App\Notifications;

use App\Filament\Actions\SimpleActions;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class SystemActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $title,
        protected string $body,
        protected string $icon,
        protected string $color = 'primary',
        protected ?Action $action = null
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    public function toDatabase($notifiable): array
    {
        $notify = FilamentNotification::make()
            ->title($this->title)
            ->body($this->body)
            ->icon($this->icon)
            ->iconColor($this->color);

        // Aplica o método de cor correspondente (substituindo '->' por '=>')
        $notify = match ($this->color) {
            'warning' => $notify->warning(),
            'success' => $notify->success(),
            'danger' => $notify->danger(),
            default => $notify->info(),
        };

        if($this->action){
            $notify->actions([
                $this->action
            ]);
        }

        // Retorna o array gerado para o banco de dados no final
        return $notify->getDatabaseMessage();
    }
}
