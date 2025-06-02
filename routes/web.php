<?php

use App\Http\Controllers\Backoffice\TourController;
use App\Http\Controllers\Backoffice\CitiesController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Home page
Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// Alternative view-based home (uncomment if needed)
// Route::get('/', function () {
//     return view('welcome');
// });

// Database connection test route
Route::get('/test-db', function () {
    try {
        $userCount = DB::table('users')->count();
        $tourCount = DB::table('tours')->count();
        $cityCount = DB::table('cities')->count();

        return response()->json([
            'database' => 'Connected successfully!',
            'users_count' => $userCount,
            'tours_count' => $tourCount,
            'cities_count' => $cityCount,
            'timestamp' => now()
        ]);
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Database connection failed',
            'message' => $e->getMessage()
        ], 500);
    }
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (General)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // User dashboard (basic authenticated users)
    Route::get('/profile', function () {
        return Inertia::render('profile/show');
    })->name('profile.show');

});

/*
|--------------------------------------------------------------------------
| Backoffice Routes (Admin/Staff)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Main dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('backoffice/dashboard');
    })->name('dashboard');

    // Cities management
    Route::prefix('cities')->name('cities.')->group(function () {
        Route::get('/', [CitiesController::class, 'index'])->name('index');
        Route::get('/create', [CitiesController::class, 'create'])->name('create');
        Route::post('/', [CitiesController::class, 'store'])->name('store');
        Route::get('/{id}', [CitiesController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CitiesController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CitiesController::class, 'update'])->name('update');
        Route::delete('/{id}', [CitiesController::class, 'destroy'])->name('destroy');
    });

    // Tours management
    Route::prefix('tours')->name('tours.')->group(function () {
        Route::get('/', [TourController::class, 'index'])->name('index');
        Route::get('/create', [TourController::class, 'create'])->name('create');
        Route::post('/', [TourController::class, 'store'])->name('store');
        Route::get('/{id}', [TourController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [TourController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TourController::class, 'update'])->name('update');
        Route::delete('/{id}', [TourController::class, 'destroy'])->name('destroy');
    });

});

/*
|--------------------------------------------------------------------------
| Admin Only Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {

    // Admin dashboard with advanced analytics
    Route::get('/admin/dashboard', function () {
        return Inertia::render('admin/dashboard');
    })->name('admin.dashboard');

    // User management (Admin only)
    Route::prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('admin/users/index');
        })->name('index');
        Route::get('/{id}/edit', function ($id) {
            return Inertia::render('admin/users/edit', ['userId' => $id]);
        })->name('edit');
    });

    // System settings
    Route::prefix('admin/settings')->name('admin.settings.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('admin/settings/index');
        })->name('index');
        Route::get('/general', function () {
            return Inertia::render('admin/settings/general');
        })->name('general');
    });

});

/*
|--------------------------------------------------------------------------
| Include Additional Route Files
|--------------------------------------------------------------------------
*/

// Include authentication routes (login, register, etc.)
require __DIR__.'/auth.php';

// Include settings routes if the file exists
if (file_exists(__DIR__.'/settings.php')) {
    require __DIR__.'/settings.php';
}
