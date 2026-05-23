<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\ClassRoomController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\SubjectRegistrationController;
use App\Http\Controllers\Admin\CourseOfferingController;
use App\Http\Controllers\Admin\LopController;
use App\Http\Controllers\Admin\CurriculumTermController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Student\CurriculumController as StudentCurriculumController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\AnnouncementManageController as TeacherAnnouncementManageController;
use App\Http\Controllers\Student\ChatController as StudentChatController;
use App\Http\Controllers\Teacher\ChatController as TeacherChatController;

require __DIR__ . '/auth.php';

Route::get('/', function () {
    $user = auth()->user();
    if ($user) {
        return match ($user->role) {
            'student' => redirect()->route('student.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            default => redirect()->route('admin.dashboard'),
        };
    }
    return view('welcome');
});

Route::get('/thong-bao', [AnnouncementController::class, 'index'])->name('announcements.index');
Route::get('/thong-bao/{slug}', [AnnouncementController::class, 'show'])->name('announcements.show');

Route::middleware('auth')->group(function () {
    Route::get('/account/change-password', [PasswordController::class, 'edit'])->name('account.password.edit');
    Route::post('/account/change-password', [PasswordController::class, 'update'])->name('account.password.update');
    Route::post('/ai/chat', [\App\Http\Controllers\AiChatController::class, 'store'])->name('ai.chat');
});

// Convenience GET logout (avoid redirect loops with "intended=/logout")
Route::get('/logout', function () {
    if (\Illuminate\Support\Facades\Auth::check()) {
        \Illuminate\Support\Facades\Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    // Always send users back to home (or login via your auth setup)
    return redirect()->to('/');
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

    Route::get('/reports', [\App\Http\Controllers\Admin\ReportsController::class, 'index'])->name('reports.index');
    Route::get('/notifications', [AdminAnnouncementController::class, 'index'])->name('notifications.index');
    Route::get('/curriculum-terms', [CurriculumTermController::class, 'index'])->name('curriculum-terms.index');
    Route::get('/curriculum-terms/create', [CurriculumTermController::class, 'create'])->name('curriculum-terms.create');
    Route::post('/curriculum-terms', [CurriculumTermController::class, 'store'])->name('curriculum-terms.store');
    Route::get('/curriculum-terms/{curriculumTerm}/edit', [CurriculumTermController::class, 'edit'])->name('curriculum-terms.edit');
    Route::put('/curriculum-terms/{curriculumTerm}', [CurriculumTermController::class, 'update'])->name('curriculum-terms.update');
    Route::delete('/curriculum-terms/{curriculumTerm}', [CurriculumTermController::class, 'destroy'])->name('curriculum-terms.destroy');
    Route::get('/notifications/create', [AdminAnnouncementController::class, 'create'])->name('notifications.create');
    Route::post('/notifications', [AdminAnnouncementController::class, 'store'])->name('notifications.store');
    Route::get('/notifications/{announcement}/edit', [AdminAnnouncementController::class, 'edit'])->name('notifications.edit');
    Route::put('/notifications/{announcement}', [AdminAnnouncementController::class, 'update'])->name('notifications.update');
    Route::delete('/notifications/{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('notifications.destroy');
    
    Route::get('/students', [StudentController::class, 'index'])->name('students');
    Route::get('/students/data', [StudentController::class, 'getData'])->name('students.data');
    Route::get('/students/next-mssv', [StudentController::class, 'nextMssv'])->name('students.next-mssv');
    Route::get('/students/next-ma-ho-so', [StudentController::class, 'nextMaHoSo'])->name('students.next-ma-ho-so');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{id}', [StudentController::class, 'show'])->name('students.show');
    Route::post('/students/{id}/reset-password', [StudentController::class, 'resetPassword'])->name('students.reset-password');
    Route::put('/students/{id}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::post('/students/bulk-delete', [StudentController::class, 'bulkDelete'])->name('students.bulk-delete');
    
    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers');
    Route::get('/teachers/data', [TeacherController::class, 'getData'])->name('teachers.data');
    Route::get('/teachers/next-msgv', [TeacherController::class, 'nextMsgv'])->name('teachers.next-msgv');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('/teachers/{id}', [TeacherController::class, 'show'])->name('teachers.show');
    Route::post('/teachers/{id}/reset-password', [TeacherController::class, 'resetPassword'])->name('teachers.reset-password');
    Route::put('/teachers/{id}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::delete('/teachers/{id}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
    Route::post('/teachers/bulk-delete', [TeacherController::class, 'bulkDelete'])->name('teachers.bulk-delete');
    
    Route::get('/classes', [ClassRoomController::class, 'index'])->name('classes');
    Route::get('/classes/data', [ClassRoomController::class, 'getData'])->name('classes.data');
    Route::get('/classes/next-ma-lop', [ClassRoomController::class, 'nextMaLop'])->name('classes.next-ma-lop');
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
    Route::get('/subject-registrations/course-offerings/{id}/sessions', [SubjectRegistrationController::class, 'offeringSessions'])->name('subject-registrations.offering-sessions');
    Route::post('/subject-registrations/course-offerings/{id}/reschedule-session', [SubjectRegistrationController::class, 'rescheduleSession'])->name('subject-registrations.reschedule-session');
    Route::post('/subject-registrations/course-offerings/{id}/pause-session', [SubjectRegistrationController::class, 'pauseSession'])->name('subject-registrations.pause-session');
    Route::post('/subject-registrations/course-offerings/{id}/unpause-session', [SubjectRegistrationController::class, 'unpauseSession'])->name('subject-registrations.unpause-session');
    Route::get('/lops', [LopController::class, 'index'])->name('lops');
    Route::get('/lops/data', [LopController::class, 'getData'])->name('lops.data');
    Route::get('/lops/next-ma-lop', [LopController::class, 'nextMaLop'])->name('lops.next-ma-lop');
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
    Route::get('/profile', [StudentDashboardController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [StudentDashboardController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [StudentDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::get('/schedule', [StudentDashboardController::class, 'schedule'])->name('schedule');
    Route::get('/schedule.pdf', [StudentDashboardController::class, 'schedulePdf'])->name('schedule.pdf');
    Route::get('/results', [StudentDashboardController::class, 'results'])->name('results');
    Route::get('/results.pdf', [StudentDashboardController::class, 'resultsPdf'])->name('results.pdf');
    Route::get('/registration', [StudentDashboardController::class, 'registration'])->name('registration');
    Route::get('/curriculum', [StudentCurriculumController::class, 'index'])->name('curriculum');
    Route::post('/registration/{courseOfferingId}/register', [StudentDashboardController::class, 'registerOffering'])->name('registration.register');
    Route::post('/registration/{courseOfferingId}/cancel', [StudentDashboardController::class, 'cancelOffering'])->name('registration.cancel');
    Route::get('/notifications', [StudentDashboardController::class, 'notifications'])->name('notifications');
    Route::get('/chat', [StudentChatController::class, 'index'])->name('chat');
    Route::post('/chat/start', [StudentChatController::class, 'startConversation'])->name('chat.start');
    Route::get('/chat/conversations/{conversation}/messages', [StudentChatController::class, 'fetchMessages'])->name('chat.messages');
    Route::post('/chat/conversations/{conversation}/messages', [StudentChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/messages/{message}/attachment', [StudentChatController::class, 'showAttachment'])->name('chat.attachment');
});

Route::prefix('teacher')->name('teacher.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [TeacherDashboardController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [TeacherDashboardController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [TeacherDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::get('/schedule', [TeacherDashboardController::class, 'schedule'])->name('schedule');
    Route::get('/schedule.pdf', [TeacherDashboardController::class, 'schedulePdf'])->name('schedule.pdf');
    Route::get('/grading', [TeacherDashboardController::class, 'grading'])->name('grading');
    Route::get('/grading/{courseOffering}', [TeacherDashboardController::class, 'gradingClass'])->name('grading.class');
    Route::post('/grading/{courseOffering}', [TeacherDashboardController::class, 'saveGrades'])->name('grading.save');
    Route::post('/grading/{courseOffering}/finalize', [TeacherDashboardController::class, 'finalizeGrades'])->name('grading.finalize');
    Route::get('/grading/{courseOffering}/export.xlsx', [TeacherDashboardController::class, 'exportGradesXlsx'])->name('grading.export.xlsx');
    Route::get('/my-classes', [TeacherDashboardController::class, 'myClasses'])->name('my-classes');
    Route::get('/my-classes/{courseOffering}/students', [TeacherDashboardController::class, 'offeringRoster'])->name('my-classes.roster');
    Route::get('/notifications', [TeacherDashboardController::class, 'notifications'])->name('notifications');

    // Teacher manages (send) announcements
    Route::get('/notifications/manage', [TeacherAnnouncementManageController::class, 'index'])->name('notifications.manage.index');
    Route::get('/notifications/manage/create', [TeacherAnnouncementManageController::class, 'create'])->name('notifications.manage.create');
    Route::post('/notifications/manage', [TeacherAnnouncementManageController::class, 'store'])->name('notifications.manage.store');
    Route::get('/notifications/manage/{announcement}/edit', [TeacherAnnouncementManageController::class, 'edit'])->name('notifications.manage.edit');
    Route::put('/notifications/manage/{announcement}', [TeacherAnnouncementManageController::class, 'update'])->name('notifications.manage.update');
    Route::delete('/notifications/manage/{announcement}', [TeacherAnnouncementManageController::class, 'destroy'])->name('notifications.manage.destroy');

    Route::get('/chat', [TeacherChatController::class, 'index'])->name('chat');
    Route::post('/chat/start', [TeacherChatController::class, 'startConversation'])->name('chat.start');
    Route::get('/chat/conversations/{conversation}/messages', [TeacherChatController::class, 'fetchMessages'])->name('chat.messages');
    Route::post('/chat/conversations/{conversation}/messages', [TeacherChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/messages/{message}/attachment', [TeacherChatController::class, 'showAttachment'])->name('chat.attachment');
});