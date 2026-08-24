<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MigrateDatabase extends Seeder
{
    public function run(): void
    {
        $this->call([
            MigrateCategories::class,
            MigrateCourses::class,
            MigrateUsers::class,
        ]);
    }
}
