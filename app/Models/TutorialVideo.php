<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorialVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'deskripsi',
        'url_video',
        'thumbnail',
        'tutorial_category_id',
    ];

    public function category()
    {
        return $this->belongsTo(TutorialCategory::class, 'tutorial_category_id');
    }

    public function views()
    {
        return $this->hasMany(TutorialVideoView::class);
    }

    public function comments()
    {
        return $this->hasMany(TutorialVideoComment::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_tutorial_video');
    }
}
