<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'jenis_aktivitas',
        'poin',
        'deskripsi',
        'status_aktif'
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];
}
