<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\ClassRoomController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;

require __DIR__ . '/auth.php';

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    
    Route::get('/students', [StudentController::class, 'index'])->name('students');
    Route::get('/students/data', [StudentController::class, 'getData'])->name('students.data');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{id}', [StudentController::class, 'show'])->name('students.show');
    Route::post('/students/{id}/send-email', [StudentController::class, 'sendEmail'])->name('students.send-email');
    Route::put('/students/{id}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
    
    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers');
    Route::get('/teachers/data', [TeacherController::class, 'getData'])->name('teachers.data');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('/teachers/{id}', [TeacherController::class, 'show'])->name('teachers.show');
    Route::post('/teachers/{id}/send-email', [TeacherController::class, 'sendEmail'])->name('teachers.send-email');
    Route::put('/teachers/{id}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::delete('/teachers/{id}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
    
    Route::get('/classes', [ClassRoomController::class, 'index'])->name('classes');
    Route::get('/classes/data', [ClassRoomController::class, 'getData'])->name('classes.data');
    Route::post('/classes', [ClassRoomController::class, 'store'])->name('classes.store');
    Route::get('/classes/{id}', [ClassRoomController::class, 'show'])->name('classes.show');
    Route::put('/classes/{id}', [ClassRoomController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{id}', [ClassRoomController::class, 'destroy'])->name('classes.destroy');
    
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects');
    Route::get('/subjects/data', [SubjectController::class, 'getData'])->name('subjects.data');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::get('/subjects/{id}', [SubjectController::class, 'show'])->name('subjects.show');
    Route::put('/subjects/{id}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
});

Route::prefix('student')->name('student.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
});

Route::prefix('teacher')->name('teacher.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
});