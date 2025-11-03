<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;


Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

//Route::get('tasks', [TaskController::class, 'index'])->name('tasks');


Route::middleware(['auth'])->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('home');
    Route::get('/tasks/create', [TaskController::class, 'new'])->name('tasks-create');
    Route::post('/tasks', [TaskController::class, 'create'])->name('tasks-store');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks-show');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks-edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks-update');
    Route::post('/tasks/{task}/comments', [CommentController::class, 'create'])->name('comments.create');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'delete'])->name('comments.delete');
    Route::delete('/tasks/{task}', [TaskController::class, 'delete'])->name('tasks-delete');
});




require __DIR__.'/settings.php';
