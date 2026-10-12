<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
});
Route::middleware(['auth', 'active.user'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/change-password', [AuthController::class, 'passwordForm'])->name('password.edit');
    Route::post('/change-password', [AuthController::class, 'password'])->name('password.update');
});

Route::middleware(['auth', 'active.user', 'password.changed'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');

    Route::get('/owner/invoices/index', [InvoiceController::class, 'index'])->middleware('can:invoices.view')->name('invoices.index');
    Route::get('/owner/invoices/add', [InvoiceController::class, 'create'])->middleware('can:invoices.create')->name('invoices.create');
    Route::post('/owner/invoices', [InvoiceController::class, 'store'])->middleware('can:invoices.create')->name('invoices.store');
    Route::get('/owner/invoices/view/{invoice}', [InvoiceController::class, 'show'])->middleware('can:view,invoice')->name('invoices.show');
    Route::get('/owner/invoices/edit/{invoice}', [InvoiceController::class, 'edit'])->middleware('can:update,invoice')->name('invoices.edit');
    Route::put('/owner/invoices/{invoice}', [InvoiceController::class, 'update'])->middleware('can:update,invoice')->name('invoices.update');
    Route::delete('/owner/invoices/{invoice}', [InvoiceController::class, 'destroy'])->middleware('can:delete,invoice')->name('invoices.destroy');
    Route::post('/owner/invoices/{invoice}/payments', [InvoiceController::class, 'payment'])->middleware('can:pay,invoice')->name('invoices.payments.store');

    Route::get('/v2/owner/entity/client/list', [ClientController::class, 'index'])->middleware('can:clients.view')->name('clients.index');
    Route::get('/owner/clients/add', [ClientController::class, 'create'])->middleware('can:clients.create')->name('clients.create');
    Route::post('/owner/clients', [ClientController::class, 'store'])->middleware('can:clients.create')->name('clients.store');
    Route::get('/owner/clients/view/{client}', [ClientController::class, 'show'])->middleware('can:view,client')->name('clients.show');
    Route::get('/owner/clients/add/{client}', [ClientController::class, 'edit'])->middleware('can:update,client')->name('clients.edit');
    Route::put('/owner/clients/{client}', [ClientController::class, 'update'])->middleware('can:update,client')->name('clients.update');
    Route::delete('/owner/clients/{client}', [ClientController::class, 'destroy'])->middleware('can:delete,client')->name('clients.destroy');

    Route::get('/v2/owner/entity/staff/list', [StaffController::class, 'index'])->middleware('can:staff.view')->name('staff.index');
    Route::get('/v2/owner/staff/create/user', [StaffController::class, 'create'])->middleware('can:staff.create')->name('staff.create');
    Route::post('/v2/owner/staff', [StaffController::class, 'store'])->middleware('can:staff.create')->name('staff.store');
    Route::get('/v2/owner/staff/{staff}/edit', [StaffController::class, 'edit'])->middleware('can:update,staff')->name('staff.edit');
    Route::put('/v2/owner/staff/{staff}', [StaffController::class, 'update'])->middleware('can:update,staff')->name('staff.update');
    Route::delete('/v2/owner/staff/{staff}', [StaffController::class, 'destroy'])->middleware('can:delete,staff')->name('staff.destroy');

    Route::get('/owner/settings/roles', [RoleController::class, 'index'])->middleware('can:roles.view')->name('settings.roles');
    Route::post('/owner/settings/roles', [RoleController::class, 'store'])->middleware('can:roles.manage')->name('roles.store');
    Route::put('/owner/settings/roles/{role}', [RoleController::class, 'update'])->middleware('can:update,role')->name('roles.update');
    Route::delete('/owner/settings/roles/{role}', [RoleController::class, 'destroy'])->middleware('can:delete,role')->name('roles.destroy');
    Route::get('/owner/settings/activity-log', [ActivityController::class, 'index'])->middleware('can:activity.view')->name('settings.activity');
});
