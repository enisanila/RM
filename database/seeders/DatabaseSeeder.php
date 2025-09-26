<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // penting untuk akses model

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        // contoh: bikin 10 user dummy
        User::factory()->count(10)->create();

        // kalau mau tambah seeder lain tinggal dipanggil di sini
        // $this->call(AnotherSeeder::class);
    }
}
