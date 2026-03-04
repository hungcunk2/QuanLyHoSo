<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();
        return view('student.dashboard', compact('user', 'student'));
    }

    public function editProfile()
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();
        if (!$student) {
            return redirect()->route('student.dashboard')->with('message', 'Chưa có hồ sơ sinh viên.');
        }
        return view('student.profile_edit', compact('user', 'student'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();
        if (!$student) {
            return redirect()->route('student.dashboard')->with('message', 'Chưa có hồ sơ sinh viên.');
        }
        $request->validate([
            'ho_ten' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:students,email,' . $student->id . '|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gioi_tinh' => 'nullable|string|max:20',
            'trang_thai' => 'nullable|string|max:50',
            'ma_ho_so' => 'nullable|string|max:100',
            'ngay_vao_truong' => 'nullable|date',
            'lop' => 'nullable|string|max:50',
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
            'dan_toc' => 'nullable|string|max:50',
            'ton_giao' => 'nullable|string|max:100',
            'quoc_tich' => 'nullable|string|max:100',
            'khu_vuc' => 'nullable|string|max:255',
            'so_cccd' => 'nullable|string|max:50',
            'ngay_cap_cccd' => 'nullable|date',
            'noi_cap_cccd' => 'nullable|string|max:255',
            'doi_tuong' => 'nullable|string|max:100',
            'dien_chinh_sach' => 'nullable|string|max:255',
            'ngay_vao_doan' => 'nullable|string|max:50',
            'ngay_vao_dang' => 'nullable|string|max:50',
            'dia_chi_lien_he' => 'nullable|string',
            'noi_sinh' => 'nullable|string|max:255',
            'ho_khau_thuong_tru' => 'nullable|string',
            'ho_ten_cha' => 'nullable|string|max:255',
            'nam_sinh_cha' => 'nullable|string|max:50',
            'nghe_nghiep_cha' => 'nullable|string|max:255',
            'quoc_tich_cha' => 'nullable|string|max:100',
            'dan_toc_cha' => 'nullable|string|max:50',
            'ton_giao_cha' => 'nullable|string|max:100',
            'co_quan_cha' => 'nullable|string|max:255',
            'chuc_vu_cha' => 'nullable|string|max:255',
            'sdt_cha' => 'nullable|string|max:20',
            'ho_ten_me' => 'nullable|string|max:255',
            'sdt_me' => 'nullable|string|max:20',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'avatar.image' => 'File phải là ảnh (jpeg, png, jpg, gif, webp).',
            'avatar.max' => 'Ảnh tối đa 2MB.',
        ]);
        if ($request->hasFile('avatar')) {
            if ($student->avatar && Storage::disk('public')->exists($student->avatar)) {
                Storage::disk('public')->delete($student->avatar);
            }
            Storage::disk('public')->makeDirectory('avatars/students');
            $path = $request->file('avatar')->store('avatars/students', 'public');
            $student->avatar = $path;
        }
        $fillable = array_diff($student->getFillable(), ['mssv', 'avatar']);
        $student->fill($request->only($fillable));
        $student->save();
        if ($user->email !== $request->email) {
            User::where('id', $user->id)->update(['email' => $request->email]);
        }
        return redirect()->route('student.dashboard')->with('success', 'Cập nhật thông tin thành công.');
    }

    public function schedule(Request $request)
    {
        $user = Auth::user();

        $dateParam = $request->query('date');
        $currentDate = $dateParam ? Carbon::parse($dateParam) : Carbon::now();

        return view('student.schedule', compact('user', 'currentDate'));
    }

    public function results()
    {
        $user = Auth::user();
        return view('student.results', compact('user'));
    }

    public function registration()
    {
        $user = Auth::user();
        return view('student.registration', compact('user'));
    }

    public function notifications()
    {
        $user = Auth::user();
        return view('student.notifications', compact('user'));
    }
}
