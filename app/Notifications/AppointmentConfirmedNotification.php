<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Appointment $appointment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $provider = $this->appointment->provider;

        return (new MailMessage)
            ->subject('Your Appointment is Confirmed!')
            ->greeting('Hello, your booking has been confirmed.')
            ->line("Provider: {$provider->healthcare_name}")
            ->line("Date & Time: " . $this->appointment->start_time->format('F d, Y @ h:i A'))
            ->action('View My Appointments', url('/user/appointments'))
            ->line('We look forward to seeing you!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $_notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'provider_name' => $this->appointment->provider->healthcare_name,
            'title' => 'Appointment Confirmed',
            'message' => "Your appointment with {$this->appointment->provider->healthcare_name} has been confirmed.",
            'type' => 'appointment',
        ];
    }
}