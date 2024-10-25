<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['type' => 'Soul'],
            ['type' => 'Ambient'],
            ['type' => 'Pop'],
            ['type' => 'Rap'],
            ['type' => 'Funk'],
            ['type' => 'Rock'],
            ['type' => 'Reggae / Dub'],
            ['type' => 'Techno'],
            ['type' => 'Electro'],
        ];

        DB::table('categories')->insert($categories);
    }
}
