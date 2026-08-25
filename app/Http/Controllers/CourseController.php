<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\ModuleFile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Category::query()
            ->with(['image'])
            ->withCount(['courses', 'subcategories'])
            ->published()
            ->visible();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                  ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        $categories = $query->orderBy('title')->get();

        return Inertia::render('Courses/Index', [
            'categories' => $categories,
            'seo' => [
                'title' => 'Categorías - Material de Estudio Procuraduría 2026 | Construyendo Méritos con Excelencia',
                'description' => 'Explora nuestras categorías de material de estudio para la Procuraduría General de la Nación 2026. Simulacros y guías de Construyendo Méritos con Excelencia.',
                'keywords' => 'Procuraduría 2026, categorías material estudio, simulacros Procuraduría, Construyendo Méritos con Excelencia',
            ],
        ]);
    }

    public function show(Request $request, $id)
    {

        $query = Course::query()
            ->with(['metadata', 'modules.files.file', 'modules.exam', 'category'])
            ->withCount(['subscriptions'])
            ->visible()
            ->slug($id);
        $queryAvailable = Course::query()->with(['modules'])->visible()->inRandomOrder()->take(3);

        if (auth()->check()) {
            $query->withUserSubscription(true)
                  ->withUserPaidOrders();
            $queryAvailable->subscripted(auth()->user()->id, false);
        }

        $course = $query->firstOrFail();

        // Obtener categorías recomendadas (puedes ajustar la lógica de selección)
        $categories = \App\Models\Category::query()
            ->with(['courses', 'image'])
            ->where('published', true)
            ->where('active', true)
            ->orderBy('title')
            ->take(6)
            ->get();

        // SEO data
        $seoImage = $course->metadata && $course->metadata->banner
            ? asset($course->metadata->banner)
            : asset('favicon.svg');

        return Inertia::render('Courses/Show', [
            'course' => $course,
            'categories' => $categories,
            'seo' => [
                'title' => $course->title.' - Construyendo Méritos con Excelencia',
                'description' => $course->description ?? 'Material de estudio Procuraduría General de la Nación 2026 - Construyendo Méritos con Excelencia',
                'image' => $seoImage,
                'url' => $request->url(),
                'type' => 'article',
                'keywords' => ($course->category ? $course->category->title.', ' : '').'Procuraduría 2026, material estudio, simulacros, Construyendo Méritos',
            ],
        ]);
    }

    public function file(Request $request, string $id)
    {
        try {
            $file = ModuleFile::query()->with(['file', 'module'])->findOrFail($id);

            // Cargar el curso incluyendo la relación file dentro de modules.files
            $course = Course::query()->with(['modules.files.file', 'modules.exam', 'category'])->visible()->findOrFail($file->module->course_id);

            // Construir la ruta completa del archivo usando el atributo full_name (append en el modelo)
            $filePath = public_path($file->file->getFullName());

            \Log::info('Intentando cargar archivo:', [
                'module_file_id' => $id,
                'file_id' => $file->file_id,
                'file_path' => $filePath,
                'file_exists' => file_exists($filePath),
            ]);

            // Verificar que el archivo exists
            if (! file_exists($filePath)) {
                \Log::error('Archivo no encontrado:', ['path' => $filePath]);

                return redirect()->back()->with('error', 'El archivo no se encuentra disponible en: '.$filePath);
            }

            $pdf = base64_encode(file_get_contents($filePath));

            return Inertia::render('Courses/Files/Show', [
                'moduleFile' => $file,
                'pdf' => $pdf,
                'course' => $course,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al cargar archivo:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Error al cargar el archivo: '.$e->getMessage());
        }
    }

    public function myCourses(Request $request)
    {
        $per_page = $request->input('per_page', 15);
        $user = auth()->user();
        $query = Course::query()
            ->with(['metadata', 'modules', 'category'])
            ->withCount(['modules', 'subscriptions'])
            ->published()
            ->withUserSubscription(true)
            ->withUserPaidOrders()
            ->where(function ($q) use ($user) {
                // Courses from active subscriptions
                $q->whereHas('subscriptions', function ($query) use ($user) {
                    $query->byUser($user->id, true);
                })
                // OR courses from paid orders or valid demos
                ->orWhereHas('orderItems', function ($query) use ($user) {
                    $query->whereHas('order', function ($orderQuery) use ($user) {
                        $orderQuery->where('user_id', $user->id)
                            ->where(function ($statusQuery) {
                                // Incluir órdenes pagadas
                                $statusQuery->where('status_id', OrderStatus::PAID->value)
                                    // O demos que no hayan expirado
                                    ->orWhere(function ($demoQuery) {
                                        $demoQuery->where('status_id', OrderStatus::DEMO->value)
                                            ->where('demo_expires_at', '>', now());
                                    });
                            });
                    });
                });
            })
            ->orderBy('title');

        if ($request->has('search')) {
            $this->search($query, $request->input('search'), ['title', 'description']);
        }

        $courses = $query->paginate($per_page);

        return Inertia::render('Courses/MyCourses', [
            'courses' => $courses->appends($request->query()),
        ]);
    }
}
