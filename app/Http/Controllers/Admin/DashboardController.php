<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Total de usuarios (excluyendo al admin actual)
        $totalStudents = User::where('id', '!=', auth()->id())
            ->whereHas('roles', function ($query) {
                $query->where('name', 'user');
            })
            ->count();

        // Total de usuarios activos este mes
        $activeStudentsThisMonth = User::where('id', '!=', auth()->id())
            ->whereHas('roles', function ($query) {
                $query->where('name', 'user');
            })
            ->whereMonth('created_at', now()->month)
            ->count();

        // Cambio porcentual de usuarios
        $lastMonthStudents = User::where('id', '!=', auth()->id())
            ->whereHas('roles', function ($query) {
                $query->where('name', 'user');
            })
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();

        $studentChangePercent = $lastMonthStudents > 0
            ? round((($activeStudentsThisMonth - $lastMonthStudents) / $lastMonthStudents) * 100)
            : 0;

        // Total de cursos
        $totalCourses = Course::count();
        $activeCourses = Course::where('published', true)->where('active', true)->count();
        $courseChangePercent = 5; // Placeholder

        // Ingresos totales
        $totalRevenue = Order::where('status_id', 2)
            ->sum('amount') ?? 0;

        // Ingresos este mes
        $monthlyRevenue = Order::where('status_id', 2)
            ->whereMonth('created_at', now()->month)
            ->sum('amount') ?? 0;

        // Cambio porcentual de ingresos
        $lastMonthRevenue = Order::where('status_id', 2)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('amount') ?? 0;

        $revenueChangePercent = $lastMonthRevenue > 0
            ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100)
            : 0;

        // Órdenes completadas
        $completedOrders = Order::where('status_id', 2)->count();
        $totalOrders = Order::count();
        $orderChangePercent = 3; // Placeholder

        // Actividades recientes
        $recentOrders = Order::with('user')
            ->latest('created_at')
            ->limit(4)
            ->get()
            ->map(function ($order) {
                return [
                    'icon' => 'feather-shopping-cart',
                    'text' => 'Orden completada: '.$order->user->name,
                    'time' => $order->created_at->diffForHumans(),
                    'color' => 'linear-gradient(135deg, #10b981 0%, #34d399 100%)',
                    'type' => 'success',
                    'status' => 'Completado',
                ];
            });

        $recentUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'student');
        })
            ->latest('created_at')
            ->limit(4)
            ->get()
            ->map(function ($user) {
                return [
                    'icon' => 'feather-user-plus',
                    'text' => 'Nuevo usuario: '.$user->name,
                    'time' => $user->created_at->diffForHumans(),
                    'color' => 'linear-gradient(135deg, #133a54 0%, #1a5a80 100%)',
                    'type' => 'new',
                    'status' => 'Nuevo',
                ];
            });

        $recentCourses = Course::latest('created_at')
            ->limit(4)
            ->get()
            ->map(function ($course) {
                return [
                    'icon' => 'feather-book',
                    'text' => 'Nuevo curso: '.$course->title,
                    'time' => $course->created_at->diffForHumans(),
                    'color' => 'linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%)',
                    'type' => 'new',
                    'status' => 'Nuevo',
                ];
            });

        $activities = collect($recentOrders)
            ->merge($recentUsers)
            ->merge($recentCourses)
            ->sortByDesc(function ($item) {
                return strtotime($item['time']);
            })
            ->values()
            ->take(8)
            ->toArray();

        // Datos para gráfico de ingresos (últimos 6 meses)
        $revenueData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = Order::where('status_id', 2)
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('amount') ?? 0;

            $revenueData[] = [
                'month' => $month->format('M'),
                'amount' => $revenue,
            ];
        }

        $totalCategories = Category::count();

        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => [
                'students' => [
                    'label' => 'Total de Estudiantes',
                    'value' => $totalStudents,
                    'icon' => 'feather-users',
                    'changePercent' => $studentChangePercent,
                    'isPositive' => $studentChangePercent >= 0,
                ],
                'courses' => [
                    'label' => 'Cursos Activos',
                    'value' => $activeCourses,
                    'icon' => 'feather-book-open',
                    'changePercent' => $courseChangePercent,
                    'isPositive' => true,
                ],
                'revenue' => [
                    'label' => 'Ingresos Totales',
                    'value' => '$'.number_format($totalRevenue, 0),
                    'icon' => 'feather-dollar-sign',
                    'changePercent' => $revenueChangePercent,
                    'isPositive' => $revenueChangePercent >= 0,
                ],
                'orders' => [
                    'label' => 'Órdenes Completadas',
                    'value' => $completedOrders,
                    'icon' => 'feather-check-circle',
                    'changePercent' => $orderChangePercent,
                    'isPositive' => true,
                ],
            ],
            'monthlyRevenue' => (float) $monthlyRevenue,
            'revenueData' => $revenueData,
            'activities' => $activities,
            'summary' => [
                'totalStudents' => $totalStudents,
                'totalCourses' => $totalCourses,
                'totalOrders' => $totalOrders,
                'totalRevenue' => $totalRevenue,
                'totalCategories' => $totalCategories,
            ],
        ]);
    }
}
