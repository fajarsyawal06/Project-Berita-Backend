<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Penting untuk sistem login API
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    // Mendaftarkan SEMUA kolom yang boleh diisi melalui form/API
    protected $fillable = [
        'nip_pegawai',
        'nama_lengkap',
        'email',
        'password',
        'jabatan_id',
        'satuan_kerja_id',
        'role_id',
        'avatar',
        'status_aktif',
        'last_login_at',
        'last_ip_address',
        'preferences',
    ];

    // Kolom yang disembunyikan saat data User di-convert ke JSON (Keamanan)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Mengatur tipe data bawaan kolom tertentu (Casting)
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status_aktif' => 'boolean',
        'last_login_at' => 'datetime',
        'preferences' => 'array', // JSON di database otomatis jadi Array di PHP
    ];

    /* =========================================
       RELASI DATABASE (Belongs To)
       ========================================= */

    // Relasi balik: User ini memiliki Role apa?
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Relasi balik: User ini menjabat sebagai apa?
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    // Relasi balik: User ini berada di Unit Kerja mana?
    public function satuanKerja()
    {
        return $this->belongsTo(SatuanKerja::class);
    }

   
    // User bisa menyimpan/bookmark banyak berita
    public function savedNews()
    {
        return $this->belongsToMany(News::class, 'saved_news', 'user_id', 'news_id')
                    ->withTimestamps();
    }
}