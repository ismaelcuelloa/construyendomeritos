<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class SubcategoryController extends Controller
{
    public function list(Request $request, string $categoryId): JsonResponse
    {
        $query = Subcategory::query()->with(['image'])->where('category_id', $categoryId);
        $perPage = $this->getPerPage($request);

        $this->search($query, $request->input('search'), ['title', 'description']);

        $data = $query->orderBy('title')->paginate($perPage);

        return response()->json($data);
    }

    public function store(Request $request, string $categoryId): JsonResponse
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $data['published'] = filter_var($request->input('published', true), FILTER_VALIDATE_BOOLEAN);
            $data['active'] = filter_var($request->input('active', true), FILTER_VALIDATE_BOOLEAN);
            $data['parent_id'] = $request->input('parent_id') ?: null;
            $data['category_id'] = $categoryId;
            $data['slug'] = $baseSlug = Str::slug($data['title']);

            $counter = 0;
            while (Subcategory::where('slug', $data['slug'])->exists()) {
                $counter++;
                $data['slug'] = $baseSlug.'-'.$counter;
            }

            if ($request->hasFile('image')) {
                $file = [
                    'file' => $request->file('image'),
                    'path' => 'files/subcategories',
                    'type' => \App\Models\File::TYPE_IMAGE,
                ];
                $fileService = new \App\Services\FileService;
                $fileService->setTransactions(false);
                $imageFile = $fileService->create($file);
                $data['image_id'] = $imageFile->id;
            }

            $subcategory = Subcategory::create($data);
            $subcategory->load('image');

            return response()->json(['subcategory' => $subcategory], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, string $categoryId, string $subcategoryId): JsonResponse
    {
        try {
            $data = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
            ]);

            if ($request->has('published')) {
                $data['published'] = filter_var($request->input('published'), FILTER_VALIDATE_BOOLEAN);
            }

            if ($request->has('active')) {
                $data['active'] = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN);
            }

            if ($request->has('parent_id')) {
                $data['parent_id'] = $request->input('parent_id') ?: null;
            }

            if ($request->hasFile('image')) {
                $file = [
                    'file' => $request->file('image'),
                    'path' => 'files/subcategories',
                    'type' => \App\Models\File::TYPE_IMAGE,
                ];
                $fileService = new \App\Services\FileService;
                $fileService->setTransactions(false);
                $imageFile = $fileService->create($file);
                $data['image_id'] = $imageFile->id;
            }

            $subcategory = Subcategory::where('category_id', $categoryId)->findOrFail($subcategoryId);
            $subcategory->update($data);
            $subcategory->load('image');

            return response()->json(['subcategory' => $subcategory], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(string $categoryId, string $subcategoryId): JsonResponse
    {
        try {
            $subcategory = Subcategory::where('category_id', $categoryId)->findOrFail($subcategoryId);
            $subcategory->delete();

            return response()->json([
                'message' => 'Subcategoría eliminada exitosamente',
                'status' => 'success',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la subcategoría',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
