<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_id',
        'user_id',
        'old_status',
        'new_status',
        'reason',
    ];

    /* =========================================
       RELASI DATABASE
       ========================================= */

    // Log ini milik berita yang mana?
    public function news()
    {
        return $this->belongsTo(News::class);
    }

    // Siapa aktor yang melakukan perubahan ini?
    public function actor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}