<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorialArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutorial_category_id',
        'judul',
        'konten',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(TutorialCategory::class, 'tutorial_category_id');
    }

    public function attachments()
    {
        return $this->hasMany(TutorialArticleAttachment::class, 'tutorial_article_id');
    }
}
