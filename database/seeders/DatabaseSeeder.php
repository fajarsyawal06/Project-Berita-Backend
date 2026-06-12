<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            NewsSeeder::class,
            NewsDailyViewSeeder::class,
            SeasonWinnerSeeder::class,
            AnalyticsSeeder::class,
            ReportTemplateSeeder::class,
            PointHistorySeeder::class,
            LeaderboardSeeder::class,
            PermissionSeeder::class,
            PointConfigurationSeeder::class,
            FeatureToggleSeeder::class,
        ]);
    }
}
