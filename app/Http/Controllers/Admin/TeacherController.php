<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TeacherWelcomeMail;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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

        return DataTables::of($query)
            ->addColumn('check', function ($teacher) {
                return '<input type="checkbox" class="form-check-input row-checkbox" name="selected_ids[]" value="' . $teacher->id . '">';
            })
            ->addColumn('action', function ($teacher) {
                return '
                    <button class="btn btn-sm btn-primary me-1 edit-btn" data-id="' . $teacher->id . '" title="Sửa">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-info me-1 send-email-btn" data-id="' . $teacher->id . '" data-email="' . ($teacher->email ?? '') . '" title="Gửi email">
                        <i class="fas fa-envelope"></i>
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

    public function store(Request $request)
    {
        // Normalize input to avoid " MSGV " / "email " bypasses
        $request->merge([
            'msgv' => is_string($request->msgv) ? trim($request->msgv) : $request->msgv,
            'email' => is_string($request->email) ? trim(mb_strtolower($request->email)) : $request->email,
        ]);

        $request->validate([
            // Ignore soft-deleted rows when checking duplicates
            'msgv' => 'required|string|max:50|unique:teachers,msgv,NULL,id,deleted_at,NULL|unique:users,username,NULL,id,deleted_at,NULL',
            'email' => 'required|email|max:255|unique:teachers,email,NULL,id,deleted_at,NULL|unique:users,email,NULL,id,deleted_at,NULL',
            'ho_ten' => 'required|string|max:255',
            'chuyen_mon' => 'required|string|max:255',
        ], [
            'msgv.required' => 'Vui lòng nhập mã số giáo viên.',
            'msgv.unique' => 'Mã số giáo viên đã tồn tại trong hệ thống.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng bởi giáo viên khác.',
            'ho_ten.required' => 'Vui lòng nhập họ và tên.',
            'chuyen_mon.required' => 'Vui lòng nhập chuyên môn.',
        ]);

        // Tạo mật khẩu 6 số ngẫu nhiên
        $password = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        try {
            [$user, $teacher] = DB::transaction(function () use ($request, $password) {
                // Create teacher + user atomically (no half-created accounts)
                $user = User::create([
                    'username' => $request->msgv,
                    'email' => $request->email,
                    'password' => $password, // hash via cast 'hashed'
                    'role' => 'teacher',
                    'status' => true,
                ]);

                $teacher = Teacher::create([
                    'msgv' => $request->msgv,
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

    public function sendEmail($id)
    {
        $teacher = Teacher::findOrFail($id);
        
        if (!$teacher->email) {
            return response()->json([
                'success' => false,
                'message' => 'Giáo viên chưa có email!'
            ], 400);
        }

        try {
            Mail::to($teacher->email)->send(new TeacherWelcomeMail($teacher));
            
            return response()->json([
                'success' => true,
                'message' => 'Email đã được gửi thành công đến ' . $teacher->email
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi gửi email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $teacher = Teacher::findOrFail($id);
        return response()->json($teacher);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'msgv' => 'required|string|max:50|unique:teachers,msgv,' . $id,
            'ho_ten' => 'required|string|max:255',
            'chuyen_mon' => 'nullable|string|max:255',
            'sdt' => 'nullable|string|max:20',
            'dia_chi' => 'nullable|string',
            'email' => 'nullable|email|max:255|unique:teachers,email,' . $id,
            'ngay_sinh' => 'nullable|date',
        ]);

        $teacher = Teacher::findOrFail($id);
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
            $teacher->delete();
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
            Teacher::whereIn('id', $teachers->pluck('id')->all())->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa ' . $teachers->count() . ' giáo viên.'
        ]);
    }
}
