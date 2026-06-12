<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorialVideoInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tutorial_video_id',
        'event_type',
        'position_seconds',
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
