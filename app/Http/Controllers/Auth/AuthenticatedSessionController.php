<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('auth.failed'),
                    'errors' => [
                        'email' => [__('auth.failed')]
                    ]
                ], 422);
            }
            
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        // Redirect based on user role
        $user = Auth::user();
        $redirectRoute = 'admin.dashboard'; // default
        
        if ($user && $user->role) {
            switch ($user->role) {
                case 'admin':
                    $redirectRoute = 'admin.dashboard';
                    break;
                case 'student':
                    $redirectRoute = 'student.dashboard';
                    break;
                case 'teacher':
                    $redirectRoute = 'teacher.dashboard';
                    break;
                default:
                    $redirectRoute = 'admin.dashboard';
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đăng nhập thành công',
                'redirect' => route($redirectRoute, absolute: false)
            ]);
        }

        return redirect()->intended(route($redirectRoute, absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
