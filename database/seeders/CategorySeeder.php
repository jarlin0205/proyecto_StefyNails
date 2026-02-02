<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Cabello',
                'slug' => 'cabello',
                'description' => 'Servicios de cortes, tintes y tratamientos capilares.',
            ],
            [
                'name' => 'Uñas',
                'slug' => 'unas',
                'description' => 'Manicure, pedicure, acrílicas y diseños.',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
