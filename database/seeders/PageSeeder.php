<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageSeeder extends Seeder
{
    public function run()
    {
        DB::table('pages')->insert([
            [
                'id' => 1,
                'title' => 'Tentang',
                'title_en' => 'About',
                'body' => '<h1>Selamat datang di website kami</h1><p>Ini adalah halaman beranda.</p>',
                'body_en' => '<h1>Welcome to our website</h1><p>This is the homepage.</p>',
                'active' => 1,
                'slug' => 'about',
                'user_id' => 1,
                'created_at' => now(),
            ],
            [
                'id' => 2,
                'title' => 'Syarat',
                'title_en' => 'Terms',
                'body' => '<h1>Tentang Kami</h1><p>Informasi mengenai perusahaan atau organisasi.</p>',
                'body_en' => '<h1>About Us</h1><p>Information about the company or organization.</p>',
                'active' => 1,
                'slug' => 'terms',
                'user_id' => 1,
                'created_at' => now(),
            ],
            [
                'id' => 3,
                'title' => 'Privasi',
                'title_en' => 'Privacy',
                'body' => '<h1>Kontak Kami</h1><p>Hubungi kami melalui form atau media sosial.</p>',
                'body_en' => '<h1>Contact Us</h1><p>Contact us via form or social media.</p>',
                'active' => 1,
                'slug' => 'privacy',
                'user_id' => 1,
                'created_at' => now(),
            ],
        ]);
    }
}
