<?php

use App\Models\Course;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table((new Course)->getTable(), function (Blueprint $table) {
            $table->string('grado')->nullable()->after('code');
        });
    }

    public function down(): void
    {
        Schema::table((new Course)->getTable(), function (Blueprint $table) {
            $table->dropColumn('grado');
        });
    }
};
