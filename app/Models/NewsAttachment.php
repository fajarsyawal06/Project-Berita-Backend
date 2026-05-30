<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_id',
        'file_type',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size_bytes',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
    ];

    /* =========================================
       RELASI DATABASE
       ========================================= */

    // Banyak Lampiran dimiliki oleh 1 Berita
    public function news()
    {
        return $this->belongsTo(News::class);
    }
}