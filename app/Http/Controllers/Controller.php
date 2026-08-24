<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

abstract class Controller
{
    public function getPerPage($data)
    {
        return $data['per_page'] ?? 50;
    }

    public function getPage($data)
    {
        return $data['page'] ?? 1;
    }

    protected function handleException(\Exception $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Log el error completo para debugging
        \Log::error('Exception in controller:', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        return response()->json([
            'message' => $exception->getMessage(),
            'error' => config('app.debug') ? [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ] : null,
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    protected function search(&$query, ?string $value, array $columns = []): void
    {
        if (! empty($value)) {
            $query->where(function ($query) use ($value, $columns) {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'like', "%{$value}%");
                }
            });
        }
    }
}
