<?php

use App\Http\Controllers\TicketController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/tickets/{ticketNumber}', [TicketController::class, 'show'])
    ->whereNumber('ticketNumber')
    ->name('tickets.show');

Route::get('/api/tickets/{ticketNumber}/execute', [TicketController::class, 'execute'])
    ->whereNumber('ticketNumber')
    ->name('tickets.execute');

Route::post('/api/tickets/create', [TicketController::class, 'apiCreate'])
    ->name('tickets.api.create');

Route::get('/api/tickets/next-number', [TicketController::class, 'nextNumber'])
    ->name('tickets.next-number');

Route::post('/api/tickets/update-query', [TicketController::class, 'updateQuery'])
    ->name('tickets.update-query');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
