<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Berita
            ['name' => 'news.create', 'module' => 'Berita', 'description' => 'Membuat berita baru'],
            ['name' => 'news.verify', 'module' => 'Berita', 'description' => 'Memverifikasi berita'],
            ['name' => 'news.queue.view', 'module' => 'Berita', 'description' => 'Melihat antrean verifikasi berita'],
            ['name' => 'news.force_publish', 'module' => 'Berita', 'description' => 'Menerbitkan berita secara paksa'],
            
            // Dashboard
            ['name' => 'dashboard.view', 'module' => 'Dashboard', 'description' => 'Melihat dashboard admin'],
            ['name' => 'dashboard.share', 'module' => 'Dashboard', 'description' => 'Membagikan dashboard (Share)'],
            
            // Report
            ['name' => 'reports.view', 'module' => 'Laporan', 'description' => 'Melihat template laporan'],
            ['name' => 'reports.generate', 'module' => 'Laporan', 'description' => 'Men-generate laporan'],
            ['name' => 'reports.adhoc', 'module' => 'Laporan', 'description' => 'Membuat laporan ad-hoc'],
            ['name' => 'reports.national', 'module' => 'Laporan', 'description' => 'Melihat laporan seluruh nasional (tidak terkunci unit)'],
            
            // Master Data & Sistem
            ['name' => 'master_data.manage', 'module' => 'Sistem', 'description' => 'Mengelola Master Data'],
            ['name' => 'tutorial_data.manage', 'module' => 'Sistem', 'description' => 'Mengelola Data Tutorial'],

            // Informasi Tersimpan & Bookmark
            ['name' => 'news.bookmark', 'module' => 'Berita', 'description' => 'Menyimpan berita (Bookmark)'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        // Attach permissions to roles
        $rolePermissions = [
            'P-01' => ['news.create', 'news.bookmark', 'reports.view'],
            'P-02' => ['news.verify', 'news.queue.view', 'reports.view'],
            'P-03' => ['news.queue.view', 'reports.view', 'reports.generate', 'reports.adhoc', 'dashboard.share'],
            'P-04' => ['news.verify', 'news.queue.view', 'news.force_publish', 'news.bookmark', 'reports.view', 'reports.generate', 'reports.adhoc', 'reports.national', 'master_data.manage', 'tutorial_data.manage', 'dashboard.view', 'dashboard.share'],
            'P-05' => []
        ];

        foreach ($rolePermissions as $kodeRole => $perms) {
            $role = \App\Models\Role::where('kode_role', $kodeRole)->first();
            if ($role) {
                $permIds = Permission::whereIn('name', $perms)->pluck('id');
                $role->permissions()->sync($permIds);
            }
        }
    }
}
