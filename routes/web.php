<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExamController as AdminExamController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SubcategoryController as AdminSubcategoryController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModuleFileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentsWebhookController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\WatiAssignController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'homeWithoutModal'])->name('home.no-modal');

Route::get('/home2', function () {
    return view('app2');
});

// Legal Pages
Route::get('/terminos-de-servicio', function () {
    return Inertia::render('Legal/TermsOfService');
})->name('terms-of-service');

Route::get('/politica-de-privacidad', function () {
    return Inertia::render('Legal/PrivacyPolicy');
})->name('privacy-policy');

// Sitemap Routes
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']);
Route::get('/sitemap-pages.xml', [\App\Http\Controllers\SitemapController::class, 'pages']);
Route::get('/sitemap-courses.xml', [\App\Http\Controllers\SitemapController::class, 'courses']);
Route::get('/sitemap-categories.xml', [\App\Http\Controllers\SitemapController::class, 'categories']);

Route::resource('cursos', \App\Http\Controllers\CourseController::class);
Route::resource('categorias', \App\Http\Controllers\CategoryController::class);

Route::get('/categorias/{categorySlug}/{subcategorySlug}', [SubcategoryController::class, 'show'])->name('subcategories.show');
Route::get('/categorias/{categorySlug}/{parentSlug}/{childSlug}', [SubcategoryController::class, 'childShow'])->name('subcategories.child.show');

Route::post('/payments/checkout', [OrderController::class, 'checkout'])->middleware('auth');
Route::post('/payments/confirmation', [PaymentsWebhookController::class, 'confirmation']);
Route::post('/payments/wompi/webhook', [PaymentsWebhookController::class, 'wompiWebhook']);
Route::get('/payments/wompi/test', function () {
    \Illuminate\Support\Facades\Log::info('TEST ENDPOINT ALCANZADO!');

    return response()->json(['status' => 'ok', 'message' => 'Webhook endpoint is reachable', 'timestamp' => now()]);
});
Route::get('/payments/status', [PaymentsWebhookController::class, 'response']);

// WATI Assign Material Routes
Route::post('/api/wati/save-material', [WatiAssignController::class, 'saveMaterial'])->name('wati.save-material');
Route::post('/api/wati/register-and-assign', [WatiAssignController::class, 'registerAndAssign'])->name('wati.register-assign');
Route::get('/api/wati/assign/verify', [WatiAssignController::class, 'verify'])->name('wati.assign.verify');

