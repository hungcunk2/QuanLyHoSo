<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\ClassRoomController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\SubjectRegistrationController;
use App\Http\Controllers\Admin\CourseOfferingController;
use App\Http\Controllers\Admin\LopController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;

require __DIR__ . '/auth.php';

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/account/change-password', [PasswordController::class, 'edit'])->name('account.password.edit');
    Route::post('/account/change-password', [PasswordController::class, 'update'])->name('account.password.update');
});

// Some templates/packages still reference route('home')
Route::get('/home', function () {
    $user = auth()->user();
    if (! $user) {
        return redirect()->to('/');
    }

    return match ($user->role) {
        'student' => redirect()->route('student.dashboard'),
        'teacher' => redirect()->route('teacher.dashboard'),
        default => redirect()->route('admin.dashboard'),
    };
})->name('home');

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
    Route::post('/students/bulk-delete', [StudentController::class, 'bulkDelete'])->name('students.bulk-delete');
    
    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers');
    Route::get('/teachers/data', [TeacherController::class, 'getData'])->name('teachers.data');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('/teachers/{id}', [TeacherController::class, 'show'])->name('teachers.show');
    Route::post('/teachers/{id}/send-email', [TeacherController::class, 'sendEmail'])->name('teachers.send-email');
    Route::put('/teachers/{id}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::delete('/teachers/{id}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
    Route::post('/teachers/bulk-delete', [TeacherController::class, 'bulkDelete'])->name('teachers.bulk-delete');
    
    Route::get('/classes', [ClassRoomController::class, 'index'])->name('classes');
    Route::get('/classes/data', [ClassRoomController::class, 'getData'])->name('classes.data');
    Route::post('/classes', [ClassRoomController::class, 'store'])->name('classes.store');
    Route::get('/classes/{id}', [ClassRoomController::class, 'show'])->name('classes.show');
    Route::put('/classes/{id}', [ClassRoomController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{id}', [ClassRoomController::class, 'destroy'])->name('classes.destroy');
    Route::post('/classes/bulk-delete', [ClassRoomController::class, 'bulkDelete'])->name('classes.bulk-delete');
    
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects');
    Route::get('/subjects/data', [SubjectController::class, 'getData'])->name('subjects.data');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::get('/subjects/{id}', [SubjectController::class, 'show'])->name('subjects.show');
    Route::put('/subjects/{id}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

    Route::get('/subject-registrations', [SubjectRegistrationController::class, 'index'])->name('subject-registrations');
    Route::get('/subject-registrations/data', [SubjectRegistrationController::class, 'getData'])->name('subject-registrations.data');
    Route::get('/lops', [LopController::class, 'index'])->name('lops');
    Route::get('/lops/data', [LopController::class, 'getData'])->name('lops.data');
    Route::post('/lops', [LopController::class, 'store'])->name('lops.store');
    Route::post('/lops/bulk-delete', [LopController::class, 'bulkDelete'])->name('lops.bulk-delete');
    Route::get('/lops/{id}', [LopController::class, 'show'])->name('lops.show');
    Route::put('/lops/{id}', [LopController::class, 'update'])->name('lops.update');
    Route::delete('/lops/{id}', [LopController::class, 'destroy'])->name('lops.destroy');
    Route::get('/course-offerings/{id}', [CourseOfferingController::class, 'show'])->name('course-offerings.show');
    Route::post('/course-offerings', [CourseOfferingController::class, 'store'])->name('course-offerings.store');
    Route::put('/course-offerings/{id}', [CourseOfferingController::class, 'update'])->name('course-offerings.update');
    Route::delete('/course-offerings/{id}', [CourseOfferingController::class, 'destroy'])->name('course-offerings.destroy');
});

Route::prefix('student')->name('student.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile/edit', [StudentDashboardController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [StudentDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::get('/schedule', [StudentDashboardController::class, 'schedule'])->name('schedule');
    Route::get('/results', [StudentDashboardController::class, 'results'])->name('results');
    Route::get('/registration', [StudentDashboardController::class, 'registration'])->name('registration');
    Route::post('/registration/{courseOfferingId}/register', [StudentDashboardController::class, 'registerOffering'])->name('registration.register');
    Route::post('/registration/{courseOfferingId}/cancel', [StudentDashboardController::class, 'cancelOffering'])->name('registration.cancel');
    Route::get('/notifications', [StudentDashboardController::class, 'notifications'])->name('notifications');
});

Route::prefix('teacher')->name('teacher.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('/schedule', [TeacherDashboardController::class, 'schedule'])->name('schedule');
    Route::get('/grading', [TeacherDashboardController::class, 'grading'])->name('grading');
    Route::get('/grading/{courseOffering}', [TeacherDashboardController::class, 'gradingClass'])->name('grading.class');
    Route::post('/grading/{courseOffering}', [TeacherDashboardController::class, 'saveGrades'])->name('grading.save');
    Route::get('/grading/{courseOffering}/export.xlsx', [TeacherDashboardController::class, 'exportGradesXlsx'])->name('grading.export.xlsx');
    Route::get('/my-classes', [TeacherDashboardController::class, 'myClasses'])->name('my-classes');
    Route::get('/my-classes/{courseOffering}/students', [TeacherDashboardController::class, 'offeringRoster'])->name('my-classes.roster');
});