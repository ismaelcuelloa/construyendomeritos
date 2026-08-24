<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

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
