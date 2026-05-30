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

    // Nanti ditambahkan relasi ke tabel Video/Artikel Panduan jika sudah dibuat
}