<?php

namespace App\Notifications;

use App\Models\Appointment; // Import the Appointment model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAppointmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // We'll pass the new appointment into the notification
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
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // We'll send this notification via email and store it in the database.
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // The patient who booked the appointment
        $user = $this->appointment->user;

        return (new MailMessage)
            ->subject('New Booking Request!')
            ->greeting('Hello, you have a new appointment request.')
            ->line("Name: {$user->name}")
            ->line("Service(s): " . $this->appointment->services->pluck('name')->join(', '))
            ->line("Date & Time: " . $this->appointment->start_time->format('F d, Y @ h:i A'))
            ->action('View Appointment', url('/provider/appointments/' . $this->appointment->id))
            ->line('Please review and confirm this booking in your dashboard.');
    }

    /**
     * Get the array representation of the notification (for the database).
     */
    public function toArray(object $_notifiable): array
    {
        // This is the data that will be stored in the 'notifications' table.
        // Your frontend will fetch this to display in a notification dropdown.
        return [
            'appointment_id' => $this->appointment->id,
            'user_name' => $this->appointment->user->name,
            'title' => 'New Appointment Request',
            'message' => "You have a new booking request from {$this->appointment->user->name}.",
            'type' => 'appointment',
        ];
    }
}