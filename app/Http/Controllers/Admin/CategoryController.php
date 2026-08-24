<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/Categories/Index');
    }

    public function store(Request $request)
    {
        try {
            $category = (new CategoryService)->create($request->all());

            return response()->json(['category' => $category], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $data = $request->all();

            if (isset($data['published'])) {
                $data['published'] = filter_var($data['published'], FILTER_VALIDATE_BOOLEAN);
            }

            if (isset($data['active'])) {
                $data['active'] = filter_var($data['active'], FILTER_VALIDATE_BOOLEAN);
            }

            if (isset($data['enable_custom_filter'])) {
                $data['enable_custom_filter'] = filter_var($data['enable_custom_filter'], FILTER_VALIDATE_BOOLEAN);
            }

            if (isset($data['enable_subcategories'])) {
                $data['enable_subcategories'] = filter_var($data['enable_subcategories'], FILTER_VALIDATE_BOOLEAN);
            }

            if (isset($data['custom_filter_options']) && is_string($data['custom_filter_options'])) {
                $data['custom_filter_options'] = json_decode($data['custom_filter_options'], true);
            }

            $category = (new CategoryService)->update($id, $data);

            // Cargar la relación de imagen para devolverla en el response
            $category = Category::with('image')->findOrFail($id);

            // Convertir a array y asegurar que la imagen se incluye
            $categoryArray = $category->toArray();

            return response()->json(['category' => $categoryArray], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show(Request $request, string $id)
    {
        $category = Category::with(['courses', 'image'])->find($id);

        if ($request->wantsJson()) {
            return response()->json(['category' => $category]);
        }

        return Inertia::render('Admin/Categories/Show', ['category' => $category]);
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);

            foreach ($category->courses as $course) {
                $course->metadata()->delete();
                $course->subscriptions()->delete();
                $course->orderItems()->delete();
                $course->modules()->each(function ($module) {
                    $module->files()->delete();
                    $module->delete();
                });
                $course->delete();
            }

            $category->delete();

            return response()->json([
                'message' => 'Categoría eliminada exitosamente',
                'status' => 'success',
            ], Response::HTTP_OK);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Error de base de datos al eliminar la categoría',
                'error' => $e->getMessage(),
            ], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la categoría',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function list(Request $request): JsonResponse
    {
        $query = Category::query()->withCount(['courses', 'image']);
        $perPage = $this->getPerPage($request);

        $this->search($query, $request->input('search'), ['title', 'description']);

        $data = $query->paginate($perPage);

        return response()->json($data);
    }
}
