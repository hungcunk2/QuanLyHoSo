<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseOffering;
use App\Models\CourseOfferingSchedule;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class CourseOfferingController extends Controller
{
    public function show($id)
    {
        $offering = CourseOffering::with('schedules')->findOrFail($id);
        $schedulesLt = $offering->schedules->where('loai', 'ly_thuyet')->values();
        $schedulesTh = $offering->schedules->where('loai', 'thuc_hanh')->values();

        $data = $offering->only([
            'id', 'ten_hoc_phan', 'class_room_id', 'subject_id', 'teacher_id', 'si_so_lop',
            'thu_ly_thuyet', 'tiet_ly_thuyet', 'ngay_thi_ly_thuyet_buoi_thu',
            'thu_thuc_hanh', 'tiet_thuc_hanh', 'ngay_thi_thuc_hanh_buoi_thu',
        ]);
        $data['ngay_mo_dang_ky'] = $offering->ngay_mo_dang_ky?->format('Y-m-d');
        $data['ngay_ket_thuc_dang_ky'] = $offering->ngay_ket_thuc_dang_ky?->format('Y-m-d');
        $data['ngay_bat_dau_hoc'] = $offering->ngay_bat_dau_hoc?->format('Y-m-d');
        $data['ngay_ket_thuc_hoc'] = $offering->ngay_ket_thuc_hoc?->format('Y-m-d');

        $data['thu_ly_thuyet'] = array_merge(
            [$offering->thu_ly_thuyet],
            $schedulesLt->pluck('thu')->all()
        );
        $data['tiet_ly_thuyet'] = array_merge(
            [$offering->tiet_ly_thuyet],
            $schedulesLt->pluck('tiet')->all()
        );
        $data['ngay_thi_ly_thuyet_buoi_thu'] = array_merge(
            [$offering->ngay_thi_ly_thuyet_buoi_thu],
            $schedulesLt->pluck('thi_buoi_thu')->all()
        );

        $data['thu_thuc_hanh'] = $offering->thu_thuc_hanh !== null
            ? array_merge([$offering->thu_thuc_hanh], $schedulesTh->pluck('thu')->all())
            : $schedulesTh->pluck('thu')->all();
        $data['tiet_thuc_hanh'] = $offering->tiet_thuc_hanh !== null && $offering->tiet_thuc_hanh !== ''
            ? array_merge([$offering->tiet_thuc_hanh], $schedulesTh->pluck('tiet')->all())
            : $schedulesTh->pluck('tiet')->all();
        $data['ngay_thi_thuc_hanh_buoi_thu'] = $offering->ngay_thi_thuc_hanh_buoi_thu !== null
            ? array_merge([$offering->ngay_thi_thuc_hanh_buoi_thu], $schedulesTh->pluck('thi_buoi_thu')->all())
            : $schedulesTh->pluck('thi_buoi_thu')->all();

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ten_hoc_phan' => 'required|string|max:255',
            'class_room_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'si_so_lop' => 'required|integer|min:1',
            'ngay_mo_dang_ky' => 'required|date',
            'ngay_ket_thuc_dang_ky' => 'required|date|after_or_equal:ngay_mo_dang_ky',
            'ngay_bat_dau_hoc' => 'required|date',
            'ngay_ket_thuc_hoc' => 'required|date|after_or_equal:ngay_bat_dau_hoc',
            'thu_ly_thuyet' => 'required|array|min:1',
            'thu_ly_thuyet.*' => 'required|integer|in:2,3,4,5,6,7,8',
            'tiet_ly_thuyet' => 'required|array|min:1',
            'tiet_ly_thuyet.*' => 'required|string|max:50',
            'ngay_thi_ly_thuyet_buoi_thu' => 'nullable|array',
            'ngay_thi_ly_thuyet_buoi_thu.*' => 'nullable|integer|min:1',
            'thu_thuc_hanh' => 'nullable|array',
            'thu_thuc_hanh.*' => 'nullable|integer|in:2,3,4,5,6,7,8',
            'tiet_thuc_hanh' => 'nullable|array',
            'tiet_thuc_hanh.*' => 'nullable|string|max:50',
            'ngay_thi_thuc_hanh_buoi_thu' => 'nullable|array',
            'ngay_thi_thuc_hanh_buoi_thu.*' => 'nullable|integer|min:1',
        ], [
            'ten_hoc_phan.required' => 'Vui lòng nhập tên học phần.',
            'class_room_id.required' => 'Vui lòng chọn lớp học.',
            'subject_id.required' => 'Vui lòng chọn môn học.',
            'teacher_id.required' => 'Vui lòng chọn giáo viên phụ trách.',
            'si_so_lop.required' => 'Vui lòng nhập sĩ số lớp.',
            'si_so_lop.min' => 'Sĩ số lớp phải lớn hơn 0.',
            'ngay_mo_dang_ky.required' => 'Vui lòng chọn ngày mở đăng ký.',
            'ngay_ket_thuc_dang_ky.required' => 'Vui lòng chọn ngày kết thúc đăng ký.',
            'ngay_bat_dau_hoc.required' => 'Vui lòng chọn ngày bắt đầu học.',
            'ngay_ket_thuc_hoc.required' => 'Vui lòng chọn ngày kết thúc học.',
            'thu_ly_thuyet.required' => 'Vui lòng chọn ít nhất một buổi lý thuyết.',
            'tiet_ly_thuyet.required' => 'Vui lòng chọn tiết cho mỗi buổi lý thuyết.',
        ]);

        $thuLt = $request->input('thu_ly_thuyet', []);
        $tietLt = $request->input('tiet_ly_thuyet', []);
        $thiLt = $request->input('ngay_thi_ly_thuyet_buoi_thu', []);
        $thuTh = $request->input('thu_thuc_hanh', []);
        $tietTh = $request->input('tiet_thuc_hanh', []);
        $thiTh = $request->input('ngay_thi_thuc_hanh_buoi_thu', []);

        $data = $request->only([
            'ten_hoc_phan', 'class_room_id', 'subject_id', 'teacher_id', 'si_so_lop',
            'ngay_mo_dang_ky', 'ngay_ket_thuc_dang_ky', 'ngay_bat_dau_hoc', 'ngay_ket_thuc_hoc',
        ]);
        $data['thu_ly_thuyet'] = $thuLt[0] ?? null;
        $data['tiet_ly_thuyet'] = $tietLt[0] ?? '';
        $data['ngay_thi_ly_thuyet_buoi_thu'] = isset($thiLt[0]) && $thiLt[0] !== '' ? (int) $thiLt[0] : null;
        $data['thu_thuc_hanh'] = isset($thuTh[0]) && $thuTh[0] !== '' ? $thuTh[0] : null;
        $data['tiet_thuc_hanh'] = isset($tietTh[0]) && $tietTh[0] !== '' ? $tietTh[0] : null;
        $data['ngay_thi_thuc_hanh_buoi_thu'] = isset($thiTh[0]) && $thiTh[0] !== '' ? (int) $thiTh[0] : null;

        $offering = CourseOffering::create($data);

        // Buổi lý thuyết thứ 2 trở đi -> bảng schedules
        for ($i = 1; $i < count($thuLt); $i++) {
            CourseOfferingSchedule::create([
                'course_offering_id' => $offering->id,
                'loai' => 'ly_thuyet',
                'thu' => $thuLt[$i],
                'tiet' => $tietLt[$i] ?? '',
                'thi_buoi_thu' => isset($thiLt[$i]) && $thiLt[$i] !== '' ? (int) $thiLt[$i] : null,
            ]);
        }
        // Buổi thực hành thứ 2 trở đi
        for ($i = 1; $i < count($thuTh); $i++) {
            if (empty($thuTh[$i]) || empty($tietTh[$i] ?? '')) {
                continue;
            }
            CourseOfferingSchedule::create([
                'course_offering_id' => $offering->id,
                'loai' => 'thuc_hanh',
                'thu' => $thuTh[$i],
                'tiet' => $tietTh[$i] ?? '',
                'thi_buoi_thu' => isset($thiTh[$i]) && $thiTh[$i] !== '' ? (int) $thiTh[$i] : null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tạo học phần mới thành công!',
        ]);
    }

    public function update(Request $request, $id)
    {
        $offering = CourseOffering::findOrFail($id);

        $request->validate([
            'ten_hoc_phan' => 'required|string|max:255',
            'class_room_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'si_so_lop' => 'required|integer|min:1',
            'ngay_mo_dang_ky' => 'required|date',
            'ngay_ket_thuc_dang_ky' => 'required|date|after_or_equal:ngay_mo_dang_ky',
            'ngay_bat_dau_hoc' => 'required|date',
            'ngay_ket_thuc_hoc' => 'required|date|after_or_equal:ngay_bat_dau_hoc',
            'thu_ly_thuyet' => 'required|array|min:1',
            'thu_ly_thuyet.*' => 'required|integer|in:2,3,4,5,6,7,8',
            'tiet_ly_thuyet' => 'required|array|min:1',
            'tiet_ly_thuyet.*' => 'required|string|max:50',
            'ngay_thi_ly_thuyet_buoi_thu' => 'nullable|array',
            'ngay_thi_ly_thuyet_buoi_thu.*' => 'nullable|integer|min:1',
            'thu_thuc_hanh' => 'nullable|array',
            'thu_thuc_hanh.*' => 'nullable|integer|in:2,3,4,5,6,7,8',
            'tiet_thuc_hanh' => 'nullable|array',
            'tiet_thuc_hanh.*' => 'nullable|string|max:50',
            'ngay_thi_thuc_hanh_buoi_thu' => 'nullable|array',
            'ngay_thi_thuc_hanh_buoi_thu.*' => 'nullable|integer|min:1',
        ], [
            'ten_hoc_phan.required' => 'Vui lòng nhập tên học phần.',
            'class_room_id.required' => 'Vui lòng chọn lớp học.',
            'subject_id.required' => 'Vui lòng chọn môn học.',
            'teacher_id.required' => 'Vui lòng chọn giáo viên phụ trách.',
            'si_so_lop.required' => 'Vui lòng nhập sĩ số lớp.',
            'thu_ly_thuyet.required' => 'Vui lòng chọn ít nhất một buổi lý thuyết.',
            'tiet_ly_thuyet.required' => 'Vui lòng chọn tiết cho mỗi buổi lý thuyết.',
        ]);

        $thuLt = $request->input('thu_ly_thuyet', []);
        $tietLt = $request->input('tiet_ly_thuyet', []);
        $thiLt = $request->input('ngay_thi_ly_thuyet_buoi_thu', []);
        $thuTh = $request->input('thu_thuc_hanh', []);
        $tietTh = $request->input('tiet_thuc_hanh', []);
        $thiTh = $request->input('ngay_thi_thuc_hanh_buoi_thu', []);

        $data = $request->only([
            'ten_hoc_phan', 'class_room_id', 'subject_id', 'teacher_id', 'si_so_lop',
            'ngay_mo_dang_ky', 'ngay_ket_thuc_dang_ky', 'ngay_bat_dau_hoc', 'ngay_ket_thuc_hoc',
        ]);
        $data['thu_ly_thuyet'] = $thuLt[0] ?? null;
        $data['tiet_ly_thuyet'] = $tietLt[0] ?? '';
        $data['ngay_thi_ly_thuyet_buoi_thu'] = isset($thiLt[0]) && $thiLt[0] !== '' ? (int) $thiLt[0] : null;
        $data['thu_thuc_hanh'] = isset($thuTh[0]) && $thuTh[0] !== '' ? $thuTh[0] : null;
        $data['tiet_thuc_hanh'] = isset($tietTh[0]) && $tietTh[0] !== '' ? $tietTh[0] : null;
        $data['ngay_thi_thuc_hanh_buoi_thu'] = isset($thiTh[0]) && $thiTh[0] !== '' ? (int) $thiTh[0] : null;

        $offering->update($data);

        $offering->schedules()->delete();

        for ($i = 1; $i < count($thuLt); $i++) {
            CourseOfferingSchedule::create([
                'course_offering_id' => $offering->id,
                'loai' => 'ly_thuyet',
                'thu' => $thuLt[$i],
                'tiet' => $tietLt[$i] ?? '',
                'thi_buoi_thu' => isset($thiLt[$i]) && $thiLt[$i] !== '' ? (int) $thiLt[$i] : null,
            ]);
        }
        for ($i = 1; $i < count($thuTh); $i++) {
            if (empty($thuTh[$i]) || empty($tietTh[$i] ?? '')) {
                continue;
            }
            CourseOfferingSchedule::create([
                'course_offering_id' => $offering->id,
                'loai' => 'thuc_hanh',
                'thu' => $thuTh[$i],
                'tiet' => $tietTh[$i] ?? '',
                'thi_buoi_thu' => isset($thiTh[$i]) && $thiTh[$i] !== '' ? (int) $thiTh[$i] : null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật học phần thành công!',
        ]);
    }
}
