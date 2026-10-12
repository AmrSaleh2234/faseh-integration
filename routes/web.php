<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'invoices.create');
Route::view('/owner/invoices/add', 'invoices.create')->name('invoices.create');
Route::view('/owner/invoices/index', 'invoices.index')->name('invoices.index');
Route::view('/owner/invoices/view/159', 'invoices.show')->name('invoices.show');
Route::view('/owner/invoices/edit/159', 'invoices.edit')->name('invoices.edit');

Route::view('/v2/owner/entity/client/list', 'clients.index')->name('clients.index');
Route::view('/owner/clients/add', 'clients.form')->name('clients.create');
Route::view('/owner/clients/add/48', 'clients.form')->name('clients.edit');
Route::view('/owner/clients/view/48', 'clients.show')->name('clients.show');

Route::view('/v2/owner/entity/staff/list', 'staff.index')->name('staff.index');
Route::view('/v2/owner/staff/create/user', 'staff.form')->name('staff.create');
Route::view('/v2/owner/staff/{staff}/edit', 'staff.form')->name('staff.edit');
