<?php

namespace App\Http\Controllers;

use App\Services\ModuleFileService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ModuleFileController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $module = (new ModuleFileService)->create($request->all());
            $module->load('file');

            return response()->json(['module_file' => $module], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $module = (new ModuleFileService)->update($id, $request->all());
            $module->load('file');

            return response()->json(['module_file' => $module], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            (new ModuleFileService)->delete($id);

            return response()->json([], Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
