<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordSixDigitMail;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PasswordResetLinkController extends Controller
{
    /**
     * Redirect to landing page and open forgot modal.
     */
    public function create(): RedirectResponse
    {
        return redirect()->to(url('/').'?forgot=1');
    }

    /**
     * Gửi mật khẩu 6 chữ số qua email và cập nhật mật khẩu cho user.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
        ]);

        $emailInput = (string) $request->input('email');
        $email = trim(mb_strtolower($emailInput));

        $user = User::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();

        // Nếu email nằm trong hồ sơ học sinh/giáo viên, tìm tài khoản users tương ứng.
        if (! $user) {
            $student = Student::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();
            if ($student) {
                $user = User::where('username', $student->mssv)->orWhereRaw('LOWER(TRIM(email)) = ?', [$email])->first();

                // Nếu có hồ sơ HS nhưng chưa có user (dữ liệu import), tự tạo user để reset được.
                if (! $user && $student->mssv) {
                    $user = DB::transaction(function () use ($student, $email) {
                        return User::create([
                            'username' => $student->mssv,
                            'email' => $email,
                            'password' => Hash::make('000000'),
                            'role' => 'student',
                            'status' => true,
                        ]);
                    });
                }
            }
        }
        if (! $user) {
            $teacher = Teacher::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();
            if ($teacher) {
                $user = User::where('username', $teacher->msgv)->orWhereRaw('LOWER(TRIM(email)) = ?', [$email])->first();

                if (! $user && $teacher->msgv) {
                    $user = DB::transaction(function () use ($teacher, $email) {
                        return User::create([
                            'username' => $teacher->msgv,
                            'email' => $email,
                            'password' => Hash::make('000000'),
                            'role' => 'teacher',
                            'status' => true,
                        ]);
                    });
                }
            }
        }

        if (! $user) {
            $msg = 'Email không tồn tại trong hệ thống.';
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $msg,
                    'errors' => ['email' => [$msg]],
                ], 422);
            }
            return back()->withErrors(['email' => $msg])->withInput($request->only('email'));
        }

        $newPassword = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->forceFill(['password' => Hash::make($newPassword)])->save();

        try {
            Mail::to($email)->send(new ForgotPasswordSixDigitMail($user, $newPassword));
        } catch (Throwable $e) {
            report($e);
            $msg = 'Không thể gửi email. Vui lòng kiểm tra cấu hình mail (MAIL_*) và thử lại.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 500);
            }
            return back()->withErrors(['email' => $msg])->withInput($request->only('email'));
        }

        $message = 'Mật khẩu mới đã được gửi về email.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('status', $message);
    }
}
