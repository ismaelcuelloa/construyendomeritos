<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Module;
use App\Services\ExamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class SimulacroController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $modules = Module::query()
            ->whereHas('exam', function ($q) {
                $q->where('active', true);
            })
            ->with(['exam' => function ($q) {
                $q->where('active', true)->withCount('questions');
            }, 'course.metadata'])
            ->get()
            ->map(function ($module) use ($userId) {
                if ($module->exam) {
                    $module->exam->remaining_attempts = $module->exam->remainingAttempts($userId ?? 0);
                    if ($userId) {
                        $module->exam->user_passed = $module->exam->attempts()
                            ->where('user_id', $userId)
                            ->where('passed', true)
                            ->exists();
                    } else {
                        $module->exam->user_passed = false;
                    }
                }
                return $module;
            })
            ->groupBy('course_id')
            ->map(function ($modules) {
                $course = $modules->first()->course;
                return [
                    'course' => $course,
                    'modules' => $modules,
                ];
            })
            ->values();

        return Inertia::render('Simulacros/Index', [
            'courses' => $modules,
        ]);
    }

    public function show(string $examId)
    {
        $exam = Exam::with(['questions' => function ($q) {
            $q->orderBy('order_no');
        }, 'module.course'])
            ->where('active', true)
            ->findOrFail($examId);

        $userId = auth()->id();
        $remainingAttempts = $exam->remainingAttempts($userId);
        // Solo intentos finalizados aparecen en el historial
        $previousAttempts = $exam->attempts()
            ->where('user_id', $userId)
            ->whereNotNull('finished_at')
            ->with('answers')
            ->orderBy('created_at', 'desc')
            ->get();

        $userPassed = $exam->attempts()
            ->where('user_id', $userId)
            ->where('passed', true)
            ->exists();

        // Intentar recuperar un intento en progreso para continuar
        $inProgress = $exam->attempts()
            ->where('user_id', $userId)
            ->whereNull('finished_at')
            ->latest('started_at')
            ->first();

        $savedAnswers = [];
        $inProgressData = null;
        if ($inProgress) {
            $inProgressData = [
                'id' => $inProgress->id,
                'started_at' => $inProgress->started_at?->toIso8601String(),
                'time_limit' => $exam->time_limit,
                'last_question_index' => $inProgress->last_question_index ?? 0,
            ];
            $savedAnswers = $inProgress->answers()
                ->pluck('selected_answer', 'question_id')
                ->toArray();
        }

        // Ocultar correct_answer en el frontend
        $questions = $exam->questions->map(function ($q) {
            return [
                'id' => $q->id,
                'question_text' => $q->question_text,
                'options' => $q->options,
                'points' => $q->points,
            ];
        });

        return Inertia::render('Simulacros/Exam', [
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description,
                'time_limit' => $exam->time_limit,
                'passing_score' => $exam->passing_score,
                'module' => [
                    'id' => $exam->module->id,
                    'title' => $exam->module->title,
                    'course' => [
                        'id' => $exam->module->course->id,
                        'title' => $exam->module->course->title,
                        'slug' => $exam->module->course->slug,
                    ],
                ],
            ],
            'questions' => $questions,
            'remainingAttempts' => $remainingAttempts,
            'previousAttempts' => $previousAttempts,
            'inProgress' => $inProgressData,
            'savedAnswers' => $savedAnswers,
            'userPassed' => $userPassed,
        ]);
    }

    public function start(Request $request, ExamService $service): JsonResponse
    {
        $request->validate(['exam_id' => 'required|exists:exams,id']);

        $exam = Exam::where('active', true)->findOrFail($request->exam_id);

        try {
            $attempt = $service->startAttempt($exam, auth()->id());

            $savedAnswers = $attempt->answers()
                ->pluck('selected_answer', 'question_id')
                ->toArray();

            return response()->json([
                'attempt' => $attempt,
                'savedAnswers' => $savedAnswers,
                'message' => 'Simulacro iniciado',
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function saveProgress(Request $request, ExamService $service): JsonResponse
    {
        $request->validate([
            'attempt_id' => 'required|exists:exam_attempts,id',
            'answers' => 'sometimes|array',
            'answers.*.question_id' => 'required|exists:exam_questions,id',
            'answers.*.selected_answer' => 'nullable|string|max:1',
        ]);

        $attempt = ExamAttempt::where('user_id', auth()->id())
            ->whereNull('finished_at')
            ->findOrFail($request->attempt_id);

        try {
            $service->saveProgress($attempt, $request->input('answers', []), $request->input('current_question'));

            return response()->json(['message' => 'Progreso guardado']);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function submit(Request $request, ExamService $service): JsonResponse
    {
        $request->validate([
            'attempt_id' => 'required|exists:exam_attempts,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:exam_questions,id',
            'answers.*.selected_answer' => 'nullable|string|max:1',
        ]);

        $attempt = ExamAttempt::where('user_id', auth()->id())
            ->whereNull('finished_at')
            ->findOrFail($request->attempt_id);

        try {
            $attempt = $service->submitAttempt($attempt, $request->answers);

            $percentage = $attempt->total_points > 0
                ? round(($attempt->score / $attempt->total_points) * 100)
                : 0;

            return response()->json([
                'attempt' => $attempt->load('answers.question'),
                'score' => $attempt->score,
                'total_points' => $attempt->total_points,
                'percentage' => $percentage,
                'passed' => $attempt->passed,
                'message' => $attempt->passed ? '¡Felicitaciones! Aprobaste el simulacro.' : 'No alcanzaste la nota mínima. Sigue estudiando.',
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function results(string $attemptId)
    {
        $attempt = ExamAttempt::where('user_id', auth()->id())
            ->with(['exam.module.course', 'exam.module.files', 'answers.question'])
            ->findOrFail($attemptId);

        $exam = $attempt->exam;
        $module = $exam->module;
        $firstFileId = $module->files->first()?->id;
        $remainingAttempts = $exam->remainingAttempts(auth()->id());
        $userPassed = $exam->attempts()
            ->where('user_id', auth()->id())
            ->where('passed', true)
            ->exists();

        return Inertia::render('Simulacros/Results', [
            'attempt' => $attempt->load('exam.module.course', 'answers.question'),
            'firstFileId' => $firstFileId,
            'remainingAttempts' => $remainingAttempts,
            'userPassed' => $userPassed,
        ]);
    }
}
