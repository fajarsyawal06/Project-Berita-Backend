<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_jabatan',
        'level_hierarki',
        'deskripsi'
    ];

    // Relasi: Satu Jabatan bisa dimiliki oleh banyak User
    public function users()
    {
        return $this->hasMany(User::class);
    }
}