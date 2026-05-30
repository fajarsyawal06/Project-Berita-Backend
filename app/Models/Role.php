<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // Kolom yang diizinkan untuk diisi secara massal (Mass Assignment)
    protected $fillable = [
        'kode_role',
        'nama_role',
        'deskripsi'
    ];

    // Relasi: Satu Role bisa dimiliki oleh banyak User (One-to-Many)
    public function users()
    {
        return $this->hasMany(User::class);
    }
}