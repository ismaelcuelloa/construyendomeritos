<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_metadata', function (Blueprint $table) {
            $table->text('custom_filter_value')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('course_metadata', function (Blueprint $table) {
            $table->string('custom_filter_value')->nullable()->change();
        });
    }
};
