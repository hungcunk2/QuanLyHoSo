<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\StudentWelcomeMail;
use App\Mail\StudentPasswordChangedMail;
use App\Models\Lop;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function nextMssv()
    {
        return response()->json([
            'success' => true,
            'next_mssv' => Student::generateNextAvailableMssv(8),
        ]);
    }

    public function nextMaHoSo()
    {
        return response()->json([
            'success' => true,
            'next_ma_ho_so' => Student::generateNextAvailableMaHoSo('HS', 2),
        ]);
    }

    public function index()
    {
        $lops = Lop::orderBy('ma_lop')->get(['id', 'ma_lop', 'ten_lop']);

        return view('admin.students', compact('lops'));
    }

    public function getData(Request $request)
    {
        $query = Student::query();

        // Map ma_lop -> ten_lop for display
        $lopNameByCode = Lop::query()->pluck('ten_lop', 'ma_lop')->all();

        $filterName = $request->input('filter_ho_ten');
        if (is_string($filterName) && trim($filterName) !== '') {
            $needle = mb_strtolower(preg_replace('/\s+/u', ' ', trim($filterName)), 'UTF-8');
            $pattern = '%'.addcslashes($needle, '%_\\').'%';
            $query->whereRaw('LOWER(ho_ten) LIKE ?', [$pattern]);
        }

        $filterLop = $request->input('filter_lop');
        if (is_string($filterLop) && trim($filterLop) !== '') {
            $query->where('lop', trim($filterLop));
        }

        return DataTables::of($query)
            ->skipAutoFilter()
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
                    <button type="button" class="btn btn-sm btn-warning me-1 reset-password-btn" data-id="' . $student->id . '" data-email="' . e($student->email ?? '') . '" data-mssv="' . e($student->mssv ?? '') . '" title="Đổi mật khẩu">
                        <i class="fas fa-key"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $student->id . '" title="Xóa">
                        <i class="fas fa-trash"></i>
                    </button>
                ';
            })
            ->editColumn('ngay_sinh', function ($student) {
                return $student->ngay_sinh ? $student->ngay_sinh->format('d/m/Y') : '';
            })
            ->addColumn('lop_ten', function ($student) use ($lopNameByCode) {
                $code = is_string($student->lop) ? $student->lop : '';
                return $lopNameByCode[$code] ?? $code;
            })
            ->rawColumns(['check', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->merge([
            'mssv' => is_string($request->mssv) ? trim($request->mssv) : $request->mssv,
            'email' => is_string($request->email) ? trim(mb_strtolower($request->email)) : $request->email,
            'ma_ho_so' => is_string($request->ma_ho_so) ? trim($request->ma_ho_so) : $request->ma_ho_so,
            'khoa' => is_string($request->khoa) ? trim($request->khoa) : $request->khoa,
            'khoa_hoc' => is_string($request->khoa_hoc) ? trim($request->khoa_hoc) : $request->khoa_hoc,
        ]);

        if (! is_string($request->khoa) || $request->khoa === '') {
            $request->merge(['khoa' => 'Khoa Công nghệ Thông tin']);
        }
        if (! is_string($request->khoa_hoc) || $request->khoa_hoc === '') {
            $year = now()->year;
            $request->merge(['khoa_hoc' => $year . '-' . ($year + 1)]);
        }

        $request->validate([
            'mssv' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('students', 'mssv')->whereNull('deleted_at'),
                Rule::unique('users', 'username'),
            ],
            'ma_ho_so' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('students', 'ma_ho_so')->whereNull('deleted_at'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('students', 'email')->whereNull('deleted_at'),
                Rule::unique('users', 'email'),
            ],
            'ho_ten' => 'required|string|max:255',
            'lop' => 'required|string|max:50|exists:lops,ma_lop',
            'co_so' => ['nullable', 'string', Rule::in(Student::coSoOptions())],
        ], [
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

        // Tạo user + student atomically
        [$user, $student] = DB::transaction(function () use ($request, $password) {
            $mssv = $request->mssv;
            if (! is_string($mssv) || trim($mssv) === '') {
                DB::table('students')
                    ->select('mssv')
                    ->whereRaw("mssv REGEXP '^[0-9]+$'")
                    ->orderByRaw('CAST(mssv AS UNSIGNED) DESC')
                    ->lockForUpdate()
                    ->first();

                $mssv = Student::generateNextAvailableMssv(8);
            } else {
                $mssv = trim($mssv);
            }

            $maHoSo = $request->ma_ho_so;
            if (! is_string($maHoSo) || trim($maHoSo) === '') {
                DB::table('students')
                    ->select('ma_ho_so')
                    ->where('ma_ho_so', 'like', 'HS%')
                    ->orderByRaw('CAST(SUBSTRING(ma_ho_so, 3) AS UNSIGNED) DESC')
                    ->lockForUpdate()
                    ->first();

                $maHoSo = Student::generateNextAvailableMaHoSo('HS', 2);
            } else {
                $maHoSo = trim($maHoSo);
            }

            $user = User::create([
                'username' => $mssv,
                'email' => $request->email,
                'password' => $password, // hash via cast 'hashed'
                'role' => 'student',
                'status' => true,
            ]);

            $student = Student::create([
                'mssv' => $mssv,
                'email' => $request->email,
                'ho_ten' => $request->ho_ten,
                'lop' => $request->lop,
                'gioi_tinh' => $request->gioi_tinh,
                'trang_thai' => $request->trang_thai,
                'ma_ho_so' => $maHoSo,
                'ngay_vao_truong' => $request->ngay_vao_truong,
                'co_so' => $request->co_so,
                'bac_dao_tao' => $request->bac_dao_tao,
                'loai_hinh_dao_tao' => $request->loai_hinh_dao_tao,
                'khoa' => $request->khoa,
                'nganh' => $request->nganh,
                'chuyen_nganh' => $request->chuyen_nganh,
                'khoa_hoc' => $request->khoa_hoc,
            ]);

            return [$user, $student];
        });

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
            'co_so' => ['nullable', 'string', Rule::in(Student::coSoOptions())],
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

    public function resetPassword(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        if (! $student->email) {
            return response()->json([
                'success' => false,
                'message' => 'Học sinh chưa có email nên không thể gửi thông báo đổi mật khẩu.',
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
            DB::transaction(function () use ($student, $newPassword) {
                $user = User::where('username', $student->mssv)->first();
                if (! $user && $student->email) {
                    $user = User::where('email', $student->email)->first();
                }

                if ($user) {
                    $user->forceFill([
                        'username' => $student->mssv ?: $user->username,
                        'email' => $student->email ?: $user->email,
                        'password' => $newPassword,
                        'role' => 'student',
                        'status' => true,
                    ])->save();
                } else {
                    User::create([
                        'username' => $student->mssv,
                        'email' => $student->email,
                        'password' => $newPassword,
                        'role' => 'student',
                        'status' => true,
                    ]);
                }
            });
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể đổi mật khẩu. Vui lòng thử lại.',
            ], 500);
        }

        try {
            Mail::to($student->email)->send(new StudentPasswordChangedMail($student, $newPassword));
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => true,
                'message' => 'Đã đổi mật khẩu thành công. Không gửi được email thông báo — kiểm tra cấu hình MAIL_* trong .env.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công và đã gửi email thông báo cho học sinh.',
        ]);
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        DB::transaction(function () use ($student) {
            // Delete linked user (username = mssv)
            User::where('username', $student->mssv)->where('role', 'student')->delete();
            $student->forceDelete();
        });

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
            Student::whereIn('id', $students->pluck('id')->all())->forceDelete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa '.$students->count().' học sinh.',
        ]);
    }
}
