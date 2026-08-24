<?php

namespace Database\Seeders;

use App\Services\CategoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MigrateCategories extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/categories.json');

        if (! File::exists($path)) {
            $this->command->error("❌ No se encontró el archivo: {$path}");

            return;
        }

        $file = File::get($path);
        $categories = json_decode($file, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('❌ Error al leer el JSON: '.json_last_error_msg());

            return;
        }

        foreach ($categories as $category) {
            try {
                $category['published'] = true;
                app(CategoryService::class)->create($category);
            } catch (\Exception $e) {

            }
        }

        $this->command->info('✅ Categorias importadas correctamente');

    }
}
