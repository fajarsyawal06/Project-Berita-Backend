<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\News;

class NewsStatusUpdatedNotification extends Notification
{
    use Queueable;

    public $news;
    public $status;
    public $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(News $news, $status, $reason = null)
    {
        $this->news = $news;
        $this->status = $status;
        $this->reason = $reason;
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
        $title = 'Status Berita Diperbarui';
        $message = 'Status berita Anda "' . $this->news->judul . '" telah diperbarui menjadi ' . $this->status . '.';
        $type = 'info';

        if ($this->status === 'PUBLISHED') {
            $title = 'Berita Diterbitkan!';
            $message = 'Selamat! Berita Anda "' . $this->news->judul . '" telah disetujui dan dipublikasikan.';
            $type = 'success';
        } elseif ($this->status === 'REJECTED') {
            $title = 'Berita Ditolak';
            $message = 'Berita Anda "' . $this->news->judul . '" dikembalikan ke Draft. Alasan: ' . $this->reason;
            $type = 'error';
        }

        return [
            'news_id' => $this->news->id,
            'title' => $title,
            'message' => $message,
            'action_url' => '/berita/' . $this->news->id,
            'type' => $type
        ];
    }
}
