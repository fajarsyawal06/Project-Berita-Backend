<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsDailyView extends Model
{
    protected $fillable = [
        'news_id',
        'view_date',
        'views_count',
    ];

    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
