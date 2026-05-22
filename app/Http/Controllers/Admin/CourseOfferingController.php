<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseOffering;
use App\Models\CourseOfferingSchedule;
use App\Models\Subject;
use App\Models\SubjectRegistration;
use App\Services\CourseOfferingScheduleConflictService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CourseOfferingController extends Controller
{
    public function show($id)
    {
        $offering = CourseOffering::with('schedules')->findOrFail($id);
        $schedulesLt = $offering->schedules->where('loai', 'ly_thuyet')->values();
        $schedulesTh = $offering->schedules->where('loai', 'thuc_hanh')->values();

        $data = $offering->only([
            'id', 'ten_hoc_phan', 'class_room_id', 'class_room_id_thuc_hanh', 'subject_id', 'teacher_id', 'si_so_lop',
            'si_so_thuc_hanh_nhom_1', 'si_so_thuc_hanh_nhom_2',
            'hoc_ky', 'khoa_hoc',
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
        $data['teacher_id_ly_thuyet'] = array_merge(
            [$offering->teacher_id_ly_thuyet],
            $schedulesLt->pluck('teacher_id')->all()
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
        $data['teacher_id_thuc_hanh'] = $offering->teacher_id_thuc_hanh !== null
            ? array_merge([$offering->teacher_id_thuc_hanh], $schedulesTh->pluck('teacher_id')->all())
            : $schedulesTh->pluck('teacher_id')->all();
        $data['class_room_id_thuc_hanh'] = $offering->class_room_id_thuc_hanh !== null
            ? array_merge([$offering->class_room_id_thuc_hanh], $schedulesTh->pluck('class_room_id')->all())
            : $schedulesTh->pluck('class_room_id')->all();
        $data['ngay_thi_thuc_hanh_buoi_thu'] = $offering->ngay_thi_thuc_hanh_buoi_thu !== null
            ? array_merge([$offering->ngay_thi_thuc_hanh_buoi_thu], $schedulesTh->pluck('thi_buoi_thu')->all())
            : $schedulesTh->pluck('thi_buoi_thu')->all();

        $data['si_so_thuc_hanh'] = array_values(array_filter([
            $offering->si_so_thuc_hanh_nhom_1,
            $offering->si_so_thuc_hanh_nhom_2,
        ], fn ($v) => $v !== null && $v !== ''));

        $hasTh = ($offering->thu_thuc_hanh && $offering->tiet_thuc_hanh)
            || $schedulesTh->contains(fn ($s) => $s->thu && ($s->tiet ?? '') !== '');
        $data['si_so_ly_thuyet'] = $hasTh ? null : $offering->si_so_lop;

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'hoc_ky' => 'required|integer|in:1,2,3',
            'khoa_hoc' => 'required|string|max:50',
            'class_room_id' => 'required|exists:classes,id',
            'class_room_id_thuc_hanh' => 'nullable|array',
            'class_room_id_thuc_hanh.*' => 'nullable|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'si_so_lop' => 'nullable|integer|min:1',
            'si_so_ly_thuyet' => 'nullable|integer|min:1',
            'si_so_thuc_hanh_nhom_1' => 'nullable|integer|min:1',
            'si_so_thuc_hanh_nhom_2' => 'nullable|integer|min:1',
            'ngay_mo_dang_ky' => 'required|date',
            'ngay_ket_thuc_dang_ky' => 'required|date|after_or_equal:ngay_mo_dang_ky|before:ngay_bat_dau_hoc',
            'ngay_bat_dau_hoc' => 'required|date|after:ngay_ket_thuc_dang_ky',
            'ngay_ket_thuc_hoc' => 'required|date|after_or_equal:ngay_bat_dau_hoc',
            'thu_ly_thuyet' => 'required|array|min:1',
            'thu_ly_thuyet.*' => 'required|integer|in:2,3,4,5,6,7,8',
            'tiet_ly_thuyet' => 'required|array|min:1',
            'tiet_ly_thuyet.*' => 'required|string|max:50',
            'teacher_id_ly_thuyet' => 'required|array|min:1',
            'teacher_id_ly_thuyet.*' => 'required|exists:teachers,id',
            'ngay_thi_ly_thuyet_buoi_thu' => 'required|array|min:1',
            'ngay_thi_ly_thuyet_buoi_thu.*' => 'required|integer|min:1',
            'thu_thuc_hanh' => 'nullable|array',
            'thu_thuc_hanh.*' => 'nullable|integer|in:2,3,4,5,6,7,8',
            'tiet_thuc_hanh' => 'nullable|array',
            'tiet_thuc_hanh.*' => 'nullable|string|max:50',
            'teacher_id_thuc_hanh' => 'nullable|array',
            'teacher_id_thuc_hanh.*' => 'nullable|exists:teachers,id',
            'ngay_thi_thuc_hanh_buoi_thu' => 'nullable|array',
            'ngay_thi_thuc_hanh_buoi_thu.*' => 'nullable|integer|min:1',
        ], [
            'class_room_id.required' => 'Vui lòng chọn phòng học.',
            'subject_id.required' => 'Vui lòng chọn môn học.',
            'si_so_ly_thuyet.min' => 'Sĩ số lớp (lý thuyết) phải lớn hơn 0.',
            'ngay_mo_dang_ky.required' => 'Vui lòng chọn ngày mở đăng ký.',
            'ngay_ket_thuc_dang_ky.required' => 'Vui lòng chọn ngày kết thúc đăng ký.',
            'ngay_ket_thuc_dang_ky.after_or_equal' => 'Ngày kết thúc đăng ký phải bằng hoặc sau ngày mở đăng ký.',
            'ngay_ket_thuc_dang_ky.before' => 'Ngày kết thúc đăng ký phải trước ngày bắt đầu học.',
            'ngay_bat_dau_hoc.required' => 'Vui lòng chọn ngày bắt đầu học.',
            'ngay_bat_dau_hoc.after' => 'Ngày bắt đầu học phải sau ngày kết thúc đăng ký.',
            'ngay_ket_thuc_hoc.required' => 'Vui lòng chọn ngày kết thúc học.',
            'ngay_ket_thuc_hoc.after_or_equal' => 'Ngày kết thúc học phải bằng hoặc sau ngày bắt đầu học.',
            'thu_ly_thuyet.required' => 'Vui lòng chọn ít nhất một buổi lý thuyết.',
            'tiet_ly_thuyet.required' => 'Vui lòng chọn tiết cho mỗi buổi lý thuyết.',
            'teacher_id_ly_thuyet.required' => 'Vui lòng chọn giáo viên cho từng buổi lý thuyết.',
            'teacher_id_ly_thuyet.*.required' => 'Vui lòng chọn giáo viên cho từng buổi lý thuyết.',
            'ngay_thi_ly_thuyet_buoi_thu.required' => 'Vui lòng nhập buổi thi lý thuyết cho từng buổi học.',
            'ngay_thi_ly_thuyet_buoi_thu.*.required' => 'Vui lòng nhập buổi thi lý thuyết cho từng buổi học.',
        ]);

        $thuLt = $request->input('thu_ly_thuyet', []);
        $tietLt = $request->input('tiet_ly_thuyet', []);
        $teacherLt = $request->input('teacher_id_ly_thuyet', []);
        $thiLt = $request->input('ngay_thi_ly_thuyet_buoi_thu', []);
        $thuTh = $request->input('thu_thuc_hanh', []);
        $tietTh = $request->input('tiet_thuc_hanh', []);
        $teacherTh = $request->input('teacher_id_thuc_hanh', []);
        $roomTh = $request->input('class_room_id_thuc_hanh', []);
        $sizeTh = $request->input('si_so_thuc_hanh', []);
        $thiTh = $request->input('ngay_thi_thuc_hanh_buoi_thu', []);

        $hasAnyTh = false;
        for ($i = 0; $i < count($thuTh); $i++) {
            if (($thuTh[$i] ?? '') !== '' && ($tietTh[$i] ?? '') !== '') {
                $hasAnyTh = true;
                break;
            }
        }
        // Nếu có buổi TH (chọn thứ/tiết) thì buổi thi TH cũng bắt buộc nhập.
        for ($i = 0; $i < count($thuTh); $i++) {
            $hasTh = ($thuTh[$i] ?? '') !== '' && ($tietTh[$i] ?? '') !== '';
            if ($hasTh && (($thiTh[$i] ?? '') === '')) {
                throw ValidationException::withMessages([
                    'ngay_thi_thuc_hanh_buoi_thu.'.$i => ['Vui lòng nhập buổi thi thực hành cho buổi TH '.($i + 1).'.'],
                ]);
            }
            if ($hasTh && (int) ($roomTh[$i] ?? 0) < 1) {
                throw ValidationException::withMessages([
                    'class_room_id_thuc_hanh.'.$i => ['Vui lòng chọn phòng học cho nhóm thực hành '.($i + 1).'.'],
                ]);
            }
            if ($hasTh && (int) ($sizeTh[$i] ?? 0) < 1) {
                throw ValidationException::withMessages([
                    'si_so_thuc_hanh.'.$i => ['Vui lòng nhập sĩ số cho nhóm thực hành '.($i + 1).'.'],
                ]);
            }
        }

        $slots = CourseOfferingScheduleConflictService::slotsFromRequestArrays($thuLt, $tietLt, $thuTh, $tietTh);
        $teacherIds = array_merge(
            array_map('intval', array_filter($teacherLt, fn ($x) => $x !== null && $x !== '')),
            array_map('intval', array_filter($teacherTh, fn ($x) => $x !== null && $x !== ''))
        );
        $conflict = CourseOfferingScheduleConflictService::findConflict(
            $slots,
            $teacherIds,
            (int) $request->class_room_id,
            Carbon::parse($request->ngay_bat_dau_hoc),
            Carbon::parse($request->ngay_ket_thuc_hoc),
            null
        );
        if ($conflict !== null) {
            throw ValidationException::withMessages(['schedule' => [$conflict]]);
        }

        $siSoLop = $this->resolveSiSoLopFromRequest($request);

        $data = $request->only([
            'hoc_ky', 'khoa_hoc', 'class_room_id', 'class_room_id_thuc_hanh', 'subject_id',
            'si_so_thuc_hanh_nhom_1', 'si_so_thuc_hanh_nhom_2',
            'ngay_mo_dang_ky', 'ngay_ket_thuc_dang_ky', 'ngay_bat_dau_hoc', 'ngay_ket_thuc_hoc',
        ]);
        $data['si_so_lop'] = $siSoLop;
        // Map first 2 group sizes to existing columns
        $data['si_so_thuc_hanh_nhom_1'] = isset($sizeTh[0]) && $sizeTh[0] !== '' ? (int) $sizeTh[0] : null;
        $data['si_so_thuc_hanh_nhom_2'] = isset($sizeTh[1]) && $sizeTh[1] !== '' ? (int) $sizeTh[1] : null;
        $subject = Subject::findOrFail((int) $request->subject_id);
        $data['ten_hoc_phan'] = (string) ($subject->ten_mon_hoc ?? '');
        // room TH: store first group on offering, rest on schedules
        $data['class_room_id_thuc_hanh'] = isset($roomTh[0]) && $roomTh[0] !== '' ? (int) $roomTh[0] : null;
        // teacher_id giữ để tương thích code cũ -> lấy theo giáo viên lý thuyết buổi 1 (bắt buộc).
        $data['teacher_id'] = (int) ($teacherLt[0] ?? 0);
        $data['teacher_id_ly_thuyet'] = isset($teacherLt[0]) && $teacherLt[0] !== '' ? (int) $teacherLt[0] : null;
        $data['teacher_id_thuc_hanh'] = isset($teacherTh[0]) && $teacherTh[0] !== '' ? (int) $teacherTh[0] : null;
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
                'teacher_id' => isset($teacherLt[$i]) && $teacherLt[$i] !== '' ? (int) $teacherLt[$i] : null,
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
                'teacher_id' => isset($teacherTh[$i]) && $teacherTh[$i] !== '' ? (int) $teacherTh[$i] : null,
                'class_room_id' => isset($roomTh[$i]) && $roomTh[$i] !== '' ? (int) $roomTh[$i] : null,
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
            'hoc_ky' => 'required|integer|in:1,2,3',
            'khoa_hoc' => 'required|string|max:50',
            'class_room_id' => 'required|exists:classes,id',
            'class_room_id_thuc_hanh' => 'nullable|array',
            'class_room_id_thuc_hanh.*' => 'nullable|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'si_so_lop' => 'nullable|integer|min:1',
            'si_so_ly_thuyet' => 'nullable|integer|min:1',
            'si_so_thuc_hanh_nhom_1' => 'nullable|integer|min:1',
            'si_so_thuc_hanh_nhom_2' => 'nullable|integer|min:1',
            'ngay_mo_dang_ky' => 'required|date',
            'ngay_ket_thuc_dang_ky' => 'required|date|after_or_equal:ngay_mo_dang_ky|before:ngay_bat_dau_hoc',
            'ngay_bat_dau_hoc' => 'required|date|after:ngay_ket_thuc_dang_ky',
            'ngay_ket_thuc_hoc' => 'required|date|after_or_equal:ngay_bat_dau_hoc',
            'thu_ly_thuyet' => 'required|array|min:1',
            'thu_ly_thuyet.*' => 'required|integer|in:2,3,4,5,6,7,8',
            'tiet_ly_thuyet' => 'required|array|min:1',
            'tiet_ly_thuyet.*' => 'required|string|max:50',
            'teacher_id_ly_thuyet' => 'required|array|min:1',
            'teacher_id_ly_thuyet.*' => 'required|exists:teachers,id',
            'ngay_thi_ly_thuyet_buoi_thu' => 'required|array|min:1',
            'ngay_thi_ly_thuyet_buoi_thu.*' => 'required|integer|min:1',
            'thu_thuc_hanh' => 'nullable|array',
            'thu_thuc_hanh.*' => 'nullable|integer|in:2,3,4,5,6,7,8',
            'tiet_thuc_hanh' => 'nullable|array',
            'tiet_thuc_hanh.*' => 'nullable|string|max:50',
            'teacher_id_thuc_hanh' => 'nullable|array',
            'teacher_id_thuc_hanh.*' => 'nullable|exists:teachers,id',
            'ngay_thi_thuc_hanh_buoi_thu' => 'nullable|array',
            'ngay_thi_thuc_hanh_buoi_thu.*' => 'nullable|integer|min:1',
        ], [
            'class_room_id.required' => 'Vui lòng chọn phòng học.',
            'subject_id.required' => 'Vui lòng chọn môn học.',
            'si_so_ly_thuyet.min' => 'Sĩ số lớp (lý thuyết) phải lớn hơn 0.',
            'ngay_mo_dang_ky.required' => 'Vui lòng chọn ngày mở đăng ký.',
            'ngay_ket_thuc_dang_ky.required' => 'Vui lòng chọn ngày kết thúc đăng ký.',
            'ngay_ket_thuc_dang_ky.after_or_equal' => 'Ngày kết thúc đăng ký phải bằng hoặc sau ngày mở đăng ký.',
            'ngay_ket_thuc_dang_ky.before' => 'Ngày kết thúc đăng ký phải trước ngày bắt đầu học.',
            'ngay_bat_dau_hoc.required' => 'Vui lòng chọn ngày bắt đầu học.',
            'ngay_bat_dau_hoc.after' => 'Ngày bắt đầu học phải sau ngày kết thúc đăng ký.',
            'ngay_ket_thuc_hoc.required' => 'Vui lòng chọn ngày kết thúc học.',
            'ngay_ket_thuc_hoc.after_or_equal' => 'Ngày kết thúc học phải bằng hoặc sau ngày bắt đầu học.',
            'thu_ly_thuyet.required' => 'Vui lòng chọn ít nhất một buổi lý thuyết.',
            'tiet_ly_thuyet.required' => 'Vui lòng chọn tiết cho mỗi buổi lý thuyết.',
            'teacher_id_ly_thuyet.required' => 'Vui lòng chọn giáo viên cho từng buổi lý thuyết.',
            'teacher_id_ly_thuyet.*.required' => 'Vui lòng chọn giáo viên cho từng buổi lý thuyết.',
            'ngay_thi_ly_thuyet_buoi_thu.required' => 'Vui lòng nhập buổi thi lý thuyết cho từng buổi học.',
            'ngay_thi_ly_thuyet_buoi_thu.*.required' => 'Vui lòng nhập buổi thi lý thuyết cho từng buổi học.',
        ]);

        $thuLt = $request->input('thu_ly_thuyet', []);
        $tietLt = $request->input('tiet_ly_thuyet', []);
        $teacherLt = $request->input('teacher_id_ly_thuyet', []);
        $thiLt = $request->input('ngay_thi_ly_thuyet_buoi_thu', []);
        $thuTh = $request->input('thu_thuc_hanh', []);
        $tietTh = $request->input('tiet_thuc_hanh', []);
        $teacherTh = $request->input('teacher_id_thuc_hanh', []);
        $roomTh = $request->input('class_room_id_thuc_hanh', []);
        $sizeTh = $request->input('si_so_thuc_hanh', []);
        $thiTh = $request->input('ngay_thi_thuc_hanh_buoi_thu', []);

        $hasAnyTh = false;
        for ($i = 0; $i < count($thuTh); $i++) {
            if (($thuTh[$i] ?? '') !== '' && ($tietTh[$i] ?? '') !== '') {
                $hasAnyTh = true;
                break;
            }
        }
        // Nếu có buổi TH (chọn thứ/tiết) thì buổi thi TH cũng bắt buộc nhập.
        for ($i = 0; $i < count($thuTh); $i++) {
            $hasTh = ($thuTh[$i] ?? '') !== '' && ($tietTh[$i] ?? '') !== '';
            if ($hasTh && (($thiTh[$i] ?? '') === '')) {
                throw ValidationException::withMessages([
                    'ngay_thi_thuc_hanh_buoi_thu.'.$i => ['Vui lòng nhập buổi thi thực hành cho buổi TH '.($i + 1).'.'],
                ]);
            }
            if ($hasTh && (int) ($roomTh[$i] ?? 0) < 1) {
                throw ValidationException::withMessages([
                    'class_room_id_thuc_hanh.'.$i => ['Vui lòng chọn phòng học cho nhóm thực hành '.($i + 1).'.'],
                ]);
            }
            if ($hasTh && (int) ($sizeTh[$i] ?? 0) < 1) {
                throw ValidationException::withMessages([
                    'si_so_thuc_hanh.'.$i => ['Vui lòng nhập sĩ số cho nhóm thực hành '.($i + 1).'.'],
                ]);
            }
        }

        $slots = CourseOfferingScheduleConflictService::slotsFromRequestArrays($thuLt, $tietLt, $thuTh, $tietTh);
        $teacherIds = array_merge(
            array_map('intval', array_filter($teacherLt, fn ($x) => $x !== null && $x !== '')),
            array_map('intval', array_filter($teacherTh, fn ($x) => $x !== null && $x !== ''))
        );
        $conflict = CourseOfferingScheduleConflictService::findConflict(
            $slots,
            $teacherIds,
            (int) $request->class_room_id,
            Carbon::parse($request->ngay_bat_dau_hoc),
            Carbon::parse($request->ngay_ket_thuc_hoc),
            (int) $id
        );
        if ($conflict !== null) {
            throw ValidationException::withMessages(['schedule' => [$conflict]]);
        }

        $siSoLop = $this->resolveSiSoLopFromRequest($request);

        $data = $request->only([
            'hoc_ky', 'khoa_hoc', 'class_room_id', 'subject_id',
            'si_so_thuc_hanh_nhom_1', 'si_so_thuc_hanh_nhom_2',
            'ngay_mo_dang_ky', 'ngay_ket_thuc_dang_ky', 'ngay_bat_dau_hoc', 'ngay_ket_thuc_hoc',
        ]);
        $data['si_so_lop'] = $siSoLop;
        // Map first 2 group sizes to existing columns
        $data['si_so_thuc_hanh_nhom_1'] = isset($sizeTh[0]) && $sizeTh[0] !== '' ? (int) $sizeTh[0] : null;
        $data['si_so_thuc_hanh_nhom_2'] = isset($sizeTh[1]) && $sizeTh[1] !== '' ? (int) $sizeTh[1] : null;
        $subject = Subject::findOrFail((int) $request->subject_id);
        $data['ten_hoc_phan'] = (string) ($subject->ten_mon_hoc ?? '');
        $data['class_room_id_thuc_hanh'] = isset($roomTh[0]) && $roomTh[0] !== '' ? (int) $roomTh[0] : null;
        $data['teacher_id'] = (int) ($teacherLt[0] ?? 0);
        $data['teacher_id_ly_thuyet'] = isset($teacherLt[0]) && $teacherLt[0] !== '' ? (int) $teacherLt[0] : null;
        $data['teacher_id_thuc_hanh'] = isset($teacherTh[0]) && $teacherTh[0] !== '' ? (int) $teacherTh[0] : null;
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
                'teacher_id' => isset($teacherLt[$i]) && $teacherLt[$i] !== '' ? (int) $teacherLt[$i] : null,
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
                'teacher_id' => isset($teacherTh[$i]) && $teacherTh[$i] !== '' ? (int) $teacherTh[$i] : null,
                'class_room_id' => isset($roomTh[$i]) && $roomTh[$i] !== '' ? (int) $roomTh[$i] : null,
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

    public function destroy($id)
    {
        $offering = CourseOffering::findOrFail($id);
        // Admin được phép xóa cả học phần đã bắt đầu.
        // Xóa các đăng ký liên quan để tránh ràng buộc FK (nếu có).
        SubjectRegistration::where('course_offering_id', $offering->id)->delete();
        $offering->schedules()->delete();
        $offering->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa học phần.',
        ]);
    }

    private function offeringHasThGroups(array $thuTh, array $tietTh): bool
    {
        for ($i = 0; $i < count($thuTh); $i++) {
            if (($thuTh[$i] ?? '') !== '' && ($tietTh[$i] ?? '') !== '') {
                return true;
            }
        }

        return false;
    }

    private function resolveSiSoLopFromRequest(Request $request): int
    {
        $thuTh = $request->input('thu_thuc_hanh', []);
        $tietTh = $request->input('tiet_thuc_hanh', []);
        $sizeTh = $request->input('si_so_thuc_hanh', []);

        if ($this->offeringHasThGroups($thuTh, $tietTh)) {
            $total = 0;
            for ($i = 0; $i < count($thuTh); $i++) {
                if (($thuTh[$i] ?? '') === '' || ($tietTh[$i] ?? '') === '') {
                    continue;
                }
                $total += max(0, (int) ($sizeTh[$i] ?? 0));
            }
            if ($total < 1) {
                throw ValidationException::withMessages([
                    'si_so_thuc_hanh' => ['Vui lòng nhập sĩ số cho từng nhóm thực hành.'],
                ]);
            }

            return $total;
        }

        $lt = (int) $request->input('si_so_ly_thuyet', 0);
        if ($lt < 1) {
            $lt = (int) $request->input('si_so_lop', 0);
        }
        if ($lt < 1) {
            throw ValidationException::withMessages([
                'si_so_ly_thuyet' => ['Vui lòng nhập sĩ số lớp (lý thuyết).'],
            ]);
        }

        return $lt;
    }
}
