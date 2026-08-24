<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar respuestas duplicadas previas si las hubiera (compatible MySQL/SQLite)
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DELETE FROM exam_user_answers WHERE id NOT IN (SELECT MIN(id) FROM exam_user_answers GROUP BY attempt_id, question_id)');
        } else {
            DB::statement('DELETE a FROM exam_user_answers a INNER JOIN exam_user_answers b ON a.attempt_id = b.attempt_id AND a.question_id = b.question_id AND a.id > b.id');
        }

        Schema::table('exam_user_answers', function (Blueprint $table) {
            $table->unique(['attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::table('exam_user_answers', function (Blueprint $table) {
            $table->dropUnique(['attempt_id', 'question_id']);
        });
    }
};
