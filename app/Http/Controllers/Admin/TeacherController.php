<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TeacherWelcomeMail;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;

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
        $request->validate([
            'msgv' => 'required|string|max:50|unique:teachers,msgv|unique:users,username',
            'email' => 'required|email|max:255|unique:teachers,email|unique:users,email',
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

        // Tạo user trước
        $user = User::create([
            'username' => $request->msgv,
            'email' => $request->email,
            'password' => $password, // Sẽ được hash tự động bởi cast 'hashed'
            'role' => 'teacher',
            'status' => true,
        ]);

        // Tạo teacher sau khi user đã được tạo thành công
        $teacher = Teacher::create([
            'msgv' => $request->msgv,
            'email' => $request->email,
            'ho_ten' => $request->ho_ten,
            'chuyen_mon' => $request->chuyen_mon,
        ]);

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
        $teacher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa giáo viên thành công!'
        ]);
    }
}
