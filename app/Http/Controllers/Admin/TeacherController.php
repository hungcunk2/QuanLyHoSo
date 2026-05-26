<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TeacherWelcomeMail;
use App\Mail\TeacherPasswordChangedMail;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class TeacherController extends Controller
{
    public function index()
    {
        return view('admin.teachers');
    }

    public function getData(Request $request)
    {
        $query = Teacher::select('id', 'msgv', 'ho_ten', 'chuyen_mon', 'sdt', 'email', 'ngay_sinh', 'created_at', 'updated_at');

        $filterName = $request->input('filter_ho_ten');
        if (is_string($filterName) && trim($filterName) !== '') {
            $needle = mb_strtolower(preg_replace('/\s+/u', ' ', trim($filterName)), 'UTF-8');
            $pattern = '%'.addcslashes($needle, '%_\\').'%';
            $query->whereRaw('LOWER(ho_ten) LIKE ?', [$pattern]);
        }

        $filterChuyenMon = $request->input('filter_chuyen_mon');
        if (is_string($filterChuyenMon) && $filterChuyenMon !== '' && in_array($filterChuyenMon, Teacher::chuyenMonOptions(), true)) {
            $query->where('chuyen_mon', $filterChuyenMon);
        }

        $query->orderByDesc('created_at');

        return DataTables::of($query)
            ->orderColumn('created_at', 'teachers.created_at $1')
            ->skipAutoFilter()
            ->addColumn('check', function ($teacher) {
                return '<input type="checkbox" class="form-check-input row-checkbox" name="selected_ids[]" value="' . $teacher->id . '">';
            })
            ->addColumn('action', function ($teacher) {
                return '
                    <button class="btn btn-sm btn-primary me-1 edit-btn" data-id="' . $teacher->id . '" title="Sửa">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-warning me-1 reset-password-btn" data-id="' . $teacher->id . '" data-email="' . ($teacher->email ?? '') . '" data-msgv="' . ($teacher->msgv ?? '') . '" title="Đổi mật khẩu">
                        <i class="fas fa-key"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="' . $teacher->id . '" title="Xóa">
                        <i class="fas fa-trash"></i>
                    </button>
                ';
            })
            ->editColumn('ngay_sinh', function ($teacher) {
                return $teacher->ngay_sinh ? $teacher->ngay_sinh->format('d/m/Y') : '';
            })
            ->rawColumns(['check', 'action'])
            ->make(true);
    }

    public function nextMsgv()
    {
        return response()->json([
            'success' => true,
            'next_msgv' => Teacher::generateNextMsgv('GV', 2),
        ]);
    }

    public function store(Request $request)
    {
        // Normalize input to avoid " MSGV " / "email " bypasses
        $request->merge([
            'msgv' => is_string($request->msgv) ? trim($request->msgv) : $request->msgv,
            'email' => is_string($request->email) ? trim(mb_strtolower($request->email)) : $request->email,
        ]);

        $request->validate([
            // Ignore soft-deleted rows when checking duplicates
            'msgv' => 'nullable|string|max:50|unique:teachers,msgv,NULL,id,deleted_at,NULL|unique:users,username,NULL,id,deleted_at,NULL',
            'email' => 'required|email|max:255|unique:teachers,email,NULL,id,deleted_at,NULL|unique:users,email,NULL,id,deleted_at,NULL',
            'ho_ten' => 'required|string|max:255',
            'chuyen_mon' => ['required', 'string', Rule::in(Teacher::chuyenMonOptions())],
        ], [
            'msgv.unique' => 'Mã số giáo viên đã tồn tại trong hệ thống.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng bởi giáo viên khác.',
            'ho_ten.required' => 'Vui lòng nhập họ và tên.',
            'chuyen_mon.required' => 'Vui lòng chọn chuyên môn.',
            'chuyen_mon.in' => 'Chuyên môn không hợp lệ.',
        ]);

        // Tạo mật khẩu 6 số ngẫu nhiên
        $password = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        try {
            [$user, $teacher] = DB::transaction(function () use ($request, $password) {
                $msgv = $request->msgv;
                if (!is_string($msgv) || trim($msgv) === '') {
                    // Generate next teacher code safely inside the transaction
                    DB::table('teachers')
                        ->select('msgv')
                        ->where('msgv', 'like', 'GV%')
                        ->orderByRaw("CAST(SUBSTRING(msgv, 3) AS UNSIGNED) DESC")
                        ->lockForUpdate()
                        ->first();

                    $msgv = Teacher::generateNextMsgv('GV', 2);
                }

                // Create teacher + user atomically (no half-created accounts)
                $user = User::create([
                    'username' => $msgv,
                    'email' => $request->email,
                    'password' => $password, // hash via cast 'hashed'
                    'role' => 'teacher',
                    'status' => true,
                ]);

                $teacher = Teacher::create([
                    'msgv' => $msgv,
                    'email' => $request->email,
                    'ho_ten' => $request->ho_ten,
                    'chuyen_mon' => $request->chuyen_mon,
                ]);

                return [$user, $teacher];
            });
        } catch (QueryException $e) {
            // In case DB unique constraints trigger, return a clear message
            return response()->json([
                'success' => false,
                'message' => 'Mã số giáo viên hoặc email đã tồn tại trong hệ thống.',
            ], 422);
        }

        // Gửi email chào mừng với thông tin đăng nhập
        try {
            if ($teacher->email) {
                Mail::to($teacher->email)->send(new TeacherWelcomeMail($teacher, $password));
            }
        } catch (\Exception $e) {
            // Log lỗi nhưng không làm gián đoạn quá trình tạo giáo viên
            \Log::error('Lỗi gửi email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Tạo giáo viên mới thành công! Tài khoản đã được tạo và thông tin đăng nhập đã được gửi qua email.',
            'data' => $teacher
        ]);
    }

    public function resetPassword(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);

        if (! $teacher->email) {
            return response()->json([
                'success' => false,
                'message' => 'Giáo viên chưa có email nên không thể gửi thông báo đổi mật khẩu.'
            ], 400);
        }

        $request->validate([
            'password' => ['required', 'string', 'min:6', 'max:255'],
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
        ]);

        $newPassword = (string) $request->input('password');

        try {
            DB::transaction(function () use ($teacher, $newPassword) {
                // Tài khoản seed/demo thường có email trùng nhưng chưa gán username = MSGV → tránh tạo user trùng email
                $user = User::where('username', $teacher->msgv)->first();
                if (! $user && $teacher->email) {
                    $user = User::where('email', $teacher->email)->first();
                }

                if ($user) {
                    $user->forceFill([
                        'username' => $teacher->msgv ?: $user->username,
                        'email' => $teacher->email ?: $user->email,
                        'password' => $newPassword, // hash via cast 'hashed'
                        'role' => 'teacher',
                        'status' => true,
                    ])->save();
                } else {
                    User::create([
                        'username' => $teacher->msgv,
                        'email' => $teacher->email,
                        'password' => $newPassword,
                        'role' => 'teacher',
                        'status' => true,
                    ]);
                }
            });
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể đổi mật khẩu. Vui lòng thử lại.',
            ], 500);
        }

        try {
            Mail::to($teacher->email)->send(new TeacherPasswordChangedMail($teacher, $newPassword));
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => true,
                'message' => 'Đã đổi mật khẩu thành công. Không gửi được email thông báo — kiểm tra cấu hình MAIL_* trong .env.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công và đã gửi email thông báo cho giáo viên.',
        ]);
    }

    public function show($id)
    {
        $teacher = Teacher::findOrFail($id);
        return response()->json($teacher);
    }

    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);

        $request->merge([
            'chuyen_mon' => $request->input('chuyen_mon') === '' ? null : $request->input('chuyen_mon'),
        ]);

        $chuyenMonAllowed = array_values(array_unique(array_merge(
            Teacher::chuyenMonOptions(),
            array_filter([$teacher->chuyen_mon])
        )));

        $request->validate([
            'msgv' => 'required|string|max:50|unique:teachers,msgv,' . $id,
            'ho_ten' => 'required|string|max:255',
            'chuyen_mon' => ['nullable', 'string', Rule::in($chuyenMonAllowed)],
            'sdt' => 'nullable|string|max:20',
            'dia_chi' => 'nullable|string',
            'email' => 'nullable|email|max:255|unique:teachers,email,' . $id,
            'ngay_sinh' => 'nullable|date',
        ], [
            'chuyen_mon.in' => 'Chuyên môn không hợp lệ.',
        ]);

        $teacher->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin giáo viên thành công!'
        ]);
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        // Also delete linked user (username = msgv)
        DB::transaction(function () use ($teacher) {
            User::where('username', $teacher->msgv)->delete();
            $teacher->forceDelete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Xóa giáo viên thành công!'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('selected_ids', []);
        if (!is_array($ids) || count($ids) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng chọn ít nhất một giáo viên để xóa.'
            ], 422);
        }

        $teachers = Teacher::whereIn('id', $ids)->get(['id', 'msgv']);

        DB::transaction(function () use ($teachers) {
            $usernames = $teachers->pluck('msgv')->filter()->values()->all();
            if (count($usernames)) {
                User::whereIn('username', $usernames)->delete();
            }
            Teacher::whereIn('id', $teachers->pluck('id')->all())->forceDelete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa ' . $teachers->count() . ' giáo viên.'
        ]);
    }
}
