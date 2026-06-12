<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeatureToggleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                'slug' => 'voice_to_text',
                'name' => 'Voice to Text (Input Suara)',
                'description' => 'Mengaktifkan fitur input suara pada form pengisian 5W+1H saat membuat atau mengedit berita.',
                'is_active' => true,
                'target_role' => null, // Aktif untuk semua role
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'slug' => 'advanced_dashboard',
                'name' => 'Advanced Dashboard',
                'description' => 'Mengaktifkan mode pengaturan dashboard canggih yang dapat dikustomisasi.',
                'is_active' => true,
                'target_role' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        foreach ($features as $feature) {
            DB::table('feature_toggles')->updateOrInsert(
                ['slug' => $feature['slug']],
                $feature
            );
        }
    }
}
