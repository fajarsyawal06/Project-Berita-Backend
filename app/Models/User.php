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

    /**
     * Relasi Many-to-Many ke tabel badges
     */
    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badge')
                    ->withPivot('tanggal_peroleh'); // Membawa serta data tanggal peroleh dari tabel pivot
    }

    /**
     * Relasi One-to-Many ke tabel news (Berita yang dibuat user ini)
     */
    public function news()
    {
        // Catatan: Ganti 'user_id' menjadi 'author_id' jika kolom pembuat di tabel news milikmu berbeda.
        return $this->hasMany(News::class, 'user_id'); 
    }

    /**
     * Relasi One-to-Many ke tabel point_histories
     */
    public function pointHistories()
    {
        return $this->hasMany(PointHistory::class);
    }
}