Route::group(['middleware' => ['auth', 'verified']], function () {

    Route::get('users/me', [UserController::class, 'getMe']);
    Route::post('users/change-password', [UserController::class, 'changePassword']);
    Route::get('change-password', function () {
        return Inertia::render('Auth/ChangePassword');
    })->name('change-password');
    Route::get('mis_cursos', [CourseController::class, 'myCourses']);
    Route::get('cursos/modulos/archivos/{id}', [CourseController::class, 'file']);

    // Mis Compras
    Route::get('mis_compras', [OrderController::class, 'myPurchases']);
    Route::get('mis_compras/list', [OrderController::class, 'myPurchasesList']);

    Route::middleware('admin')->group(function () {
        Route::get('admin', [DashboardController::class, 'index'])->name('dashboard');

        Route::group(['prefix' => 'admin'], function () {
            Route::group(['prefix' => 'usuarios'], function () {
                Route::get('list', [UserController::class, 'list']);
            });
            Route::resource('usuarios', UserController::class);

            Route::group(['prefix' => 'roles'], function () {
                Route::get('list', [RoleController::class, 'list']);
            });

            Route::resource('roles', RoleController::class);

            Route::group(['prefix' => 'cursos'], function () {
                Route::post('list', [AdminCourseController::class, 'list']);
                Route::get('{id}/codes-template', [AdminCourseController::class, 'codesTemplate'])->where('id', '[0-9]+');
                Route::post('{id}/copy', [AdminCourseController::class, 'copy'])->where('id', '[0-9]+');
                Route::post('{id}/metadata', [AdminCourseController::class, 'metadata'])->where('id', '[0-9]+');
                Route::post('{id}/import-codes', [AdminCourseController::class, 'importCodes'])->where('id', '[0-9]+');

                Route::group(['prefix' => 'modulos'], function () {
                    Route::resource('archivos', ModuleFileController::class);
                    Route::post('copy', [ModuleController::class, 'copy']);
                    Route::post('reorder', [ModuleController::class, 'reorder']);

                    // Exámenes
                    Route::get('{moduleId}/examen', [AdminExamController::class, 'show']);
                    Route::post('{moduleId}/examen', [AdminExamController::class, 'store']);
                    Route::put('{moduleId}/examen/{id}', [AdminExamController::class, 'update']);
                    Route::delete('{moduleId}/examen/{id}', [AdminExamController::class, 'destroy']);
                    Route::post('examen/preguntas', [AdminExamController::class, 'storeQuestion']);
                    Route::put('examen/preguntas/{id}', [AdminExamController::class, 'updateQuestion']);
                    Route::delete('examen/preguntas/{id}', [AdminExamController::class, 'destroyQuestion']);
                    Route::post('examen/preguntas/limpiar', [AdminExamController::class, 'clearQuestions']);
                    Route::post('examen/preguntas/reorder', [AdminExamController::class, 'reorderQuestions']);
                    Route::post('examen/copy', [AdminExamController::class, 'copy']);
                    Route::get('examen/modulos-disponibles', [AdminExamController::class, 'modulesList']);
                    Route::get('examen/plantilla', [AdminExamController::class, 'downloadTemplate']);
                    Route::post('examen/importar', [AdminExamController::class, 'importQuestions']);
                });

                Route::resource('modulos', ModuleController::class);

            });

            Route::resource('cursos', AdminCourseController::class)->names([
                'index' => 'admin.cursos.index',
                'create' => 'admin.cursos.create',
                'store' => 'admin.cursos.store',
                'show' => 'admin.cursos.show',
                'edit' => 'admin.cursos.edit',
                'update' => 'admin.cursos.update',
                'destroy' => 'admin.cursos.destroy',
            ]);

            Route::group(['prefix' => 'suscripciones'], function () {
                Route::post('list', [SubscriptionController::class, 'list']);
            });

            Route::resource('suscripciones', SubscriptionController::class);

            Route::group(['prefix' => 'categorias'], function () {
                Route::post('list', [AdminCategoryController::class, 'list']);
                Route::post('courses-tree', [AdminCategoryController::class, 'coursesTree']);
                Route::post('tree', [AdminCategoryController::class, 'tree']);
                Route::post('{categoryId}/subcategorias/list', [AdminSubcategoryController::class, 'list']);
                Route::post('{categoryId}/subcategorias', [AdminSubcategoryController::class, 'store']);
                Route::put('{categoryId}/subcategorias/{subcategoryId}', [AdminSubcategoryController::class, 'update']);
                Route::delete('{categoryId}/subcategorias/{subcategoryId}', [AdminSubcategoryController::class, 'destroy']);
            });

            Route::resource('categorias', AdminCategoryController::class)->names([
                'index' => 'admin.categorias.index',
                'create' => 'admin.categorias.create',
                'store' => 'admin.categorias.store',
                'show' => 'admin.categorias.show',
                'edit' => 'admin.categorias.edit',
                'update' => 'admin.categorias.update',
                'destroy' => 'admin.categorias.destroy',
            ]);

            Route::group(['prefix' => 'ordenes'], function () {
                Route::get('list', [AdminOrderController::class, 'list']);
                Route::patch('{id}/status', [AdminOrderController::class, 'updateStatus']);
                Route::patch('{id}/upgrade-demo', [AdminOrderController::class, 'upgradeDemoToPaid']);
                Route::delete('item/{id}', [AdminOrderController::class, 'deleteItem']);
            });

            Route::resource('ordenes', AdminOrderController::class);

        });
    });

    // Simulacros - subdominio (producción)
    Route::domain(config('app.simulacros_domain', 'simulacros.localhost'))->group(function () {
        Route::get('/', [\App\Http\Controllers\SimulacroController::class, 'index'])->name('simulacros.index');
        Route::get('/examen/{examId}', [\App\Http\Controllers\SimulacroController::class, 'show'])->name('simulacros.show');
        Route::post('/iniciar', [\App\Http\Controllers\SimulacroController::class, 'start'])->name('simulacros.start');
        Route::post('/guardar-progreso', [\App\Http\Controllers\SimulacroController::class, 'saveProgress'])->name('simulacros.saveProgress');
        Route::post('/enviar', [\App\Http\Controllers\SimulacroController::class, 'submit'])->name('simulacros.submit');
        Route::get('/resultados/{attemptId}', [\App\Http\Controllers\SimulacroController::class, 'results'])->name('simulacros.results');
    });

    // Simulacros - prefijo (funciona en local y producción)
    Route::prefix('simulacros')->group(function () {
        Route::get('/', [\App\Http\Controllers\SimulacroController::class, 'index']);
        Route::get('/examen/{examId}', [\App\Http\Controllers\SimulacroController::class, 'show']);
        Route::post('/iniciar', [\App\Http\Controllers\SimulacroController::class, 'start']);
        Route::post('/guardar-progreso', [\App\Http\Controllers\SimulacroController::class, 'saveProgress']);
        Route::post('/enviar', [\App\Http\Controllers\SimulacroController::class, 'submit']);
        Route::get('/resultados/{attemptId}', [\App\Http\Controllers\SimulacroController::class, 'results']);
    });

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
