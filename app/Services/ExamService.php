<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamUserAnswer;
use Illuminate\Support\Facades\DB;

class ExamService extends BaseService
{
    public function startAttempt(Exam $exam, int $userId): ExamAttempt
    {
        // Reutilizar un intento en progreso si existe
        $inProgress = $exam->attempts()
            ->where('user_id', $userId)
            ->whereNull('finished_at')
            ->latest('started_at')
            ->first();

        if ($inProgress) {
            return $inProgress;
        }

        if ($exam->max_attempts !== null) {
            $used = $exam->attempts()->where('user_id', $userId)->count();
            if ($used >= $exam->max_attempts) {
                $this->error('Has alcanzado el límite de intentos para este simulacro');
            }
        }

        return ExamAttempt::create([
            'user_id' => $userId,
            'exam_id' => $exam->id,
            'score' => 0,
            'total_points' => $exam->totalPoints(),
            'started_at' => now(),
        ]);
    }

    public function saveProgress(ExamAttempt $attempt, array $answers, ?int $currentQuestion = null): void
    {
        foreach ($answers as $answer) {
            $question = ExamQuestion::find($answer['question_id'] ?? null);

            if (! $question || $question->exam_id !== $attempt->exam_id) {
                continue;
            }

            ExamUserAnswer::updateOrCreate(
                [
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                ],
                [
                    'selected_answer' => $answer['selected_answer'] ?? null,
                ]
            );
        }

        if ($currentQuestion !== null) {
            $attempt->update(['last_question_index' => $currentQuestion]);
        }
    }

    public function submitAttempt(ExamAttempt $attempt, array $answers): ExamAttempt
    {
        $this->initTransactions();

        try {
            $score = 0;
            $totalPoints = $attempt->total_points;

            foreach ($answers as $answer) {
                $question = ExamQuestion::find($answer['question_id']);

                if (! $question || $question->exam_id !== $attempt->exam_id) {
                    continue;
                }

                $selectedAnswer = $answer['selected_answer'] ?? null;
                $isCorrect = $selectedAnswer === $question->correct_answer;

                if ($isCorrect) {
                    $score += $question->points;
                }

                ExamUserAnswer::updateOrCreate(
                    [
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                    ],
                    [
                        'selected_answer' => $selectedAnswer,
                        'is_correct' => $isCorrect,
                    ]
                );
            }

            $percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100) : 0;
            $passed = $percentage >= $attempt->exam->passing_score;

            $attempt->update([
                'score' => $score,
                'passed' => $passed,
                'finished_at' => now(),
            ]);

            $this->commitTransactions();

            return $attempt->fresh(['answers', 'answers.question', 'exam']);
        } catch (\Exception $e) {
            $this->rollbackTransactions();
            throw $e;
        }
    }
}
