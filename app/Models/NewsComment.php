<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_id',
        'user_id',
        'parent_id',
        'content',
    ];

    /**
     * Get the news that owns the comment.
     */
    public function news()
    {
        return $this->belongsTo(News::class);
    }

    /**
     * Get the user that wrote the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment (if this is a reply).
     */
    public function parent()
    {
        return $this->belongsTo(NewsComment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies()
    {
        return $this->hasMany(NewsComment::class, 'parent_id');
    }
}
