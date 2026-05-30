<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
        'warna_badge',
        'ikon',
        'urutan_tampilan',
        'status_aktif'
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    // Relasi: 1 Kategori memiliki banyak Berita
    public function news()
    {
        return $this->hasMany(News::class, 'category_id');
    }
}