<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PointHistory;
use App\Models\Role;
use App\Models\SatuanKerja;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Carbon\Carbon;

class LeaderboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('id_ID');

        // Pastikan role P-01 (Kontributor) ada
        $role = Role::firstOrCreate(
            ['kode_role' => 'P-01'],
            ['nama_role' => 'Pembuat Berita']
        );

        // Pastikan ada satuan kerja
        $satker = SatuanKerja::firstOrCreate(
            ['id' => 1],
            ['nama_satuan_kerja' => 'BPS Pusat']
        );

        // Buat 15 user dummy
        for ($i = 1; $i <= 15; $i++) {
            $user = User::create([
                'nip_pegawai' => $faker->unique()->numerify('198#######'),
                'nama_lengkap' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'satuan_kerja_id' => $satker->id,
                'role_id' => $role->id,
                'status_aktif' => true,
                // Mengisi poin aktif agar user juga terurut berdasarkan poin ini
                'poin_aktif' => 0,
            ]);

            // Berikan riwayat poin random (1 sampai 5 kali aktivitas per user)
            $jumlahAktivitas = rand(1, 5);
            $totalPoin = 0;
            for ($j = 0; $j < $jumlahAktivitas; $j++) {
                $poin = rand(10, 100);
                $totalPoin += $poin;
                PointHistory::create([
                    'user_id' => $user->id,
                    'jumlah_poin' => $poin,
                    'activity_type' => 'Membuat Berita',
                    'created_at' => Carbon::now()->subDays(rand(0, 30)), // Poin di dapat dalam bulan ini
                    'updated_at' => Carbon::now(),
                ]);
            }

            // Update total poin_aktif user
            $user->update(['poin_aktif' => $totalPoin]);
        }

        $this->command->info('15 Dummy Users with Points created successfully!');
    }
}
