<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorialCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
        'ikon',
        'urutan_tampilan'
    ];

    public function videos()
    {
        return $this->hasMany(TutorialVideo::class, 'tutorial_category_id');
    }

    public function articles()
    {
        return $this->hasMany(TutorialArticle::class, 'tutorial_category_id');
    }
}