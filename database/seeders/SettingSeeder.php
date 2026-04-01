<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->insert([
            [
                'id' => 1,
                'key' => 'app_developer',
                'value' => 'Laravel',
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'id' => 2,
                'key' => 'site_name',
                'value' => 'Laravel',
                'created_at' => null,
                'updated_at' => Carbon::parse('2025-07-26 10:58:11'),
            ],
            [
                'id' => 3,
                'key' => 'site_description',
                'value' => 'Laravel',
                'created_at' => null,
                'updated_at' => Carbon::parse('2025-07-19 02:40:47'),
            ],
            [
                'id' => 4,
                'key' => 'site_logo',
                'value' => '/images/logo.png',
                'created_at' => null,
                'updated_at' => Carbon::parse('2025-09-20 03:47:12'),
            ],
            [
                'id' => 5,
                'key' => 'site_background',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
        ]);
    }
}