<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCode;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/Courses/Index');
    }

    public function store(Request $request)
    {
        // Solo super_user puede crear cursos
        if (! auth()->user()->hasRole('super_user')) {
            return response()->json([
                'message' => 'Solo los super usuarios pueden crear cursos',
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $course = (new CourseService)->create($request->all());

            return response()->json(['course' => $course], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, string $id)
    {
        // Solo super_user puede editar cursos
        if (! auth()->user()->hasRole('super_user')) {
            return response()->json([
                'message' => 'Solo los super usuarios pueden editar cursos',
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $course = (new CourseService)->update($id, $request->all());

            return response()->json(['course' => $course], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show(Request $request, string $id)
    {
        $course = Course::with(['modules.files.file', 'metadata', 'category', 'subcategory', 'codes'])->find($id);

        return Inertia::render('Admin/Courses/Show', ['course' => $course]);
    }

    public function codesTemplate(Request $request, string $id)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Códigos de Convocatoria');

        $sheet->fromArray([['Código']], null, 'A1');

        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF133a54');

        $sheet->getColumnDimension('A')->setAutoSize(true);

        $sheet->setCellValue('A2', '01-2026');
        $sheet->setCellValue('A3', '02-2026');
        $sheet->setCellValue('A4', '108-2026');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'plantilla-codigos-convocatoria.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function copy(Request $request, string $id)
    {
        if (! auth()->user()->hasRole('super_user')) {
            return response()->json([
                'message' => 'Solo los super usuarios pueden copiar cursos',
            ], Response::HTTP_FORBIDDEN);
        }
        try {
            $course = Course::with([
                'metadata',
                'codes',
                'modules.files.file',
                'modules.exam.questions',
            ])->findOrFail($id);

            $newTitle = $course->title.' (copia)';
            $baseSlug = Str::slug($newTitle);
            $slug = $baseSlug;
            $counter = 1;
            while (Course::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }

            $copy = $course->replicate();
            $copy->title = $newTitle;
            $copy->slug = $slug;
            $copy->published = false;
            $copy->code = null;
            $copy->save();

            // Copiar códigos
            foreach ($course->codes as $code) {
                \App\Models\CourseCode::create([
                    'course_id' => $copy->id,
                    'code' => $code->code,
                ]);
            }

            // Copiar metadata
            if ($course->metadata) {
                $metadataCopy = $course->metadata->replicate();
                $metadataCopy->course_id = $copy->id;
                $metadataCopy->save();
            }

            // Copiar módulos con archivos y exámenes
            foreach ($course->modules as $module) {
                $moduleCopy = $module->replicate();
                $moduleCopy->course_id = $copy->id;
                $moduleCopy->save();

                foreach ($module->files as $file) {
                    $fileCopy = $file->replicate();
                    $fileCopy->module_id = $moduleCopy->id;
                    $fileCopy->save();
                }

                if ($module->exam) {
                    $examCopy = $module->exam->replicate();
                    $examCopy->module_id = $moduleCopy->id;
                    $examCopy->save();

                    foreach ($module->exam->questions as $question) {
                        $questionCopy = $question->replicate();
                        $questionCopy->exam_id = $examCopy->id;
                        $questionCopy->save();
                    }
                }
            }

            return response()->json(['course' => $copy], Response::HTTP_CREATED);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Curso no encontrado'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function importCodes(Request $request, string $id)
    {
        if (! auth()->user()->hasRole('super_user')) {
            return response()->json([
                'message' => 'Solo los super usuarios pueden importar códigos',
            ], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $course = Course::findOrFail($id);
            $existing = $course->codes()->pluck('code')->flip();
            $imported = 0;
            $skipped = 0;

            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // skip header

                $code = trim((string) ($row[0] ?? ''));
                if ($code === '') continue;

                if (isset($existing[$code])) {
                    $skipped++;
                    continue;
                }

                CourseCode::create([
                    'course_id' => $course->id,
                    'code' => $code,
                ]);
                $existing[$code] = true;
                $imported++;
            }

            return response()->json([
                'message' => "$imported códigos importados" . ($skipped ? ", $skipped duplicados omitidos" : ''),
                'codes' => $course->codes()->orderBy('code')->get()->pluck('code'),
            ], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Curso no encontrado'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al importar el archivo: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function metadata(Request $request, string $id)
    {
        // Solo super_user puede editar metadata de cursos
        if (! auth()->user()->hasRole('super_user')) {
            return response()->json([
                'message' => 'Solo los super usuarios pueden editar cursos',
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $metadata = (new CourseService)->saveMetadata($id, $request->all());

            return response()->json(['metadata' => $metadata], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(string $id)
    {
        // Solo super_user puede eliminar cursos
        if (! auth()->user()->hasRole('super_user')) {
            return response()->json([
                'message' => 'Solo los super usuarios pueden eliminar cursos',
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $course = Course::findOrFail($id);

            // Eliminar metadata relacionada
            $course->metadata()->delete();

            // Eliminar módulos relacionados
            $course->modules()->delete();

            // Eliminar suscripciones relacionadas
            $course->subscriptions()->delete();

            // Eliminar el curso
            $course->delete();

            return response()->json(['message' => 'Curso eliminado con éxito'], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Curso no encontrado'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function list(Request $request)
    {
        $query = Course::query()->withCount(['modules', 'subscriptions'])->with(['category']);
        $perPage = $this->getPerPage($request);
        $user_id = $request->input('user_id');
        $exclude_user_id = $request->input('exclude_user_id');
        $category_id = $request->input('category_id');

        if ($category_id != null) {
            $query->byCategory($category_id);
        }

        if ($user_id != null) {
            $query->whereHas('subscriptions', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            });
        }

        if ($exclude_user_id != null) {
            $query->whereDoesntHave('subscriptions', function ($query) use ($exclude_user_id) {
                $query->byUser($exclude_user_id, true);
            });
        }

        $this->search($query, $request->input('search'), ['title', 'description']);

        $data = $query->paginate($perPage);

        return response()->json($data);
    }
}
