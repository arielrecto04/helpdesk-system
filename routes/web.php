<?php

use App\Http\Controllers\AllTicketsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\HelpdeskTeamsController;
use App\Http\Controllers\MyTicketsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamTicketsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TicketMessageController;
use App\Http\Controllers\TicketAnalysisController;
use App\Http\Controllers\CustomerRatingsController;
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

    Route::get('/customer/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');
    Route::post('/customer/tickets', [CustomerDashboardController::class, 'storeTicket'])->name('customer.tickets.store');
    Route::get('/customer/tickets/{ticket}', [CustomerDashboardController::class, 'showTicket'])->name('customer.tickets.show');
    Route::post('/customer/tickets/{ticket}/rating', [CustomerDashboardController::class, 'storeRating'])->name('customer.tickets.rating');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:view_dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('permission:view_profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('permission:view_profile');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('permission:view_profile');

    Route::get('/teamtickets', [TeamTicketsController::class, 'index'])->name('teamtickets.index')->middleware('permission:show_teamtickets');
    Route::get('/teamtickets/create', [TeamTicketsController::class, 'create'])->name('teamtickets.create')->middleware('permission:create_teamtickets');
    Route::post('/teamtickets', [TeamTicketsController::class, 'store'])->name('teamtickets.store')->middleware('permission:create_teamtickets');
    Route::post('/teamtickets/bulk-update', [TeamTicketsController::class, 'bulkUpdate'])->name('teamtickets.bulk-update')->middleware('permission:edit_teamtickets');
    Route::post('/teamtickets/bulk-delete', [TeamTicketsController::class, 'bulkDelete'])->name('teamtickets.bulk-delete')->middleware('permission:delete_teamtickets');
    Route::get('/teamtickets/{ticket}', [TeamTicketsController::class, 'show'])->name('teamtickets.show')->middleware('permission:show_teamtickets');
    Route::get('/teamtickets/{ticket}/edit', [TeamTicketsController::class, 'edit'])->name('teamtickets.edit')->middleware('permission:edit_teamtickets');
    Route::put('/teamtickets/{ticket}', [TeamTicketsController::class, 'update'])->name('teamtickets.update')->middleware('permission:edit_teamtickets');
    Route::delete('/teamtickets/{ticket}', [TeamTicketsController::class, 'destroy'])->name('teamtickets.destroy')->middleware('permission:delete_teamtickets');

    Route::get('/alltickets', [AllTicketsController::class, 'index'])->name('alltickets.index')->middleware('permission:show_alltickets');
    Route::get('/alltickets/create', [AllTicketsController::class, 'create'])->name('alltickets.create')->middleware('permission:create_alltickets');
    Route::post('/alltickets', [AllTicketsController::class, 'store'])->name('alltickets.store')->middleware('permission:create_alltickets');
    Route::post('/alltickets/bulk-update', [AllTicketsController::class, 'bulkUpdate'])->name('alltickets.bulk-update')->middleware('permission:edit_alltickets');
    Route::post('/alltickets/bulk-delete', [AllTicketsController::class, 'bulkDelete'])->name('alltickets.bulk-delete')->middleware('permission:delete_alltickets');
    Route::get('/alltickets/{ticket}', [AllTicketsController::class, 'show'])->name('alltickets.show')->middleware('permission:show_alltickets');
    Route::get('/alltickets/{ticket}/edit', [AllTicketsController::class, 'edit'])->name('alltickets.edit')->middleware('permission:edit_alltickets');
    Route::put('/alltickets/{ticket}', [AllTicketsController::class, 'update'])->name('alltickets.update')->middleware('permission:edit_alltickets');
    Route::delete('/alltickets/{ticket}', [AllTicketsController::class, 'destroy'])->name('alltickets.destroy')->middleware('permission:delete_alltickets');

    Route::get('/mytickets', [MyTicketsController::class, 'index'])->name('mytickets.index')->middleware('permission:show_mytickets');
    Route::get('/mytickets/create', [MyTicketsController::class, 'create'])->name('mytickets.create')->middleware('permission:create_mytickets');
    Route::post('/mytickets', [MyTicketsController::class, 'store'])->name('mytickets.store')->middleware('permission:create_mytickets');
    Route::post('/mytickets/bulk-update', [MyTicketsController::class, 'bulkUpdate'])->name('mytickets.bulk-update')->middleware('permission:edit_mytickets');
    Route::post('/mytickets/bulk-delete', [MyTicketsController::class, 'bulkDelete'])->name('mytickets.bulk-delete')->middleware('permission:delete_mytickets');
    Route::get('/mytickets/{ticket}', [MyTicketsController::class, 'show'])->name('mytickets.show')->middleware('permission:show_mytickets');
    Route::get('/mytickets/{ticket}/edit', [MyTicketsController::class, 'edit'])->name('mytickets.edit')->middleware('permission:edit_mytickets');
    Route::put('/mytickets/{ticket}', [MyTicketsController::class, 'update'])->name('mytickets.update')->middleware('permission:edit_mytickets');
    Route::delete('/mytickets/{ticket}', [MyTicketsController::class, 'destroy'])->name('mytickets.destroy')->middleware('permission:delete_mytickets');

    Route::get('/helpdeskteams', [HelpdeskTeamsController::class, 'index'])->name('helpdeskteams.index')->middleware('permission:show_helpdeskteams');
    Route::get('/helpdeskteams/create', [HelpdeskTeamsController::class, 'create'])->name('helpdeskteams.create')->middleware('permission:create_helpdeskteams');
    Route::post('/helpdeskteams', [HelpdeskTeamsController::class, 'store'])->name('helpdeskteams.store')->middleware('permission:create_helpdeskteams');
    Route::get('/helpdeskteams/{team}', [HelpdeskTeamsController::class, 'show'])->name('helpdeskteams.show')->middleware('permission:show_helpdeskteams');
    Route::get('/helpdeskteams/{team}/edit', [HelpdeskTeamsController::class, 'edit'])->name('helpdeskteams.edit')->middleware('permission:edit_helpdeskteams');
    Route::put('/helpdeskteams/{team}', [HelpdeskTeamsController::class, 'update'])->name('helpdeskteams.update')->middleware('permission:edit_helpdeskteams');
    Route::delete('/helpdeskteams/{team}', [HelpdeskTeamsController::class, 'destroy'])->name('helpdeskteams.destroy')->middleware('permission:delete_helpdeskteams');

    Route::get('/users', [UsersController::class, 'index'])->name('users.index')->middleware('permission:show_users');
    Route::get('/users/create', [UsersController::class, 'create'])->name('users.create')->middleware('permission:create_users');
    Route::post('/users', [UsersController::class, 'store'])->name('users.store')->middleware('permission:create_users');
    Route::get('/users/{user}', [UsersController::class, 'show'])->name('users.show')->middleware('permission:show_users');
    Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit')->middleware('permission:edit_users');
    Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update')->middleware('permission:edit_users');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy')->middleware('permission:delete_users');

    Route::get('/customers', [CustomersController::class, 'index'])->name('customers.index')->middleware('permission:show_customers');
    Route::get('/customers/create', [CustomersController::class, 'create'])->name('customers.create')->middleware('permission:create_customers');
    Route::post('/customers', [CustomersController::class, 'store'])->name('customers.store')->middleware('permission:create_customers');
    Route::get('/customers/{customer}', [CustomersController::class, 'show'])->name('customers.show')->middleware('permission:show_customers');
    Route::get('/customers/{customer}/edit', [CustomersController::class, 'edit'])->name('customers.edit')->middleware('permission:edit_customers');
    Route::put('/customers/{customer}', [CustomersController::class, 'update'])->name('customers.update')->middleware('permission:edit_customers');
    Route::delete('/customers/{customer}', [CustomersController::class, 'destroy'])->name('customers.destroy')->middleware('permission:delete_customers');

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index')->middleware('permission:show_employees');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create')->middleware('permission:create_employees');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store')->middleware('permission:create_employees');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show')->middleware('permission:show_employees');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit')->middleware('permission:edit_employees');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update')->middleware('permission:edit_employees');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy')->middleware('permission:delete_employees');

    Route::get('/employees/{employee}/create-account', [EmployeeController::class, 'createAccount'])->name('employees.createAccount')->middleware('permission:create_employees');
    Route::post('/employees/{employee}/store-account', [EmployeeController::class, 'storeAccount'])->name('employees.storeAccount')->middleware('permission:create_employees');

    Route::get('/departments', [DepartmentsController::class, 'index'])->name('departments.index')->middleware('permission:show_departments');
    Route::get('/departments/create', [DepartmentsController::class, 'create'])->name('departments.create')->middleware('permission:create_departments');
    Route::post('/departments', [DepartmentsController::class, 'store'])->name('departments.store')->middleware('permission:create_departments');
    Route::get('/departments/{department}', [DepartmentsController::class, 'show'])->name('departments.show')->middleware('permission:show_departments');
    Route::get('/departments/{department}/edit', [DepartmentsController::class, 'edit'])->name('departments.edit')->middleware('permission:edit_departments');
    Route::put('/departments/{department}', [DepartmentsController::class, 'update'])->name('departments.update')->middleware('permission:edit_departments');
    Route::delete('/departments/{department}', [DepartmentsController::class, 'destroy'])->name('departments.destroy')->middleware('permission:delete_departments');

    Route::get('/positions', [PositionsController::class, 'index'])->name('positions.index')->middleware('permission:show_positions');
    Route::get('/positions/create', [PositionsController::class, 'create'])->name('positions.create')->middleware('permission:create_positions');
    Route::post('/positions', [PositionsController::class, 'store'])->name('positions.store')->middleware('permission:create_positions');
    Route::get('/positions/{position}/edit', [PositionsController::class, 'edit'])->name('positions.edit')->middleware('permission:edit_positions');
    Route::put('/positions/{position}', [PositionsController::class, 'update'])->name('positions.update')->middleware('permission:edit_positions');
    Route::delete('/positions/{position}', [PositionsController::class, 'destroy'])->name('positions.destroy')->middleware('permission:delete_positions');

    Route::get('/tags', [TagsController::class, 'index'])->name('tags.index')->middleware('permission:show_tags');
    Route::get('/tags/create', [TagsController::class, 'create'])->name('tags.create')->middleware('permission:create_tags');
    Route::post('/tags', [TagsController::class, 'store'])->name('tags.store')->middleware('permission:create_tags');
    Route::get('/tags/{tag}', [TagsController::class, 'show'])->name('tags.show')->middleware('permission:show_tags');
    Route::get('/tags/{tag}/edit', [TagsController::class, 'edit'])->name('tags.edit')->middleware('permission:edit_tags');
    Route::put('/tags/{tag}', [TagsController::class, 'update'])->name('tags.update')->middleware('permission:edit_tags');
    Route::delete('/tags/{tag}', [TagsController::class, 'destroy'])->name('tags.destroy')->middleware('permission:delete_tags');

    Route::get('/companies', [CompaniesController::class, 'index'])->name('companies.index')->middleware('permission:show_companies');
    Route::get('/companies/create', [CompaniesController::class, 'create'])->name('companies.create')->middleware('permission:create_companies');
    Route::post('/companies', [CompaniesController::class, 'store'])->name('companies.store')->middleware('permission:create_companies');
    Route::get('/companies/{company}', [CompaniesController::class, 'show'])->name('companies.show')->middleware('permission:show_companies');
    Route::get('/companies/{company}/edit', [CompaniesController::class, 'edit'])->name('companies.edit')->middleware('permission:edit_companies');
    Route::put('/companies/{company}', [CompaniesController::class, 'update'])->name('companies.update')->middleware('permission:edit_companies');
    Route::delete('/companies/{company}', [CompaniesController::class, 'destroy'])->name('companies.destroy')->middleware('permission:delete_companies');

    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:show_roles');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('permission:create_roles');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:create_roles');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show')->middleware('permission:show_roles');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('permission:edit_roles');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('permission:edit_roles');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:delete_roles');

    // Ticket Messages Routes
    Route::get('/tickets/{ticket}/messages', [TicketMessageController::class, 'index'])->name('ticket.messages.index');
    Route::post('/tickets/{ticket}/messages', [TicketMessageController::class, 'store'])->name('ticket.messages.store');
    
    // Ticket Analysis Routes
    Route::get('/ticket-analysis', [TicketAnalysisController::class, 'index'])->name('ticket.analysis')->middleware('permission:view_ticket_analysis_menu');
    Route::get('/ticket-analysis/export', [TicketAnalysisController::class, 'export'])->name('ticket.analysis.export')->middleware('permission:view_ticket_analysis_menu');
    
    // Customer Ratings Routes
    Route::get('/customer-ratings', [CustomerRatingsController::class, 'index'])->name('customer-ratings.index')->middleware('permission:view_customer_ratings_menu');
    Route::get('/customer-ratings/{rating}', [CustomerRatingsController::class, 'show'])->name('customer-ratings.show')->middleware('permission:view_customer_ratings_menu');
    

});

require __DIR__.'/auth.php';
