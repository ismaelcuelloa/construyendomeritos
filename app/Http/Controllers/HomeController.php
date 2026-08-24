<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::query()
            ->with(['category', 'subcategory', 'metadata', 'codes'])
            ->where('published', true);

        if ($request->filled('codigo')) {
            $searchCode = '%' . $request->codigo . '%';
            $query->where(function ($q) use ($searchCode) {
                $q->where('code', 'like', $searchCode)
                  ->orWhereHas('codes', function ($sub) use ($searchCode) {
                      $sub->where('code', 'like', $searchCode);
                  });
            });
        }

        if ($request->filled('grado')) {
            $searchGrado = '%' . $request->grado . '%';
            $query->where(function ($q) use ($searchGrado) {
                $q->where('grado', 'like', $searchGrado)
                  ->orWhere('code', 'like', $searchGrado)
                  ->orWhereHas('codes', function ($sub) use ($searchGrado) {
                      $sub->where('code', 'like', $searchGrado);
                  });
            });
        }

        if ($request->filled('nivel')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('title', $request->nivel);
            });
        }

        if ($request->filled('educacion')) {
            $query->whereHas('metadata', function ($q) use ($request) {
                $q->where('custom_filter_value', 'like', '%' . $request->educacion . '%');
            });
        }

        if ($request->filled('cargo')) {
            $query->where('title', 'like', '%' . $request->cargo . '%');
        }

        if ($request->filled('ubicacion')) {
            $query->whereHas('subcategory', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->ubicacion . '%');
            });
        }

        if ($request->filled('salario_min')) {
            $query->where('price', '>=', $request->salario_min);
        }

        if ($request->filled('salario_max')) {
            $query->where('price', '<=', $request->salario_max);
        }

        $perPage = $request->input('per_page', 25);
        $courses = $query->paginate($perPage);

        $niveles = Category::query()->published()->pluck('title');

        return Inertia::render('Home/Index', [
            'courses' => $courses,
            'niveles' => $niveles,
            'filters' => $request->only([
                'codigo', 'grado', 'nivel', 'educacion',
                'cargo', 'ubicacion', 'salario_min', 'salario_max', 'per_page',
            ]),
            'seo' => [
                'image' => asset('assets/images/logo/logo SEO.png'),
            ],
        ]);
    }

    public function homeWithoutModal(Request $request)
    {
        return $this->index($request);
    }
}
