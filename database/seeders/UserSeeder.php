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
        // 1. Buat 5 Role Sesuai PRD
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

        // 2. Buat Dummy Satuan Kerja & Jabatan
        DB::table('satuan_kerjas')->updateOrInsert(
            ['id' => 1], 
            ['nama_satuan_kerja' => 'Pusat Jakarta']
        );
        DB::table('jabatans')->updateOrInsert(
            ['id' => 1], 
            ['nama_jabatan' => 'Staf Operasional']
        );

        // 3. Buat 5 Akun User (1 untuk setiap Role)
        $emails = ['kontributor', 'editor', 'kepsat', 'admin', 'viewer'];
        
        foreach ($roles as $index => $role) {
            User::updateOrCreate(
                ['email' => $emails[$index] . '@portal.com'],
                [
                    'nip_pegawai' => '199001012026' . $index,
                    'nama_lengkap' => 'Akun ' . $role['nama_role'],
                    'password' => Hash::make('password123'), // Semua password: password123
                    'role_id' => $role['id'],
                    'satuan_kerja_id' => 1,
                    'jabatan_id' => 1,
                    'status_aktif' => true,
                ]
            );
        }
    }
}
