<?php

namespace App\Services;

use App\Models\User;
use App\Models\PointConfiguration;
use App\Models\PointHistory;

class PointService
{
    /**
     * Berikan poin ke user berdasarkan jenis aktivitas yang ada di konfigurasi
     *
     * @param User $user
     * @param string $jenisAktivitas
     * @param string|null $deskripsiOpsional
     * @return PointHistory|null
     */
    public static function awardPoint(User $user, string $jenisAktivitas, $deskripsiOpsional = null)
    {
        // Cari konfigurasi poin berdasarkan jenis aktivitas
        $config = PointConfiguration::where('jenis_aktivitas', $jenisAktivitas)->first();

        // Jika konfigurasi tidak ditemukan, atau statusnya tidak aktif, atau poinnya 0, abaikan
        if (!$config || !$config->status_aktif || $config->poin == 0) {
            return null;
        }

        // Simpan history perolehan poin
        return PointHistory::create([
            'user_id' => $user->id,
            'jumlah_poin' => $config->poin,
            'deskripsi' => $deskripsiOpsional ?? $config->deskripsi,
        ]);
    }
}
