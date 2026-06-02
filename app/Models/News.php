<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{
    use HasFactory, SoftDeletes;

    // Mendaftarkan kolom yang diizinkan untuk Mass Assignment
    protected $fillable = [
        'user_id',
        'category_id',
        'satuan_kerja_id',
        'judul',
        'slug',
        'what_content',
        'who_involved',
        'when_occurred',
        'where_location',
        'why_happened',
        'how_resolved',
        'latitude',
        'longitude',
        'location_address',
        'status',
        'views_count',
        'shares_count',
        'comments_count',
    ];

    // Memastikan tipe data kembalian (casting) sesuai
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'views_count' => 'integer',
        'shares_count' => 'integer',
        'comments_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        // Event 'creating' berjalan tepat SEBELUM data di-insert ke database
        static::creating(function ($model) {
            if (empty($model->kode_berita)) {
                $year = date('Y'); // Ambil tahun saat ini (misal: 2026)
                
                // Cari berita terakhir di tahun yang sama
                $lastNews = self::whereYear('created_at', $year)->latest('id')->first();
                
                // Jika ada, tambahkan 1. Jika tidak, mulai dari 1.
                $sequence = $lastNews ? intval(substr($lastNews->kode_berita, -3)) + 1 : 1;
                
                // Format menjadi: NEWS-2026-001
                $model->kode_berita = 'NEWS-' . $year . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    /* =========================================
       RELASI DATABASE
       ========================================= */

    // 1 Berita dimiliki oleh 1 Pembuat (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 1 Berita masuk ke 1 Kategori
    public function category()
    {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    // 1 Berita merujuk pada 1 Satuan Kerja pembuatnya
    public function satuanKerja()
    {
        return $this->belongsTo(SatuanKerja::class);
    }

    // 1 Berita bisa memiliki banyak Lampiran (Gambar/Video/Dokumen)
    public function attachments()
    {
        return $this->hasMany(NewsAttachment::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(NewsStatusLog::class)->orderBy('created_at', 'desc');
    }


}