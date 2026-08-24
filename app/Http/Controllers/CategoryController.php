<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {

        $per_page = $this->getPerPage($request);
        $page = $this->getPage($request);

        $categories = Category::query()->published()->paginate($per_page, $page);

        return response()->json($categories);

    }

    public function show(Request $request, string $id)
    {
        $per_page = $request->input('per_page', 15);

        $category = Category::query()->published()->slug($id)->firstOrFail();

        if ($category->enable_subcategories) {
            $subcategories = Subcategory::query()
                ->with(['image', 'courses'])
                ->where('category_id', $category->id)
                ->whereNull('parent_id')
                ->published()
                ->orderBy('title')
                ->paginate($per_page);

            // Filtro personalizado (OPEC): cuando hay filtro activo se muestran los
            // cursos de toda la categoria que coincidan con el valor seleccionado.
            $courses = null;
            if ($category->enable_custom_filter && $request->filled('filter')) {
                $filterValue = $request->input('filter');
                $matchValues = [$filterValue];

                foreach ($category->custom_filter_options ?? [] as $option) {
                    if (($option['label'] ?? null) === $filterValue) {
                        $values = $option['values'] ?? (isset($option['value']) ? [$option['value']] : []);
                        $matchValues = array_unique(array_merge($matchValues, $values));
                        break;
                    }
                }

                $courses = Course::query()
                    ->with(['metadata', 'modules'])
                    ->where('category_id', $category->id)
                    ->published()
                    ->withUserSubscription()
                    ->withUserPaidOrders()
                    ->withCount(['modules', 'subscriptions'])
                    ->orderBy('title')
                    ->whereHas('metadata', function ($q) use ($matchValues) {
                        $q->where(function ($inner) use ($matchValues) {
                            foreach ($matchValues as $val) {
                                $inner->orWhere('custom_filter_value', $val)
                                    ->orWhereRaw("CONCAT('||', custom_filter_value, '||') LIKE ?", ['%||'.$val.'||%']);
                            }
                        });
                    })
                    ->paginate($per_page);
            }

            $seoImage = $category->image
                ? asset($category->image->path ?? 'favicon.svg')
                : asset('favicon.svg');

            return Inertia::render('Catalog/Subcategories/Index', [
                'category' => $category,
                'subcategories' => $subcategories->appends($request->query()),
                'courses' => $courses?->appends($request->query()),
                'seo' => [
                    'title' => $category->title.' - Subcategorías | Construyendo Méritos con Excelencia',
                    'description' => $category->description ?? 'Explora todas las subcategorías de '.$category->title.' - Material Procuraduría 2026.',
                    'image' => $seoImage,
                    'url' => $request->url(),
                    'keywords' => $category->title.', subcategorías, Procuraduría 2026, Construyendo Méritos',
                ],
            ]);
        }

        $query = Course::query()
            ->with(['metadata', 'modules'])
            ->where('category_id', $category->id)
            ->published()
            ->withUserSubscription()
            ->withUserPaidOrders()
            ->withCount(['modules', 'subscriptions'])
            ->orderBy('title');

        if ($request->has('search') && ! empty($request->input('search'))) {
            $searchTerm = $request->input('search');
            $normalizedSearch = str_replace([' ', '_'], '', $searchTerm);

            \Log::info('Search term: '.$searchTerm);

            $query->where(function ($q) use ($searchTerm, $normalizedSearch, $category) {
                // Buscar en título y descripción del curso
                $q->where('title', 'like', '%'.$searchTerm.'%')
                    ->orWhere('description', 'like', '%'.$searchTerm.'%');

                // Buscar en el valor del filtro personalizado de metadata (normalizando espacios y guiones bajos)
                $q->orWhereHas('metadata', function ($metaQuery) use ($normalizedSearch) {
                    $metaQuery->whereRaw(
                        "REPLACE(REPLACE(custom_filter_value, ' ', ''), '_', '') LIKE ?",
                        ['%'.$normalizedSearch.'%']
                    );
                });

                // Buscar en las etiquetas y valores de las opciones del filtro personalizado
                if ($category->enable_custom_filter && ! empty($category->custom_filter_options)) {
                    foreach ($category->custom_filter_options as $option) {
                        $normalizedLabel = str_replace([' ', '_'], '', $option['label']);
                        $optionValues = $option['values'] ?? (isset($option['value']) ? [$option['value']] : []);
                        $matched = false;

                        // Buscar coincidencia en la etiqueta
                        if (stripos($normalizedLabel, $normalizedSearch) !== false) {
                            $matched = true;
                        }

                        // Buscar coincidencia en cualquiera de los valores del grupo
                        if (! $matched) {
                            foreach ($optionValues as $val) {
                                $normalizedVal = str_replace([' ', '_'], '', $val);
                                if (stripos($normalizedVal, $normalizedSearch) !== false) {
                                    $matched = true;
                                    break;
                                }
                            }
                        }

                        if ($matched) {
                            $matchValues = array_unique(array_merge($optionValues, [$option['label']]));
                            $q->orWhereHas('metadata', function ($metaQuery) use ($matchValues) {
                                $metaQuery->where(function ($inner) use ($matchValues) {
                                    foreach ($matchValues as $val) {
                                        $inner->orWhere('custom_filter_value', $val)
                                            ->orWhereRaw("CONCAT('||', custom_filter_value, '||') LIKE ?", ['%||'.$val.'||%']);
                                    }
                                });
                            });
                        }
                    }
                }
            });
        }

        if ($request->has('filter') && ! empty($request->input('filter'))) {
            $filterValue = $request->input('filter');
            $matchValues = [$filterValue];

            $options = $category->enable_custom_filter ? ($category->custom_filter_options ?? []) : [];
            foreach ($options as $option) {
                $values = $option['values'] ?? (isset($option['value']) ? [$option['value']] : []);
                if ($option['label'] === $filterValue) {
                    $matchValues = array_unique(array_merge($matchValues, $values));
                    break;
                }
            }

            $query->whereHas('metadata', function ($q) use ($matchValues) {
                $q->where(function ($inner) use ($matchValues) {
                    foreach ($matchValues as $val) {
                        $inner->orWhere('custom_filter_value', $val)
                            ->orWhereRaw("CONCAT('||', custom_filter_value, '||') LIKE ?", ['%||'.$val.'||%']);
                    }
                });
            });
        }

        $courses = $query->paginate($per_page);

        $seoImage = $category->image
            ? asset($category->image->path ?? 'favicon.svg')
            : asset('favicon.svg');

        return Inertia::render('Catalog/Categories/Show', [
            'category' => $category,
            'courses' => $courses->appends($request->query()),
            'seo' => [
                'title' => $category->title.' - Material Procuraduría 2026 | Construyendo Méritos con Excelencia',
                'description' => $category->description ?? 'Explora todos los materiales de '.$category->title.' para la Procuraduría General de la Nación 2026.',
                'image' => $seoImage,
                'url' => $request->url(),
                'keywords' => $category->title.', Procuraduría 2026, material estudio, Construyendo Méritos',
            ],
        ]);
    }
}
