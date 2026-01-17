<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\ClassRoomController;
use App\Http\Controllers\Admin\SubjectController;

require __DIR__ . '/auth.php';

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    
    Route::get('/students', [StudentController::class, 'index'])->name('students');
    Route::get('/students/data', [StudentController::class, 'getData'])->name('students.data');
    Route::get('/students/{id}', [StudentController::class, 'show'])->name('students.show');
    Route::put('/students/{id}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
    
    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers');
    Route::get('/teachers/data', [TeacherController::class, 'getData'])->name('teachers.data');
    Route::get('/teachers/{id}', [TeacherController::class, 'show'])->name('teachers.show');
    Route::put('/teachers/{id}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::delete('/teachers/{id}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
    
    Route::get('/classes', [ClassRoomController::class, 'index'])->name('classes');
    Route::get('/classes/data', [ClassRoomController::class, 'getData'])->name('classes.data');
    Route::get('/classes/{id}', [ClassRoomController::class, 'show'])->name('classes.show');
    Route::put('/classes/{id}', [ClassRoomController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{id}', [ClassRoomController::class, 'destroy'])->name('classes.destroy');
    
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects');
    Route::get('/subjects/data', [SubjectController::class, 'getData'])->name('subjects.data');
    Route::get('/subjects/{id}', [SubjectController::class, 'show'])->name('subjects.show');
    Route::put('/subjects/{id}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
});
