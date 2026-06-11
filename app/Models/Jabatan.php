<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_jabatan',
        'nama_jabatan',
        'level_hierarki',
        'satuan_kerja_id',
        'deskripsi'
    ];

    // Relasi: Satu Jabatan bisa dimiliki oleh banyak User
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Relasi: Satu Jabatan bisa terkait dengan satu Satuan Kerja Induk
    public function satuanKerja()
    {
        return $this->belongsTo(SatuanKerja::class, 'satuan_kerja_id');
    }
}