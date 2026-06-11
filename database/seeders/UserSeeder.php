<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $roles = [
            ['id' => 1, 'kode_role' => 'P-01', 'nama_role' => 'Pembuat Berita'],
            ['id' => 2, 'kode_role' => 'P-02', 'nama_role' => 'Verifikator'],
            ['id' => 3, 'kode_role' => 'P-03', 'nama_role' => 'Kepala Satuan Kerja'],
            ['id' => 4, 'kode_role' => 'P-04', 'nama_role' => 'Administrator Sistem'],
            ['id' => 5, 'kode_role' => 'P-05', 'nama_role' => 'Viewer Umum'],
        ];
        
        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(['id' => $role['id']], $role);
        }

        // 2. Realistic Satuan Kerja
        $satuanKerjas = [
            // Level 1: Pusat
            ['id' => 1, 'kode_unik' => 'SAT-JKT', 'nama_satuan_kerja' => 'Kantor Pusat Jakarta', 'level' => 1, 'parent_id' => null, 'provinsi_wilayah' => 'DKI Jakarta'],
            
            // Level 2: Wilayah / Provinsi
            ['id' => 2, 'kode_unik' => 'SAT-JBR', 'nama_satuan_kerja' => 'Kantor Wilayah Jawa Barat', 'level' => 2, 'parent_id' => 1, 'provinsi_wilayah' => 'Jawa Barat'],
            ['id' => 3, 'kode_unik' => 'SAT-JTM', 'nama_satuan_kerja' => 'Kantor Wilayah Jawa Timur', 'level' => 2, 'parent_id' => 1, 'provinsi_wilayah' => 'Jawa Timur'],
            ['id' => 4, 'kode_unik' => 'SAT-SUL', 'nama_satuan_kerja' => 'Kantor Wilayah Sulawesi Selatan', 'level' => 2, 'parent_id' => 1, 'provinsi_wilayah' => 'Sulawesi Selatan'],
            ['id' => 5, 'kode_unik' => 'SAT-BAL', 'nama_satuan_kerja' => 'Kantor Wilayah Bali', 'level' => 2, 'parent_id' => 1, 'provinsi_wilayah' => 'Bali'],
            ['id' => 6, 'kode_unik' => 'SAT-SMT', 'nama_satuan_kerja' => 'Kantor Wilayah Sumatera Utara', 'level' => 2, 'parent_id' => 1, 'provinsi_wilayah' => 'Sumatera Utara'],

            // Level 3: Cabang / Kota / Kabupaten
            ['id' => 7, 'kode_unik' => 'SAT-BND', 'nama_satuan_kerja' => 'Kantor Cabang Kota Bandung', 'level' => 3, 'parent_id' => 2, 'provinsi_wilayah' => 'Jawa Barat'],
            ['id' => 8, 'kode_unik' => 'SAT-BGR', 'nama_satuan_kerja' => 'Kantor Cabang Kabupaten Bogor', 'level' => 3, 'parent_id' => 2, 'provinsi_wilayah' => 'Jawa Barat'],
            ['id' => 9, 'kode_unik' => 'SAT-SBY', 'nama_satuan_kerja' => 'Kantor Cabang Kota Surabaya', 'level' => 3, 'parent_id' => 3, 'provinsi_wilayah' => 'Jawa Timur'],
            ['id' => 10, 'kode_unik' => 'SAT-MLG', 'nama_satuan_kerja' => 'Kantor Cabang Kota Malang', 'level' => 3, 'parent_id' => 3, 'provinsi_wilayah' => 'Jawa Timur'],
            ['id' => 11, 'kode_unik' => 'SAT-MKS', 'nama_satuan_kerja' => 'Kantor Cabang Kota Makassar', 'level' => 3, 'parent_id' => 4, 'provinsi_wilayah' => 'Sulawesi Selatan'],
            ['id' => 12, 'kode_unik' => 'SAT-DPS', 'nama_satuan_kerja' => 'Kantor Cabang Kota Denpasar', 'level' => 3, 'parent_id' => 5, 'provinsi_wilayah' => 'Bali'],
            ['id' => 13, 'kode_unik' => 'SAT-MDN', 'nama_satuan_kerja' => 'Kantor Cabang Kota Medan', 'level' => 3, 'parent_id' => 6, 'provinsi_wilayah' => 'Sumatera Utara'],
        ];

        foreach ($satuanKerjas as $satker) {
            DB::table('satuan_kerjas')->updateOrInsert(['id' => $satker['id']], $satker);
        }

        // 3. Realistic Jabatans
        $jabatans = [
            ['id' => 1, 'kode_jabatan' => 'JAB-DIR', 'nama_jabatan' => 'Direktur Utama', 'level_hierarki' => 1, 'satuan_kerja_id' => 1, 'deskripsi' => 'Pemimpin tertinggi'],
            ['id' => 2, 'kode_jabatan' => 'JAB-KAD', 'nama_jabatan' => 'Kepala Kantor Daerah', 'level_hierarki' => 2, 'satuan_kerja_id' => 2, 'deskripsi' => 'Kepala cabang wilayah'],
            ['id' => 3, 'kode_jabatan' => 'JAB-HUM', 'nama_jabatan' => 'Staf Humas', 'level_hierarki' => 3, 'satuan_kerja_id' => 1, 'deskripsi' => 'Pengelola informasi publik'],
            ['id' => 4, 'kode_jabatan' => 'JAB-IT', 'nama_jabatan' => 'Staf IT', 'level_hierarki' => 3, 'satuan_kerja_id' => 1, 'deskripsi' => 'Pengelola sistem informasi'],
            ['id' => 5, 'kode_jabatan' => 'JAB-UMM', 'nama_jabatan' => 'Staf Umum', 'level_hierarki' => 4, 'satuan_kerja_id' => 3, 'deskripsi' => 'Staf operasional'],
        ];

        foreach ($jabatans as $jabatan) {
            DB::table('jabatans')->updateOrInsert(['id' => $jabatan['id']], $jabatan);
        }

        // 4. Realistic Users
        $users = [
            [
                'email' => 'kontributor@portal.com',
                'nip_pegawai' => '19900101202601',
                'nama_lengkap' => 'Andi Saputra',
                'role_id' => 1, // Pembuat Berita
                'satuan_kerja_id' => 2,
                'jabatan_id' => 3,
            ],
            [
                'email' => 'editor@portal.com',
                'nip_pegawai' => '19850202202602',
                'nama_lengkap' => 'Budi Santoso',
                'role_id' => 2, // Verifikator
                'satuan_kerja_id' => 1,
                'jabatan_id' => 3,
            ],
            [
                'email' => 'kepsat@portal.com',
                'nip_pegawai' => '19750303202603',
                'nama_lengkap' => 'Siti Aminah',
                'role_id' => 3, // Kepala Satker
                'satuan_kerja_id' => 2,
                'jabatan_id' => 2,
            ],
            [
                'email' => 'admin@portal.com',
                'nip_pegawai' => '19920404202604',
                'nama_lengkap' => 'Reza Pratama',
                'role_id' => 4, // Admin Sistem
                'satuan_kerja_id' => 1,
                'jabatan_id' => 4,
            ],
            [
                'email' => 'viewer@portal.com',
                'nip_pegawai' => '19950505202605',
                'nama_lengkap' => 'Dewi Lestari',
                'role_id' => 5, // Viewer
                'satuan_kerja_id' => 3,
                'jabatan_id' => 5,
            ]
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                array_merge($user, [
                    'password' => Hash::make('password123'),
                    'status_aktif' => true,
                ])
            );
        }
    }
}
