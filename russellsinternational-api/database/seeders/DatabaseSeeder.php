<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            HomePageSectionSeeder::class,
            LanguageProgramSeeder::class,
            HeroSlideSeeder::class,
            CourseSeeder::class,
            JobSeeder::class,
            EventSeeder::class,
            TestimonialSeeder::class,
            ProductionContentBackfillSeeder::class,
        ]);

        $this->command->info('✅  Database seeded with Russell\'s International initial data.');
    }
}
