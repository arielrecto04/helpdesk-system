<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HelpdeskTeamsController;
use App\Http\Controllers\MyTicketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TeamTicketsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\TagsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Custom Ticket Routes
    Route::get('/my-tickets', [MyTicketController::class, 'index'])->name('tickets.my');
    Route::get('/my-tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::get('/team/{teamId}/tickets', [TeamTicketsController::class, 'index'])->name('team.tickets');

    Route::get('/all-tickets', [TicketController::class, 'index'])->name('tickets.all');
    // Standard Ticket Resource Routes
    Route::resource('tickets', TicketController::class);

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
    Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');

    Route::get('/helpdeskteams', [HelpdeskTeamsController::class, 'index'])->name('helpdeskteams.index');
    Route::get('/helpdeskteams/create', [HelpdeskTeamsController::class, 'create'])->name('helpdeskteams.create');
    Route::post('/helpdeskteams', [HelpdeskTeamsController::class, 'store'])->name('helpdeskteams.store');
    Route::get('/helpdeskteams/{team}', [HelpdeskTeamsController::class, 'show'])->name('helpdeskteams.show');
    Route::get('/helpdeskteams/{team}/edit', [HelpdeskTeamsController::class, 'edit'])->name('helpdeskteams.edit');
    Route::put('/helpdeskteams/{team}', [HelpdeskTeamsController::class, 'update'])->name('helpdeskteams.update');
    Route::delete('/helpdeskteams/{team}', [HelpdeskTeamsController::class, 'destroy'])->name('helpdeskteams.destroy');

    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
    Route::post('/users', [UsersController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UsersController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');

    Route::get('/customers', [CustomersController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomersController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomersController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomersController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomersController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomersController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomersController::class, 'destroy'])->name('customers.destroy');

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    Route::get('/employees/{employee}/create-account', [EmployeeController::class, 'createAccount'])->name('employees.createAccount');
    Route::post('/employees/{employee}/store-account', [EmployeeController::class, 'storeAccount'])->name('employees.storeAccount');

    Route::get('/departments', [DepartmentsController::class, 'index'])->name('departments.index');
    Route::get('/departments/create', [DepartmentsController::class, 'create'])->name('departments.create');
    Route::post('/departments', [DepartmentsController::class, 'store'])->name('departments.store');
    Route::get('/departments/{department}', [DepartmentsController::class, 'show'])->name('departments.show');
    Route::get('/departments/{department}/edit', [DepartmentsController::class, 'edit'])->name('departments.edit');
    Route::put('/departments/{department}', [DepartmentsController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{department}', [DepartmentsController::class, 'destroy'])->name('departments.destroy');

    Route::get('/positions', [PositionsController::class, 'index'])->name('positions.index');
    Route::get('/positions/create', [PositionsController::class, 'create'])->name('positions.create');
    Route::post('/positions', [PositionsController::class, 'store'])->name('positions.store');
    // Route::get('/positions/{position}', [PositionsController::class, 'show'])->name('positions.show');
    Route::get('/positions/{position}/edit', [PositionsController::class, 'edit'])->name('positions.edit');
    Route::put('/positions/{position}', [PositionsController::class, 'update'])->name('positions.update');
    Route::delete('/positions/{position}', [PositionsController::class, 'destroy'])->name('positions.destroy');

    Route::get('/tags', [TagsController::class, 'index'])->name('tags.index');
    Route::get('/tags/create', [TagsController::class, 'create'])->name('tags.create');
    Route::post('/tags', [TagsController::class, 'store'])->name('tags.store');
    Route::get('/tags/{tag}', [TagsController::class, 'show'])->name('tags.show');
    Route::get('/tags/{tag}/edit', [TagsController::class, 'edit'])->name('tags.edit');
    Route::put('/tags/{tag}', [TagsController::class, 'update'])->name('tags.update');
    Route::delete('/tags/{tag}', [TagsController::class, 'destroy'])->name('tags.destroy');

});

require __DIR__.'/auth.php';
