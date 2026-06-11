<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\News;

class NewsSubmittedNotification extends Notification
{
    use Queueable;

    public $news;

    /**
     * Create a new notification instance.
     */
    public function __construct(News $news)
    {
        $this->news = $news;
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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'news_id' => $this->news->id,
            'title' => 'Berita Menunggu Verifikasi',
            'message' => 'Berita "' . $this->news->judul . '" telah disubmit untuk verifikasi.',
            'action_url' => '/berita/' . $this->news->id,
            'type' => 'info'
        ];
    }
}
