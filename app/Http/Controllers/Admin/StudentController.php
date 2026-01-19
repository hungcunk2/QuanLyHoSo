<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\StudentWelcomeMail;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function index()
    {
        return view('admin.students');
    }

    public function getData(Request $request)
    {
        $query = Student::select('id', 'mssv', 'ho_ten', 'email', 'lop', 'so_dien_thoai', 'ngay_sinh', 'created_at', 'updated_at');

        return DataTables::of($query)
            ->addColumn('check', function ($student) {
                return '<input type="checkbox" class="form-check-input row-checkbox" name="selected_ids[]" value="' . $student->id . '">';
            })
            ->addColumn('action', function ($student) {
                return '
                    <button class="btn btn-sm btn-primary me-1 edit-btn" data-id="' . $student->id . '" title="Sửa">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-info me-1 send-email-btn" data-id="' . $student->id . '" data-email="' . ($student->email ?? '') . '" title="Gửi email">
                        <i class="fas fa-envelope"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="' . $student->id . '" title="Xóa">
                        <i class="fas fa-trash"></i>
                    </button>
                ';
            })
            ->editColumn('ngay_sinh', function ($student) {
                return $student->ngay_sinh ? $student->ngay_sinh->format('d/m/Y') : '';
            })
            ->rawColumns(['check', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mssv' => 'required|string|max:50|unique:students,mssv|unique:users,username',
            'email' => 'required|email|max:255|unique:students,email|unique:users,email',
            'ho_ten' => 'required|string|max:255',
            'lop' => 'required|string|max:50',
        ], [
            'mssv.required' => 'Vui lòng nhập mã số học sinh.',
            'mssv.unique' => 'Mã số học sinh đã tồn tại trong hệ thống.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng bởi học sinh khác.',
            'ho_ten.required' => 'Vui lòng nhập họ và tên.',
            'lop.required' => 'Vui lòng nhập lớp.',
        ]);

        // Tạo mật khẩu 6 số ngẫu nhiên
        $password = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Tạo user trước
        $user = User::create([
            'username' => $request->mssv,
            'email' => $request->email,
            'password' => $password, // Sẽ được hash tự động bởi cast 'hashed'
            'role' => 'student',
            'status' => true,
        ]);

        // Tạo student sau khi user đã được tạo thành công
        $student = Student::create([
            'mssv' => $request->mssv,
            'email' => $request->email,
            'ho_ten' => $request->ho_ten,
            'lop' => $request->lop,
        ]);

        // Gửi email chào mừng với thông tin đăng nhập
        try {
            if ($student->email) {
                Mail::to($student->email)->send(new StudentWelcomeMail($student, $password));
            }
        } catch (\Exception $e) {
            // Log lỗi nhưng không làm gián đoạn quá trình tạo học sinh
            \Log::error('Lỗi gửi email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Tạo học sinh mới thành công! Tài khoản đã được tạo và thông tin đăng nhập đã được gửi qua email.',
            'data' => $student
        ]);
    }

    public function show($id)
    {
        $student = Student::findOrFail($id);
        return response()->json($student);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'mssv' => 'required|string|max:50|unique:students,mssv,' . $id,
            'email' => 'nullable|email|max:255|unique:students,email,' . $id,
            'ho_ten' => 'required|string|max:255',
            'lop' => 'nullable|string|max:50',
            'so_dien_thoai' => 'nullable|string|max:20',
            'ngay_sinh' => 'nullable|date',
            'dia_chi' => 'nullable|string',
            'ho_ten_cha' => 'nullable|string|max:255',
            'sdt_cha' => 'nullable|string|max:20',
            'ho_ten_me' => 'nullable|string|max:255',
            'sdt_me' => 'nullable|string|max:20',
        ]);

        $student = Student::findOrFail($id);
        $student->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin học sinh thành công!'
        ]);
    }

    public function sendEmail($id)
    {
        $student = Student::findOrFail($id);
        
        if (!$student->email) {
            return response()->json([
                'success' => false,
                'message' => 'Học sinh chưa có email!'
            ], 400);
        }

        try {
            Mail::to($student->email)->send(new StudentWelcomeMail($student));
            
            return response()->json([
                'success' => true,
                'message' => 'Email đã được gửi thành công đến ' . $student->email
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi gửi email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa học sinh thành công!'
        ]);
    }
}
