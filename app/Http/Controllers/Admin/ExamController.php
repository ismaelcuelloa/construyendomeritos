<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExamController extends Controller
{
    public function show(string $moduleId): JsonResponse
    {
        $exam = Exam::where('module_id', $moduleId)
            ->with(['questions' => function ($q) {
                $q->orderBy('order_no');
            }])
            ->first();

        return response()->json(['exam' => $exam]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'active' => 'boolean',
        ]);

        $existing = Exam::where('module_id', $request->module_id)->first();
        if ($existing) {
            return response()->json(['message' => 'Este módulo ya tiene un simulacro'], Response::HTTP_CONFLICT);
        }

        $exam = Exam::create($request->all());

        return response()->json(['exam' => $exam->load('questions')], Response::HTTP_CREATED);
    }

    public function update(Request $request, string $moduleId, string $id): JsonResponse
    {
        $exam = Exam::find($id);
        if (! $exam) {
            return response()->json(['message' => 'Simulacro no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'passing_score' => 'sometimes|integer|min:0|max:100',
            'active' => 'boolean',
        ]);

        $exam->update($request->all());

        return response()->json(['exam' => $exam->fresh('questions')]);
    }

    public function destroy(string $moduleId, string $id): JsonResponse
    {
        $exam = Exam::find($id);
        if ($exam) {
            $exam->delete();
        }

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    // Preguntas
    public function storeQuestion(Request $request): JsonResponse
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'question_text' => 'required|string',
            'options' => 'required|array',
            'options.a' => 'required|string',
            'options.b' => 'required|string',
            'correct_answer' => 'required|in:a,b,c,d',
            'justification' => 'nullable|string',
            'points' => 'numeric|min:0',
        ]);

        $maxOrder = ExamQuestion::where('exam_id', $request->exam_id)->max('order_no') ?? -1;

        $question = ExamQuestion::create([
            'exam_id' => $request->exam_id,
            'question_text' => $request->question_text,
            'options' => $this->cleanOptions($request->options),
            'correct_answer' => $request->correct_answer,
            'justification' => $request->justification,
            'points' => 0,
            'order_no' => $maxOrder + 1,
        ]);

        $this->recalculatePoints($request->exam_id);

        return response()->json([
            'question' => $question,
            'questions' => $this->getExamQuestions($request->exam_id),
        ], Response::HTTP_CREATED);
    }

    public function updateQuestion(Request $request, string $id): JsonResponse
    {
        $question = ExamQuestion::findOrFail($id);

        $request->validate([
            'question_text' => 'sometimes|string',
            'options' => 'sometimes|array',
            'options.a' => 'required_with:options|string',
            'options.b' => 'required_with:options|string',
            'correct_answer' => 'sometimes|in:a,b,c,d',
            'justification' => 'nullable|string',
            'points' => 'sometimes|numeric|min:0',
        ]);

        $data = $request->all();
        if (isset($data['options']) && is_array($data['options'])) {
            $data['options'] = $this->cleanOptions($data['options']);
        }
        // Los puntos siempre se recalculan automáticamente
        unset($data['points']);

        $question->update($data);

        $this->recalculatePoints($question->exam_id);

        return response()->json([
            'question' => $question->fresh(),
            'questions' => $this->getExamQuestions($question->exam_id),
        ]);
    }

    private function cleanOptions(array $options): array
    {
        foreach ($options as $key => $value) {
            if (trim((string) $value) === '') {
                unset($options[$key]);
            }
        }
        return $options;
    }

    public function destroyQuestion(string $id): JsonResponse
    {
        $question = ExamQuestion::findOrFail($id);
        $examId = $question->exam_id;
        $question->delete();

        $this->recalculatePoints($examId);

        return response()->json([
            'questions' => $this->getExamQuestions($examId),
        ]);
    }

    public function clearQuestions(Request $request): JsonResponse
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
        ]);

        ExamQuestion::where('exam_id', $request->exam_id)->delete();

        return response()->json(['message' => 'Preguntas eliminadas']);
    }

    public function reorderQuestions(Request $request): JsonResponse
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:exam_questions,id',
            'orders.*.order_no' => 'required|integer|min:0',
        ]);

        foreach ($request->input('orders') as $item) {
            ExamQuestion::where('id', $item['id'])->update(['order_no' => $item['order_no']]);
        }

        return response()->json(['message' => 'Orden actualizado']);
    }

    public function modulesList(Request $request): JsonResponse
    {
        $query = Module::query()
            ->whereDoesntHave('exam')
            ->with('course');

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->integer('course_id'));
        }

        $modules = $query->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'title' => $m->title,
                    'course_id' => $m->course_id,
                    'course_title' => $m->course->title ?? 'Sin curso',
                ];
            });

        return response()->json(['modules' => $modules]);
    }

    public function copy(Request $request): JsonResponse
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'module_id' => 'required|exists:modules,id',
        ]);

        $existing = Exam::where('module_id', $request->module_id)->first();
        if ($existing) {
            return response()->json(['message' => 'El módulo destino ya tiene un simulacro'], Response::HTTP_CONFLICT);
        }

        $original = Exam::with('questions')->findOrFail($request->exam_id);
        $copy = $original->replicate();
        $copy->module_id = $request->module_id;
        $copy->save();

        foreach ($original->questions as $question) {
            $qCopy = $question->replicate();
            $qCopy->exam_id = $copy->id;
            $qCopy->save();
        }

        return response()->json(['exam' => $copy->load('questions')], Response::HTTP_CREATED);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plantilla Preguntas');

        $headers = ['Pregunta', 'Opción A', 'Opción B', 'Opción C', 'Opción D', 'Respuesta Correcta (a/b/c/d)', 'Justificación', 'Puntos'];
        $sheet->fromArray([$headers], null, 'A1');

        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF07900');
        $sheet->getStyle('A1:H1')->getFont()->getColor()->setARGB('FFFFFFFF');

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->setCellValue('A2', '¿Cuál es la capital de Colombia?');
        $sheet->setCellValue('B2', 'Medellín');
        $sheet->setCellValue('C2', 'Bogotá');
        $sheet->setCellValue('D2', 'Cali');
        $sheet->setCellValue('E2', 'Barranquilla');
        $sheet->setCellValue('F2', 'b');
        $sheet->setCellValue('G2', 'Bogotá es la capital de Colombia desde 1538.');
        $sheet->setCellValue('H2', 20);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'plantilla-preguntas.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function importQuestions(Request $request): JsonResponse
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $imported = 0;
        $maxOrder = ExamQuestion::where('exam_id', $request->exam_id)->max('order_no') ?? -1;

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // skip header
            if (empty($row[0])) continue; // skip empty

            $questionText = trim($row[0] ?? '');
            if (empty($questionText)) continue;

            ExamQuestion::create([
                'exam_id' => $request->exam_id,
                'question_text' => $questionText,
                'options' => $this->cleanOptions([
                    'a' => trim($row[1] ?? ''),
                    'b' => trim($row[2] ?? ''),
                    'c' => trim($row[3] ?? ''),
                    'd' => trim($row[4] ?? ''),
                ]),
                'correct_answer' => strtolower(trim($row[5] ?? 'a')),
                'justification' => trim($row[6] ?? ''),
                'points' => 0,
                'order_no' => $maxOrder + $index,
            ]);
            $imported++;
        }

        $this->recalculatePoints($request->exam_id);

        return response()->json([
            'message' => "$imported preguntas importadas",
            'questions' => $this->getExamQuestions($request->exam_id),
        ]);
    }

    /**
     * Distribuir 100 puntos equitativamente entre todas las preguntas del examen.
     */
    private function recalculatePoints(int $examId): void
    {
        $questions = ExamQuestion::where('exam_id', $examId)->orderBy('order_no')->get();
        $count = $questions->count();

        if ($count === 0) {
            return;
        }

        $base = intdiv(100, $count);
        $remainder = 100 % $count;

        foreach ($questions as $index => $question) {
            $points = $base + ($index < $remainder ? 1 : 0);
            $question->update(['points' => $points]);
        }
    }

    private function getExamQuestions(int $examId)
    {
        return ExamQuestion::where('exam_id', $examId)->orderBy('order_no')->get();
    }
}
