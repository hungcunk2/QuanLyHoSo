<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\StudentWelcomeMail;
use App\Models\Lop;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function index()
    {
        $lops = Lop::orderBy('ma_lop')->get(['id', 'ma_lop', 'ten_lop']);

        return view('admin.students', compact('lops'));
    }

    public function getData(Request $request)
    {
        $query = Student::query();

        return DataTables::of($query)
            ->addColumn('check', function ($student) {
                return '<input type="checkbox" class="form-check-input row-checkbox" name="selected_ids[]" value="' . $student->id . '">';
            })
            ->addColumn('action', function ($student) {
                $editData = [
                    'id' => $student->id,
                    'mssv' => $student->mssv ?? '',
                    'ho_ten' => $student->ho_ten ?? '',
                    'gioi_tinh' => $student->gioi_tinh ?? '',
                    'trang_thai' => $student->trang_thai ?? '',
                    'ma_ho_so' => $student->ma_ho_so ?? '',
                    'ngay_vao_truong' => $student->ngay_vao_truong ? $student->ngay_vao_truong->format('Y-m-d') : '',
                    'lop' => $student->lop ?? '',
                    'co_so' => $student->co_so ?? '',
                    'bac_dao_tao' => $student->bac_dao_tao ?? '',
                    'loai_hinh_dao_tao' => $student->loai_hinh_dao_tao ?? '',
                    'khoa' => $student->khoa ?? '',
                    'nganh' => $student->nganh ?? '',
                    'chuyen_nganh' => $student->chuyen_nganh ?? '',
                    'khoa_hoc' => $student->khoa_hoc ?? '',
                    'email' => $student->email ?? '',
                    'so_dien_thoai' => $student->so_dien_thoai ?? '',
                    'ngay_sinh' => $student->ngay_sinh ? $student->ngay_sinh->format('Y-m-d') : '',
                    'dia_chi' => $student->dia_chi ?? '',
                    'ho_ten_cha' => $student->ho_ten_cha ?? '',
                    'sdt_cha' => $student->sdt_cha ?? '',
                    'ho_ten_me' => $student->ho_ten_me ?? '',
                    'sdt_me' => $student->sdt_me ?? '',
                ];
                $editJson = base64_encode(json_encode($editData));
                return '
                    <button type="button" class="btn btn-sm btn-primary me-1 edit-btn" data-id="' . (int) $student->id . '" data-edit="' . e($editJson) . '" title="Sửa">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-info me-1 send-email-btn" data-id="' . $student->id . '" data-email="' . e($student->email ?? '') . '" title="Gửi email">
                        <i class="fas fa-envelope"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $student->id . '" title="Xóa">
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
            'lop' => 'required|string|max:50|exists:lops,ma_lop',
        ], [
            'mssv.required' => 'Vui lòng nhập mã số học sinh.',
            'mssv.unique' => 'Mã số học sinh đã tồn tại trong hệ thống.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng bởi học sinh khác.',
            'ho_ten.required' => 'Vui lòng nhập họ và tên.',
            'lop.required' => 'Vui lòng chọn lớp.',
            'lop.exists' => 'Lớp không tồn tại. Vui lòng chọn lớp trong Quản lý lớp.',
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
            'gioi_tinh' => $request->gioi_tinh,
            'trang_thai' => $request->trang_thai,
            'ma_ho_so' => $request->ma_ho_so,
            'ngay_vao_truong' => $request->ngay_vao_truong,
            'co_so' => $request->co_so,
            'bac_dao_tao' => $request->bac_dao_tao,
            'loai_hinh_dao_tao' => $request->loai_hinh_dao_tao,
            'khoa' => $request->khoa,
            'nganh' => $request->nganh,
            'chuyen_nganh' => $request->chuyen_nganh,
            'khoa_hoc' => $request->khoa_hoc,
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
            'lop' => 'nullable|string|max:50|exists:lops,ma_lop',
            'gioi_tinh' => 'nullable|string|max:20',
            'trang_thai' => 'nullable|string|max:50',
            'ma_ho_so' => 'nullable|string|max:100',
            'ngay_vao_truong' => 'nullable|date',
            'co_so' => 'nullable|string|max:255',
            'bac_dao_tao' => 'nullable|string|max:100',
            'loai_hinh_dao_tao' => 'nullable|string|max:100',
            'khoa' => 'nullable|string|max:255',
            'nganh' => 'nullable|string|max:255',
            'chuyen_nganh' => 'nullable|string|max:255',
            'khoa_hoc' => 'nullable|string|max:50',
            'so_dien_thoai' => 'nullable|string|max:20',
            'ngay_sinh' => 'nullable|date',
            'dia_chi' => 'nullable|string',
            'ho_ten_cha' => 'nullable|string|max:255',
            'sdt_cha' => 'nullable|string|max:20',
            'ho_ten_me' => 'nullable|string|max:255',
            'sdt_me' => 'nullable|string|max:20',
        ]);

        $student = Student::findOrFail($id);
        $student->update($request->only($student->getFillable()));

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

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('selected_ids', []);
        if (! is_array($ids) || count($ids) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng chọn ít nhất một học sinh để xóa.',
            ], 422);
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (count($ids) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Danh sách học sinh không hợp lệ.',
            ], 422);
        }

        $students = Student::whereIn('id', $ids)->get(['id', 'mssv']);
        if ($students->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy học sinh để xóa.',
            ], 422);
        }

        DB::transaction(function () use ($students) {
            $usernames = $students->pluck('mssv')->filter()->values()->all();
            if (count($usernames)) {
                User::whereIn('username', $usernames)->where('role', 'student')->delete();
            }
            foreach ($students as $student) {
                $student->delete();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa '.$students->count().' học sinh.',
        ]);
    }
}
