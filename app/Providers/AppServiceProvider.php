<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Teacher;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer(
            [
                'layouts.admin',
                'layouts.teacher',
                'layouts.student',
            ],
            function ($view) {
                $user = Auth::user();
                $displayName = $user?->email ?? '';

                if ($user) {
                    if ($user->role === 'student') {
                        $student = Student::query()
                            ->where('email', $user->email)
                            ->orWhere('mssv', $user->username)
                            ->first();
                        $displayName = $student?->ho_ten ?: ($user->username ?: $user->email);
                    } elseif ($user->role === 'teacher') {
                        $teacher = Teacher::query()
                            ->where('email', $user->email)
                            ->orWhere('msgv', $user->username)
                            ->first();
                        $displayName = $teacher?->ho_ten ?: ($user->username ?: $user->email);
                    } elseif ($user->role === 'admin') {
                        $displayName = $user->username ?: 'Admin';
                    } else {
                        $displayName = $user->username ?: ($user->email ?: '');
                    }
                }

                $view->with('authDisplayName', $displayName);
            }
        );
    }
}

