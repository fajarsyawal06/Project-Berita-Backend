<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatuanKerja extends Model
{
    use HasFactory;

    // Menyesuaikan dengan kolom migration yang paling lengkap
    protected $fillable = [
        'kode_unik',
        'nama_satuan_kerja',
        'provinsi_wilayah',
        'level',
        'parent_id',
        'status_aktif'
    ];

    // Relasi: Satu Satuan Kerja memiliki banyak User
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Relasi Hierarki: Mengambil Data Induk (Parent)
    public function parent()
    {
        return $this->belongsTo(SatuanKerja::class, 'parent_id');
    }

    // Relasi Hierarki: Mengambil Data Anak (Children)
    public function children()
    {
        return $this->hasMany(SatuanKerja::class, 'parent_id');
    }
}