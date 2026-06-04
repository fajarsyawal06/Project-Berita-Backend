<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\SeasonWinner;
use Carbon\Carbon;

class GenerateSeasonWinners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-season-winners {--tahun=} {--semester=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and freeze top 3 season winners for a given semester';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tahun = $this->option('tahun');
        $semester = $this->option('semester');

        $now = Carbon::now();

        // Jika tidak di-supply via argument, deteksi otomatis berdasarkan bulan saat ini
        if (!$tahun || !$semester) {
            // Logika standar cron: berjalan tiap 1 Juli (Semester 1 berakhir) atau 1 Jan (Semester 2 berakhir)
            if ($now->month == 7) {
                $tahun = $now->year;
                $semester = 'Semester 1';
            } elseif ($now->month == 1) {
                $tahun = $now->year - 1; // Semester 2 tahun lalu
                $semester = 'Semester 2';
            } else {
                $this->error("Bukan waktu yang tepat untuk generate otomatis (harus Januari atau Juli). Gunakan opsi --tahun dan --semester untuk manual.");
                return;
            }
        }

        $this->info("Menghitung pemenang untuk $semester Tahun $tahun...");

        // Tentukan rentang tanggal
        if ($semester === 'Semester 1') {
            $startDate = Carbon::create($tahun, 1, 1)->startOfDay();
            $endDate = Carbon::create($tahun, 6, 30)->endOfDay();
        } elseif ($semester === 'Semester 2') {
            $startDate = Carbon::create($tahun, 7, 1)->startOfDay();
            $endDate = Carbon::create($tahun, 12, 31)->endOfDay();
        } else {
            $this->error("Format semester tidak valid. Gunakan 'Semester 1' atau 'Semester 2'.");
            return;
        }

        // Cek apakah sudah pernah di-generate
        $exists = SeasonWinner::where('tahun', $tahun)->where('semester', $semester)->exists();
        if ($exists) {
            $this->warn("Pemenang untuk $semester Tahun $tahun sudah ada di database. Menghapus data lama...");
            SeasonWinner::where('tahun', $tahun)->where('semester', $semester)->delete();
        }

        // Query top 3 pengguna
        // Catatan: Kriteria FR-PG-04 tidak secara spesifik membatasi Role (misal hanya Kontributor). 
        // Jika butuh dibatasi, kita bisa tambahkan whereHas('role', ...) di sini. 
        // Saya akan mengambil semua user yang memiliki poin tertinggi.
        $topUsers = User::with('satuanKerja')
            ->withSum(['pointHistories as total_poin_periode' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }], 'jumlah_poin')
            ->having('total_poin_periode', '>', 0) // Pastikan punya poin
            ->orderByRaw('COALESCE(total_poin_periode, 0) DESC')
            ->take(3)
            ->get();

        if ($topUsers->isEmpty()) {
            $this->info("Tidak ada pengguna yang memiliki poin pada periode tersebut.");
            return;
        }

        // Freeze data ke season_winners
        $peringkat = 1;
        foreach ($topUsers as $user) {
            SeasonWinner::create([
                'user_id' => $user->id,
                'tahun' => $tahun,
                'semester' => $semester,
                'peringkat' => $peringkat,
                'total_poin' => (int) $user->total_poin_periode,
                'nama_lengkap_snapshot' => $user->nama_lengkap,
                'satuan_kerja_snapshot' => $user->satuanKerja ? $user->satuanKerja->nama : null,
                'avatar_snapshot' => $user->avatar,
            ]);

            $this->line("Peringkat {$peringkat}: {$user->nama_lengkap} - Poin: {$user->total_poin_periode}");
            $peringkat++;
        }

        $this->info("Berhasil menyimpan Highlight Pemenang Semester!");
    }
}
