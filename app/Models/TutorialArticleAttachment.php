<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorialArticleAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutorial_article_id',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size_bytes',
    ];

    public function article()
    {
        return $this->belongsTo(TutorialArticle::class, 'tutorial_article_id');
    }
}
