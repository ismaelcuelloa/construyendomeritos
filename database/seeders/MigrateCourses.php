<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Services\CourseService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class MigrateCourses extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('data/courses.json');

        if (! File::exists($path)) {
            $this->command->error("❌ No se encontró el archivo: {$path}");

            return;
        }

        $file = File::get($path);
        $courses = json_decode($file, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('❌ Error al leer el JSON: '.json_last_error_msg());

            return;
        }

        $categories = $this->getCategoriesByCode();
        foreach ($courses as $course) {
            try {
                $course['category_id'] = $categories[(string) $course['category']] ?? null;
                $course['published'] = true;
                unset($course['category']);
                app(CourseService::class)->create($course);
            } catch (\Exception $e) {
                $this->command->error('❌ Error creando curso: '.$course['code']);
                Log::error($e->getMessage());
            }
        }

        $this->command->info('✅ Cursos importados correctamente');
    }

    private function getCategoriesByCode(): array
    {
        $array = [];
        $categories = Category::all();
        foreach ($categories as $category) {
            $array[(string) $category->code] = $category->id;
        }
        unset($categories);

        return $array;
    }
}
