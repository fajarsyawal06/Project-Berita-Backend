<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorialVideoView extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tutorial_video_id',
        'watch_time_seconds',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function video()
    {
        return $this->belongsTo(TutorialVideo::class, 'tutorial_video_id');
    }
}
