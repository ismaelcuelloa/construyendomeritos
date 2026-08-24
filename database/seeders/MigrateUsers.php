<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class MigrateUsers extends Seeder
{
    public function run(): void
    {

        $email = 'root@admin.com';
        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::factory()->create([
                'name' => 'root',
                'email' => $email,
                'password' => bcrypt('root2025@'),
            ]);
        }

        (new CreateRolesSeeder)->run();

        $user->assignRole(User::ROLE_SUPER_USER);

        $path = database_path('data/users.json');

        if (! File::exists($path)) {
            $this->command->error("❌ No se encontró el archivo: {$path}");

            return;
        }

        $json = File::get($path);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('❌ Error al leer el JSON: '.json_last_error_msg());

            return;
        }

        if (empty($data['rows']) || ! is_array($data['rows'])) {
            $this->command->error("❌ El archivo JSON no tiene la estructura esperada (falta 'rows').");

            return;
        }

        $courses = $this->getCursesByCode();

        foreach ($data['rows'] as $row) {
            $nameParts = explode(' ', $row['full_name'], 4);
            $name = '';
            $lastName = '';
            switch (count($nameParts)) {
                case 1:
                    $name = $nameParts[0];
                    break;
                case 2:
                    $name = $nameParts[0];
                    $lastName = $nameParts[1];
                    break;
                case 3:
                    $name = $nameParts[0];
                    $lastName = $nameParts[1].' '.$nameParts[2];
                    break;

                case 4:
                    $name = $nameParts[0].' '.$nameParts[1];
                    $lastName = $nameParts[2].' '.$nameParts[3];
                    break;
                default:
                    $name = $nameParts[0];
            }

            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $name,
                    'last_name' => $lastName,
                    'email' => $row['email'],
                    'password' => $row['password'],
                ]
            );

            if ($row['role'] == 'admin') {
                $user->assignRole(User::ROLE_ADMIN);
            } else {
                $user->assignRole(User::ROLE_USER);
            }

            if (isset($row['courses'])) {
                $courses_id = [];
                $codes = explode(',', $row['courses']);
                foreach ($codes as $code) {
                    if (isset($courses[$code])) {
                        $courses_id[] = $courses[$code];
                    }
                }

                $this->createSubscriptions($user->id, $courses_id);
            }
        }

        $this->command->info('✅ Usuarios importados correctamente desde users.json');
    }

    private function getCursesByCode(): array
    {
        $array = [];
        $courses = Course::all();
        foreach ($courses as $course) {
            $array[$course->code] = $course->id;
        }
        unset($courses);

        return $array;
    }

    private function createSubscriptions($user_id, $courses_id): void
    {
        if (count($courses_id) === 0) {
            return;
        }
        $subscriptions = Subscription::where('user_id', $user_id)->get()->pluck('course_id')->toArray();

        foreach ($courses_id as $course_id) {
            if (! in_array($course_id, $subscriptions)) {
                try {
                    app(SubscriptionService::class)->create([
                        'user_id' => $user_id,
                        'course_id' => $course_id,
                    ]);
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        }
    }
}
