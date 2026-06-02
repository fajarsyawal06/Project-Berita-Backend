<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_id',
        'user_id',
        'old_status',
        'new_status',
        'reason',
    ];

    protected $appends = [
        'jenis_aktivitas',
        'detail_perubahan'
    ];

    public function getJenisAktivitasAttribute()
    {
        if (empty($this->old_status) && $this->new_status === 'DRAFT') {
            return 'Pembuatan Draft Berita';
        }
        if ($this->old_status === 'DRAFT' && $this->new_status === 'SENT_WAITING_VERIFICATION') {
            return 'Pengajuan Verifikasi (Submit)';
        }
        if ($this->old_status === 'SENT_WAITING_VERIFICATION' && $this->new_status === 'PUBLISHED') {
            return 'Persetujuan (Approve & Publish)';
        }
        if ($this->old_status === 'SENT_WAITING_VERIFICATION' && $this->new_status === 'APPROVED') {
            return 'Persetujuan (Approve)';
        }
        if ($this->old_status === 'SENT_WAITING_VERIFICATION' && $this->new_status === 'DRAFT') {
            return 'Penolakan (Reject)';
        }
        return 'Perubahan Status menjadi ' . $this->new_status;
    }

    public function getDetailPerubahanAttribute()
    {
        if ($this->reason) {
            return $this->reason;
        }
        
        $old = $this->old_status ? $this->old_status : 'Baru';
        return "Status berubah dari [{$old}] menjadi [{$this->new_status}]";
    }

    /* =========================================
       RELASI DATABASE
       ========================================= */

    // Log ini milik berita yang mana?
    public function news()
    {
        return $this->belongsTo(News::class);
    }

    // Siapa aktor yang melakukan perubahan ini?
    public function actor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}