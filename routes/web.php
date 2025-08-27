<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Auth;

// Default welcome page
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect('/login');
    }

    $user = Auth::user();

    if ($user->role->name === 'super_admin') {
        return redirect()->route('super-admin.dashboard');
    } elseif ($user->role->name === 'admin') {
        return redirect()->route('admin.dashboard');
    } else {
        return redirect()->route('staff.dashboard');
    }
})->name('home');

// Dashboard (generic)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Super Admin Routes
Route::prefix('super-admin')->name('super-admin.')->middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');

    // User Management
    Route::get('/users', [SuperAdminController::class, 'users'])->name('users');
    Route::patch('/users/{user}/toggle-status', [SuperAdminController::class, 'toggleUserStatus'])->name('users.toggle-status');

    // Department Management
    Route::get('/departments', [SuperAdminController::class, 'departments'])->name('departments');
    Route::post('/departments', [SuperAdminController::class, 'createDepartment'])->name('departments.create');
    Route::patch('/departments/{department}/toggle-status', [SuperAdminController::class, 'toggleDepartmentStatus'])->name('departments.toggle-status');

    // Global Reports
    Route::get('/reports', [SuperAdminController::class, 'reports'])->name('reports');
    Route::post('/reports/generate', [SuperAdminController::class, 'generateGlobalReport'])->name('reports.generate');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Staff Management
    Route::get('/staff', [AdminController::class, 'staff'])->name('staff');
    Route::patch('/staff/{user}/toggle-status', [AdminController::class, 'toggleStaffStatus'])->name('staff.toggle-status');

    // Program Management
    Route::resource('programs', ProgramController::class)->except(['create']);
    Route::post('/programs', [AdminController::class, 'createProgram'])->name('programs.create');

    // Service Management
    Route::resource('services', ServiceController::class)->except(['create']);
    Route::post('/services', [AdminController::class, 'createService'])->name('services.create');

    // Department Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::post('/reports/generate', [AdminController::class, 'generateDepartmentReport'])->name('reports.generate');
});

// Staff Routes
Route::prefix('staff')->name('staff.')->middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');

    // Client Management
    Route::resource('clients', ClientController::class);

    // Service Registration
    Route::get('/registrations', [StaffController::class, 'registrations'])->name('registrations');
    Route::post('/registrations', [StaffController::class, 'registerClient'])->name('registrations.create');
    Route::patch('/registrations/{registration}/status', [StaffController::class, 'updateRegistrationStatus'])->name('registrations.update-status');
});

// Shared (accessible by any active authenticated user)
Route::middleware(['auth', 'active.user'])->group(function () {
    // Read-only access for programs/services/reports
    Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
    Route::get('/programs/{program}', [ProgramController::class, 'show'])->name('programs.show');

    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');

    // AJAX
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/programs/{department}', [ProgramController::class, 'getByDepartment'])->name('programs.by-department');
        Route::get('/services/{program}', [ServiceController::class, 'getByProgram'])->name('services.by-program');
        Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search');
    });
});

// Breeze/Jetstream auth routes
require __DIR__.'/auth.php';
