<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Category;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $cabello = Category::where('slug', 'cabello')->first();
        $unas = Category::where('slug', 'unas')->first();

        // Cabello Services
        if ($cabello) {
            $cabelloServices = [
                ['name' => 'Planchado', 'price_display' => '$20k', 'price' => 20000, 'duration' => '30 min'],
                ['name' => 'Cepillado y planchado', 'price_display' => '$35k', 'price' => 35000, 'duration' => '1 hora'],
                ['name' => 'Aplicacion de tinte', 'price_display' => 'Cotizar', 'price' => null, 'duration' => '2 horas'],
                ['name' => 'Aminoacido', 'price_display' => 'Depende el largo', 'price' => null, 'duration' => '3 horas'],
                ['name' => 'Keratina', 'price_display' => 'Depende el largo', 'price' => null, 'duration' => '3 horas'],
                ['name' => 'Peinados de niñas', 'price_display' => 'Desde 15k', 'price' => 15000, 'duration' => '1 hora'],
            ];

            foreach ($cabelloServices as $service) {
                Service::updateOrCreate(['name' => $service['name'], 'category_id' => $cabello->id], $service);
            }
        }

        // Uñas Services
        if ($unas) {
            $unasServices = [
                ['name' => 'Semipermanente', 'price_display' => '$25.000', 'price' => 25000, 'duration' => '1 hora'],
                ['name' => 'Efecto Diping', 'price_display' => '$25.000', 'price' => 25000, 'duration' => '1.5 horas'],
                ['name' => 'Builder Gel', 'price_display' => '$25.000', 'price' => 25000, 'duration' => '1.5 horas'],
                ['name' => 'Soft Gel o Jelly Tips', 'price_display' => '$40.000', 'price' => 40000, 'duration' => '2 horas'],
                ['name' => 'Poligel', 'price_display' => '$45.000', 'price' => 45000, 'duration' => '2 horas'],
            ];

            foreach ($unasServices as $service) {
                Service::updateOrCreate(['name' => $service['name'], 'category_id' => $unas->id], $service);
            }
        }
    }
}
