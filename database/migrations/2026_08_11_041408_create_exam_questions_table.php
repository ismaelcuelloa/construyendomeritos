<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->text('question_text');
            $table->json('options')->comment('Opciones en formato JSON: {"a":"...","b":"...","c":"...","d":"..."}');
            $table->string('correct_answer', 1)->comment('Letra de la respuesta correcta: a,b,c,d');
            $table->text('justification')->nullable();
            $table->integer('points')->default(1);
            $table->integer('order_no')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
