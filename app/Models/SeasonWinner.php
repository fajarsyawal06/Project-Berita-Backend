<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeasonWinner extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tahun',
        'semester',
        'peringkat',
        'total_poin',
        'nama_lengkap_snapshot',
        'satuan_kerja_snapshot',
        'avatar_snapshot',
    ];

    /**
     * Relasi opsional ke user yang asli, jika masih ada di sistem.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
