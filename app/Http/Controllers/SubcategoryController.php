<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubcategoryController extends Controller
{
    public function childShow(Request $request, string $categorySlug, string $parentSlug, string $childSlug)
    {
        $per_page = $request->input('per_page', 15);

        $category = Category::query()->published()->slug($categorySlug)->firstOrFail();
        $parentSubcategory = Subcategory::query()->published()->slug($parentSlug)->where('category_id', $category->id)->firstOrFail();
        $subcategory = Subcategory::query()->published()->slug($childSlug)->where('parent_id', $parentSubcategory->id)->firstOrFail();

        $query = Course::query()
            ->with(['metadata', 'modules'])
            ->where('category_id', $category->id)
            ->where('subcategory_id', $subcategory->id)
            ->published()
            ->withUserSubscription()
            ->withUserPaidOrders()
            ->withCount(['modules', 'subscriptions'])
            ->orderBy('title');

        if ($request->has('search') && ! empty($request->input('search'))) {
            $searchTerm = $request->input('search');
            $normalizedSearch = str_replace([' ', '_'], '', $searchTerm);

            $query->where(function ($q) use ($searchTerm, $normalizedSearch, $category) {
                $q->where('title', 'like', '%'.$searchTerm.'%')
                    ->orWhere('description', 'like', '%'.$searchTerm.'%');

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

                        if (stripos($normalizedLabel, $normalizedSearch) !== false) {
                            $matched = true;
                        }

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

        $seoImage = $subcategory->image
            ? asset($subcategory->image->path ?? 'favicon.svg')
            : ($category->image ? asset($category->image->path ?? 'favicon.svg') : asset('favicon.svg'));

        return Inertia::render('Catalog/Subcategories/Show', [
            'category' => $category,
            'subcategory' => $subcategory,
            'parentSubcategory' => $parentSubcategory,
            'courses' => $courses->appends($request->query()),
            'seo' => [
                'title' => $subcategory->title.' - '.$category->title.' | Construyendo Méritos con Excelencia',
                'description' => $subcategory->description ?? 'Material de estudio '.$subcategory->title.' en '.$category->title.' - Procuraduría 2026.',
                'image' => $seoImage,
                'url' => $request->url(),
                'keywords' => $subcategory->title.', '.$category->title.', Procuraduría 2026, Construyendo Méritos',
            ],
        ]);
    }

    public function show(Request $request, string $categorySlug, string $subcategorySlug)
    {
        $per_page = $request->input('per_page', 15);

        $category = Category::query()->published()->slug($categorySlug)->firstOrFail();
        $subcategory = Subcategory::query()->published()->slug($subcategorySlug)->where('category_id', $category->id)->firstOrFail();

        $hasChildren = Subcategory::query()->published()->where('parent_id', $subcategory->id)->exists();

        $seoImage = $subcategory->image
            ? asset($subcategory->image->path ?? 'favicon.svg')
            : ($category->image ? asset($category->image->path ?? 'favicon.svg') : asset('favicon.svg'));

        // If subcategory has children, show nested subcategories
        if ($hasChildren) {
            $children = Subcategory::query()
                ->with(['image', 'courses'])
                ->where('parent_id', $subcategory->id)
                ->published()
                ->orderBy('title')
                ->paginate($per_page);

            return Inertia::render('Catalog/Subcategories/Index', [
                'category' => $subcategory,
                'subcategories' => $children->appends($request->query()),
                'parentCategory' => $category,
                'seo' => [
                    'title' => $subcategory->title.' - '.$category->title.' | Construyendo Méritos con Excelencia',
                    'description' => $subcategory->description ?? 'Subcategorías de '.$subcategory->title.' - Procuraduría 2026.',
                    'image' => $seoImage,
                    'url' => $request->url(),
                    'keywords' => $subcategory->title.', '.$category->title.', Procuraduría 2026, Construyendo Méritos',
                ],
            ]);
        }

        // Otherwise show courses for this subcategory
        $query = Course::query()
            ->with(['metadata', 'modules'])
            ->where('category_id', $category->id)
            ->where('subcategory_id', $subcategory->id)
            ->published()
            ->withUserSubscription()
            ->withUserPaidOrders()
            ->withCount(['modules', 'subscriptions'])
            ->orderBy('title');

        if ($request->has('search') && ! empty($request->input('search'))) {
            $searchTerm = $request->input('search');
            $normalizedSearch = str_replace([' ', '_'], '', $searchTerm);

            $query->where(function ($q) use ($searchTerm, $normalizedSearch, $category) {
                $q->where('title', 'like', '%'.$searchTerm.'%')
                    ->orWhere('description', 'like', '%'.$searchTerm.'%');

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

                        if (stripos($normalizedLabel, $normalizedSearch) !== false) {
                            $matched = true;
                        }

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

        $seoImage = $subcategory->image
            ? asset($subcategory->image->path ?? 'favicon.svg')
            : ($category->image ? asset($category->image->path ?? 'favicon.svg') : asset('favicon.svg'));

        return Inertia::render('Catalog/Subcategories/Show', [
            'category' => $category,
            'subcategory' => $subcategory,
            'courses' => $courses->appends($request->query()),
            'seo' => [
                'title' => $subcategory->title.' - '.$category->title.' | Construyendo Méritos con Excelencia',
                'description' => $subcategory->description ?? 'Material de estudio '.$subcategory->title.' en '.$category->title.' - Procuraduría 2026.',
                'image' => $seoImage,
                'url' => $request->url(),
                'keywords' => $subcategory->title.', '.$category->title.', Procuraduría 2026, Construyendo Méritos',
            ],
        ]);
    }
}
