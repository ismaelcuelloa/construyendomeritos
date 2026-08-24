<?php

namespace App\Http\Controllers;

use App\Services\ModuleService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ModuleController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $module = (new ModuleService)->create($request->all());

            return response()->json(['module' => $module], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $module = (new ModuleService)->update($id, $request->all());

            return response()->json(['module' => $module], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            (new ModuleService)->delete($id);

            return response()->json([], Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function copy(Request $request)
    {

        try {
            $module = (new ModuleService)->copy($request->all());

            return response()->json(['module' => $module], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }

    }
}
