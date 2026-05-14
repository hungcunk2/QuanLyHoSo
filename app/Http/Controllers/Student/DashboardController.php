<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseOffering;
use App\Models\CourseOfferingGrade;
use App\Models\Lop;
use App\Models\SubjectRegistration;
use App\Models\Student;
use App\Models\User;
use App\Services\CourseOfferingScheduleConflictService;
use App\Support\OfferingWeekCalendar;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $remindersCount = 0;
        $lopTen = null;
        if ($student && $student->lop) {
            $lopTen = Lop::query()
                ->where('ma_lop', $student->lop)
                ->value('ten_lop');
        }

        $offerings = collect();
        $offeringsLhp = collect();
        $gradesByOffering = collect();
        $hocKyOptions = collect();

        // tách 2 dropdown độc lập: kết quả học tập và lớp học phần
        $selectedDotKq = (string) request()->query('dot_kq', '');
        $selectedDotLhp = (string) request()->query('dot_lhp', '');

        $parseDot = function (string $dot): array {
            if ($dot === '' || !str_contains($dot, '|')) return [null, null];
            [$hk, $kh] = array_pad(explode('|', $dot, 2), 2, '');
            $hk = $hk !== '' ? $hk : null;
            $kh = $kh !== '' ? $kh : null;
            return [$hk, $kh];
        };

        [$kqHocKy, $kqKhoaHoc] = $parseDot($selectedDotKq);
        [$lhpHocKy, $lhpKhoaHoc] = $parseDot($selectedDotLhp);

        $weekClassCount = 0;
        $weekExamCount = 0;

        if ($student) {
            $offeringIds = SubjectRegistration::query()
                ->where('student_id', $student->id)
                ->where('status', '!=', 'cancelled')
                ->whereNotNull('course_offering_id')
                ->pluck('course_offering_id')
                ->unique()
                ->values();

            if ($offeringIds->isNotEmpty()) {
                $allOfferings = CourseOffering::query()
                    ->whereIn('id', $offeringIds)
                    ->where('is_cancelled', false)
                    ->with(['subject', 'classRoom', 'schedules'])
                    ->get();

                $hocKyOptions = $allOfferings
                    ->map(fn ($o) => [
                        'khoa_hoc' => $o->khoa_hoc,
                        'hoc_ky' => $o->hoc_ky,
                    ])
                    ->unique(fn ($x) => (string) ($x['khoa_hoc'] ?? '') . '|' . (string) ($x['hoc_ky'] ?? ''))
                    ->values();

                // điểm và tiến độ tính theo toàn bộ học phần đã đăng ký (không phụ thuộc dropdown)
                $offerings = $allOfferings
                    ->sortBy(fn ($o) => (string) ($o->subject?->ten_mon_hoc ?? $o->ten_hoc_phan ?? ''))
                    ->values();

                $gradesByOffering = CourseOfferingGrade::query()
                    ->whereIn('course_offering_id', $allOfferings->pluck('id'))
                    ->where('student_id', $student->id)
                    ->get()
                    ->keyBy('course_offering_id');

                $filterByDot = function ($collection, $hk, $kh) {
                    if ($hk !== null && $hk !== '') {
                        $collection = $collection->where('hoc_ky', $hk)->values();
                    }
                    if ($kh !== null && $kh !== '') {
                        $collection = $collection->where('khoa_hoc', $kh)->values();
                    }
                    return $collection;
                };

                // danh sách lớp học phần (dropdown riêng)
                $offeringsLhp = $filterByDot($allOfferings, $lhpHocKy, $lhpKhoaHoc)
                    ->sortBy(fn ($o) => (string) ($o->subject?->ten_mon_hoc ?? $o->ten_hoc_phan ?? ''))
                    ->values();

                // lớp học phần để vẽ chart kết quả (dropdown riêng)
                $offeringsKq = $filterByDot($allOfferings, $kqHocKy, $kqKhoaHoc)
                    ->sortBy(fn ($o) => (string) ($o->subject?->ten_mon_hoc ?? $o->ten_hoc_phan ?? ''))
                    ->values();

                $startWeek = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
                $endWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY)->endOfDay();
                $weekOfferings = $baseOfferings = CourseOffering::query()
                    ->whereIn('id', $offeringIds)
                    ->where('is_cancelled', false)
                    ->with('schedules')
                    ->get()
                    ->filter(function ($o) use ($startWeek, $endWeek) {
                        $from = $o->ngay_bat_dau_hoc ? $o->ngay_bat_dau_hoc->startOfDay() : null;
                        $to = $o->ngay_ket_thuc_hoc ? $o->ngay_ket_thuc_hoc->endOfDay() : null;
                        if ($from && $to) {
                            return $from->lte($endWeek) && $to->gte($startWeek);
                        }
                        return true;
                    })
                    ->values();

                $weekClassCount = (int) $weekOfferings->count();
            }
        }

        $totalCredits = (int) ($offerings->sum(fn ($o) => (int) ($o->subject?->so_tin_chi ?? 0)));
        $passedCredits = (int) ($offerings->sum(function ($o) use ($gradesByOffering) {
            if (!$o->grades_finalized_at) return 0;
            $g = $gradesByOffering[$o->id] ?? null;
            $tk = $g?->diem_tong_ket;
            if ($tk === null || $tk === '') return 0;
            $n = (float) str_replace(',', '.', (string) $tk);
            return $n >= 5 ? (int) ($o->subject?->so_tin_chi ?? 0) : 0;
        }));

        $resultsChartLabels = [];
        $resultsChartValues = [];
        $resultsChartAverages = [];
        $offeringsForResults = $offeringsKq ?? collect();
        if ($offeringsForResults->isNotEmpty()) {
            $finalizedOfferingIds = $offeringsForResults
                ->filter(fn ($o) => (bool) $o->grades_finalized_at)
                ->pluck('id')
                ->values();

            $avgByOffering = collect();
            if ($finalizedOfferingIds->isNotEmpty()) {
                $avgByOffering = CourseOfferingGrade::query()
                    ->selectRaw('course_offering_id, AVG(diem_tong_ket) as avg_tk')
                    ->whereIn('course_offering_id', $finalizedOfferingIds)
                    ->whereNotNull('diem_tong_ket')
                    ->groupBy('course_offering_id')
                    ->get()
                    ->keyBy('course_offering_id');
            }

            foreach ($offeringsForResults as $o) {
                if (!$o->grades_finalized_at) continue;

                $label = (string) ($o->subject?->ten_mon_hoc ?? $o->ten_hoc_phan ?? $o->subject?->ma_mon_hoc ?? '—');
                $resultsChartLabels[] = $label;

                $g = $gradesByOffering[$o->id] ?? null;
                $tk = $g?->diem_tong_ket;
                $studentVal = 0.0;
                if ($tk !== null && $tk !== '') {
                    $studentVal = (float) str_replace(',', '.', (string) $tk);
                }
                $resultsChartValues[] = round($studentVal, 2);

                $avgRow = $avgByOffering[$o->id] ?? null;
                $avgVal = $avgRow?->avg_tk;
                $resultsChartAverages[] = round((float) ($avgVal ?? 0), 2);
            }
        }

        $programTotalCredits = 167;

        return view('student.dashboard', compact(
            'user',
            'student',
            'lopTen',
            'remindersCount',
            'weekClassCount',
            'weekExamCount',
            'offerings',
            'offeringsLhp',
            'gradesByOffering',
            'hocKyOptions',
            'selectedDotKq',
            'selectedDotLhp',
            'totalCredits',
            'passedCredits',
            'programTotalCredits',
            'resultsChartLabels',
            'resultsChartValues',
            'resultsChartAverages',
        ));
    }

    public function profile()
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();
        return view('student.profile', compact('user', 'student'));
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
            'lop' => 'nullable|string|max:50|exists:lops,ma_lop',
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
        $student = Student::where('email', $user->email)->first();

        $dateParam = $request->query('date');
        $currentDate = $dateParam ? Carbon::parse($dateParam) : Carbon::now();

        $offerings = collect();
        $thGroupIndexByOfferingId = [];
        if ($student) {
            $offeringIds = SubjectRegistration::query()
                ->where('student_id', $student->id)
                ->where('status', '!=', 'cancelled')
                ->whereNotNull('course_offering_id')
                ->pluck('course_offering_id')
                ->unique()
                ->values();

            if ($offeringIds->isNotEmpty()) {
                $thGroupIndexByOfferingId = SubjectRegistration::query()
                    ->where('student_id', $student->id)
                    ->where('status', '!=', 'cancelled')
                    ->whereIn('course_offering_id', $offeringIds)
                    ->pluck('th_group_index', 'course_offering_id')
                    ->map(fn ($v) => $v === null ? null : (int) $v)
                    ->all();

                $offerings = CourseOffering::query()
                    ->whereIn('id', $offeringIds)
                    ->with(['subject', 'classRoom', 'teacherLyThuyet', 'teacherThucHanh', 'schedules.teacher'])
                    ->get();
            }
        }

        $scheduleGrid = OfferingWeekCalendar::buildGrid($offerings, $currentDate->copy(), $thGroupIndexByOfferingId);

        return view('student.schedule', compact('user', 'currentDate', 'scheduleGrid'));
    }

    public function schedulePdf(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();
        if (! $student) {
            abort(404);
        }

        $range = (string) $request->query('range', 'week'); // week|month
        if (! in_array($range, ['week', 'month'], true)) {
            $range = 'week';
        }

        $dateParam = $request->query('date');
        $baseDate = $dateParam ? Carbon::parse($dateParam) : Carbon::now();

        $from = $range === 'month'
            ? $baseDate->copy()->startOfMonth()->startOfDay()
            : $baseDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $to = $range === 'month'
            ? $baseDate->copy()->endOfMonth()->endOfDay()
            : $baseDate->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $offerings = collect();
        $thGroupIndexByOfferingId = SubjectRegistration::query()
            ->where('student_id', $student->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('course_offering_id')
            ->pluck('th_group_index', 'course_offering_id')
            ->map(fn ($v) => $v === null ? null : (int) $v)
            ->all();
        $offeringIds = SubjectRegistration::query()
            ->where('student_id', $student->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('course_offering_id')
            ->pluck('course_offering_id')
            ->unique()
            ->values();

        if ($offeringIds->isNotEmpty()) {
            $offerings = CourseOffering::query()
                ->whereIn('id', $offeringIds)
                ->whereDate('ngay_bat_dau_hoc', '<=', $to->toDateString())
                ->whereDate('ngay_ket_thuc_hoc', '>=', $from->toDateString())
                ->with(['subject', 'classRoom', 'teacherLyThuyet', 'teacherThucHanh', 'schedules.teacher'])
                ->get();
        }

        $weeks = [];
        if ($range === 'month') {
            $cursor = $from->copy()->startOfWeek(Carbon::MONDAY);
            $endCursor = $to->copy()->endOfWeek(Carbon::SUNDAY);
            while ($cursor->lte($endCursor)) {
                $weeks[] = [
                    'currentDate' => $cursor->copy(),
                    'scheduleGrid' => OfferingWeekCalendar::buildGrid($offerings, $cursor->copy(), $thGroupIndexByOfferingId),
                ];
                $cursor->addWeek();
            }
        } else {
            $weeks[] = [
                'currentDate' => $from->copy(),
                'scheduleGrid' => OfferingWeekCalendar::buildGrid($offerings, $from->copy(), $thGroupIndexByOfferingId),
            ];
        }

        $fileName = $range === 'month'
            ? ('Lich_hoc_thang_' . $baseDate->format('Y-m') . '.pdf')
            : ('Lich_hoc_tuan_' . $from->format('Y-m-d') . '_' . $to->format('Y-m-d') . '.pdf');

        return Pdf::loadView('student.schedule-pdf', [
            'student' => $student,
            'range' => $range,
            'baseDate' => $baseDate,
            'from' => $from,
            'to' => $to,
            'weeks' => $weeks,
        ])->setPaper('a4', 'landscape')->download($fileName);
    }

    public function results()
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

        $offerings = collect();
        $gradesByOffering = collect();

        if ($student) {
            $offeringIds = SubjectRegistration::query()
                ->where('student_id', $student->id)
                ->where('status', '!=', 'cancelled')
                ->whereNotNull('course_offering_id')
                ->pluck('course_offering_id')
                ->unique()
                ->values();

            if ($offeringIds->isNotEmpty()) {
                $offerings = CourseOffering::query()
                    ->whereIn('id', $offeringIds)
                    ->where('is_cancelled', false)
                    ->with(['subject', 'classRoom'])
                    ->orderByRaw('khoa_hoc is null')
                    ->orderBy('khoa_hoc')
                    ->orderByRaw('hoc_ky is null')
                    ->orderBy('hoc_ky')
                    ->orderByRaw('ngay_bat_dau_hoc is null')
                    ->orderBy('ngay_bat_dau_hoc')
                    ->orderBy('created_at')
                    ->get();

                $gradesByOffering = CourseOfferingGrade::query()
                    ->whereIn('course_offering_id', $offerings->pluck('id'))
                    ->where('student_id', $student->id)
                    ->get()
                    ->keyBy('course_offering_id');
            }
        }

        return view('student.results', compact('user', 'student', 'offerings', 'gradesByOffering'));
    }

    public function resultsPdf()
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();
        if (! $student) {
            abort(404);
        }

        $lopTen = null;
        if ($student->lop) {
            $lopTen = Lop::query()->where('ma_lop', $student->lop)->value('ten_lop');
        }

        $offerings = collect();
        $gradesByOffering = collect();

        $offeringIds = SubjectRegistration::query()
            ->where('student_id', $student->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('course_offering_id')
            ->pluck('course_offering_id')
            ->unique()
            ->values();

        if ($offeringIds->isNotEmpty()) {
            $offerings = CourseOffering::query()
                ->whereIn('id', $offeringIds)
                ->where('is_cancelled', false)
                ->with(['subject', 'classRoom'])
                ->orderByRaw('khoa_hoc is null')
                ->orderBy('khoa_hoc')
                ->orderByRaw('hoc_ky is null')
                ->orderBy('hoc_ky')
                ->orderByRaw('ngay_bat_dau_hoc is null')
                ->orderBy('ngay_bat_dau_hoc')
                ->orderBy('created_at')
                ->get();

            $gradesByOffering = CourseOfferingGrade::query()
                ->whereIn('course_offering_id', $offerings->pluck('id'))
                ->where('student_id', $student->id)
                ->get()
                ->keyBy('course_offering_id');
        }

        $fileName = 'Bang_diem_' . ($student->mssv ?? 'sinhvien') . '_' . now()->format('Ymd-His') . '.pdf';

        return Pdf::loadView('student.results-pdf', [
            'user' => $user,
            'student' => $student,
            'lopTen' => $lopTen,
            'offerings' => $offerings,
            'gradesByOffering' => $gradesByOffering,
        ])->setPaper('a4', 'landscape')->download($fileName);
    }

    public function registration()
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();
        $studentLop = null;
        if ($student && $student->lop) {
            $studentLop = Lop::where('ma_lop', $student->lop)->first();
        }

        $today = Carbon::today();

        // Filter by current academic year (3 semesters)
        $currentKhoaHoc = (string) (CourseOffering::query()
            ->whereNotNull('khoa_hoc')
            ->where('khoa_hoc', '!=', '')
            ->orderByDesc('khoa_hoc')
            ->value('khoa_hoc') ?? '');
        if (trim($currentKhoaHoc) === '') {
            $month = (int) $today->format('n');
            $year = (int) $today->format('Y');
            $academicStart = $month >= 8 ? $year : ($year - 1);
            $currentKhoaHoc = $academicStart . '-' . ($academicStart + 1);
        }

        $month = (int) $today->format('n');
        $defaultHocKy = $month >= 8 ? '1' : ($month <= 5 ? '2' : '3');
        $selectedHocKy = (string) request()->query('hoc_ky', $defaultHocKy);
        if (! in_array($selectedHocKy, ['1', '2', '3'], true)) {
            $selectedHocKy = $defaultHocKy;
        }

        $hocKyOptions = [
            ['value' => '1', 'label' => 'Học kỳ 1 ' . $currentKhoaHoc],
            ['value' => '2', 'label' => 'Học kỳ 2 ' . $currentKhoaHoc],
            ['value' => '3', 'label' => 'Học kỳ 3 ' . $currentKhoaHoc],
        ];

        $offerings = CourseOffering::with([
            'subject',
            'classRoom',
            'classRoomThucHanh',
            'teacherLyThuyet',
            'teacherThucHanh',
            'schedules.teacher',
            'schedules.classRoom',
        ])
            ->where('is_cancelled', false)
            ->whereRaw('TRIM(khoa_hoc) = ?', [$currentKhoaHoc])
            ->where('hoc_ky', (int) $selectedHocKy)
            ->withCount([
                'subjectRegistrations as registrations_count' => function ($q) {
                    $q->where('status', '!=', 'cancelled');
                }
            ])
            ->orderByDesc('created_at')
            ->get();

        // Count registrations per offering per TH group
        $thCountsByOffering = SubjectRegistration::query()
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('course_offering_id')
            ->whereNotNull('th_group_index')
            ->selectRaw('course_offering_id, th_group_index, COUNT(*) as cnt')
            ->groupBy('course_offering_id', 'th_group_index')
            ->get()
            ->groupBy('course_offering_id')
            ->map(function ($rows) {
                return $rows->keyBy(fn ($r) => (int) $r->th_group_index)
                    ->map(fn ($r) => (int) $r->cnt)
                    ->all();
            })
            ->all();

        $myRegs = collect();
        $myRegisteredOfferings = collect();
        if ($student) {
            $myRegs = SubjectRegistration::where('student_id', $student->id)
                ->whereNotNull('course_offering_id')
                ->get()
                ->keyBy('course_offering_id');

            $myOfferingIdsInTerm = SubjectRegistration::query()
                ->where('student_id', $student->id)
                ->where('status', '!=', 'cancelled')
                ->whereNotNull('course_offering_id')
                ->pluck('course_offering_id')
                ->unique()
                ->values();

            if ($myOfferingIdsInTerm->isNotEmpty()) {
                $myRegisteredOfferings = CourseOffering::query()
                    ->whereIn('id', $myOfferingIdsInTerm)
                    ->where('is_cancelled', false)
                    ->whereRaw('TRIM(khoa_hoc) = ?', [$currentKhoaHoc])
                    ->where('hoc_ky', (int) $selectedHocKy)
                    ->with(['subject', 'classRoom', 'teacherLyThuyet', 'teacherThucHanh'])
                    ->orderByDesc('created_at')
                    ->get();
            }
        }

        return view('student.registration', compact(
            'user',
            'student',
            'studentLop',
            'offerings',
            'myRegs',
            'today',
            'thCountsByOffering',
            'hocKyOptions',
            'selectedHocKy',
            'currentKhoaHoc',
            'myRegisteredOfferings'
        ));
    }

    public function registerOffering(Request $request, $courseOfferingId)
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();
        if (!$student) {
            return back()->with('error', 'Không tìm thấy hồ sơ học sinh.');
        }

        $offering = CourseOffering::withCount([
            'subjectRegistrations as registrations_count' => function ($q) {
                $q->where('status', '!=', 'cancelled');
            }
        ])->with(['schedules', 'subject'])->findOrFail($courseOfferingId);

        if ($offering->is_cancelled) {
            return back()->with('error', $offering->cancel_reason ?: 'Học phần đã bị hủy.');
        }

        $today = Carbon::today();
        if ($offering->ngay_mo_dang_ky && $offering->ngay_mo_dang_ky->gt($today)) {
            return back()->with('error', 'Chưa đến thời gian mở đăng ký.');
        }
        if ($offering->ngay_ket_thuc_dang_ky && $offering->ngay_ket_thuc_dang_ky->lt($today)) {
            return back()->with('error', 'Đã hết thời gian đăng ký.');
        }

        $conLai = (int) $offering->si_so_lop - (int) $offering->registrations_count;
        if ($conLai <= 0) {
            return back()->with('error', 'Lớp đã đủ sĩ số.');
        }

        // Chặn đăng ký trùng cùng môn học trong cùng học kỳ/khóa học
        if ($offering->subject_id) {
            $sameTermSameSubjectReg = SubjectRegistration::query()
                ->where('student_id', $student->id)
                ->where('status', '!=', 'cancelled')
                ->where('subject_id', $offering->subject_id)
                ->where('course_offering_id', '!=', $offering->id)
                ->whereHas('courseOffering', function ($q) use ($offering) {
                    $q->where('is_cancelled', false)
                        ->where('hoc_ky', $offering->hoc_ky)
                        ->where('khoa_hoc', $offering->khoa_hoc);
                })
                ->with(['courseOffering.subject'])
                ->orderByDesc('id')
                ->first();

            if ($sameTermSameSubjectReg) {
                $other = $sameTermSameSubjectReg->courseOffering;
                $mon = (string) ($other?->subject?->ten_mon_hoc ?? $offering->subject?->ten_mon_hoc ?? 'môn này');
                $hp = (string) ($other?->ten_hoc_phan ?? 'học phần khác');
                $hocKyLabel = $offering->hoc_ky ? ('học kỳ ' . $offering->hoc_ky) : 'kỳ hiện tại';
                $khoaHocLabel = $offering->khoa_hoc ? (' năm học ' . $offering->khoa_hoc) : '';

                return back()->with(
                    'error',
                    'Không thể đăng ký trùng môn "' . $mon . '" trong ' . $hocKyLabel . $khoaHocLabel
                    . '. Bạn đã đăng ký học phần "' . $hp . '" của môn này rồi.'
                );
            }
        }

        // Chặn đăng ký lại nếu sinh viên đang học (chưa kết thúc) cùng môn học này ở học phần khác
        if ($offering->subject_id) {
            $activeSameSubjectReg = SubjectRegistration::query()
                ->where('student_id', $student->id)
                ->where('status', '!=', 'cancelled')
                ->where('subject_id', $offering->subject_id)
                ->where('course_offering_id', '!=', $offering->id)
                ->whereHas('courseOffering', function ($q) use ($today) {
                    $q->where('is_cancelled', false)
                        // Chưa finalize điểm -> coi như chưa kết thúc
                        ->whereNull('grades_finalized_at')
                        // Nếu có ngày kết thúc học: phải qua ngày mới được đăng ký lại
                        ->where(function ($qq) use ($today) {
                            $qq->whereNull('ngay_ket_thuc_hoc')
                                ->orWhereDate('ngay_ket_thuc_hoc', '>=', $today->toDateString());
                        });
                })
                ->with(['courseOffering.subject'])
                ->orderByDesc('id')
                ->first();

            if ($activeSameSubjectReg) {
                $other = $activeSameSubjectReg->courseOffering;
                $mon = (string) ($other?->subject?->ten_mon_hoc ?? $offering->subject?->ten_mon_hoc ?? 'môn này');
                $hp = (string) ($other?->ten_hoc_phan ?? 'học phần hiện tại');

                $from = $other?->ngay_bat_dau_hoc ? $other->ngay_bat_dau_hoc->format('d/m/Y') : null;
                $to = $other?->ngay_ket_thuc_hoc ? $other->ngay_ket_thuc_hoc->format('d/m/Y') : null;

                $timeStr = '';
                if ($from && $to) {
                    $timeStr = ' (thời gian học: ' . $from . ' - ' . $to . ')';
                } elseif ($to) {
                    $timeStr = ' (dự kiến kết thúc: ' . $to . ')';
                }

                return back()->with(
                    'error',
                    'Không thể đăng ký lại môn "' . $mon . '" vì bạn đang học môn này ở học phần "' . $hp . '"' . $timeStr . '. Vui lòng đăng ký lại sau khi kết thúc thời gian học.'
                );
            }
        }

        $roomTh = collect($offering->schedules ?? collect())
            ->where('loai', 'thuc_hanh')
            ->sortBy('id')
            ->values();
        $thGroups = [];
        if ($offering->thu_thuc_hanh && $offering->tiet_thuc_hanh) {
            $thGroups[] = [
                'thu' => (int) $offering->thu_thuc_hanh,
                'tiet' => (string) $offering->tiet_thuc_hanh,
            ];
        }
        foreach ($roomTh as $sc) {
            if ($sc->thu && $sc->tiet) {
                $thGroups[] = ['thu' => (int) $sc->thu, 'tiet' => (string) $sc->tiet];
            }
        }
        $hasTh = count($thGroups) > 0;

        $selectedThGroupIndex = $request->input('th_group_index');
        $selectedThGroupIndex = is_null($selectedThGroupIndex) ? null : (int) $selectedThGroupIndex;
        if ($hasTh) {
            if (! $selectedThGroupIndex || $selectedThGroupIndex < 1 || $selectedThGroupIndex > count($thGroups)) {
                return back()->with('error', 'Vui lòng chọn nhóm thực hành.');
            }
        } else {
            $selectedThGroupIndex = null;
        }

        // Chặn trùng lịch học với các học phần đã đăng ký (LT + nhóm TH đã chọn) nếu thời gian học giao nhau
        $myRegsByOfferingId = SubjectRegistration::query()
            ->where('student_id', $student->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('course_offering_id')
            ->get()
            ->keyBy('course_offering_id');

        $myOfferingIds = $myRegsByOfferingId->keys();
        if ($myOfferingIds->isNotEmpty()) {
            $myOfferings = CourseOffering::query()
                ->whereIn('id', $myOfferingIds)
                ->with('schedules')
                ->get();

            $newSlots = [];
            // LT slots (all)
            $newSlots = CourseOfferingScheduleConflictService::slotsFromRequestArrays(
                [$offering->thu_ly_thuyet, ...collect($offering->schedules ?? [])->where('loai', 'ly_thuyet')->sortBy('id')->pluck('thu')->all()],
                [$offering->tiet_ly_thuyet, ...collect($offering->schedules ?? [])->where('loai', 'ly_thuyet')->sortBy('id')->pluck('tiet')->all()],
                [$hasTh ? ($thGroups[$selectedThGroupIndex - 1]['thu'] ?? null) : null],
                [$hasTh ? ($thGroups[$selectedThGroupIndex - 1]['tiet'] ?? '') : '']
            );
            $weekdays = CourseOffering::weekdays();

            // Chặn trường hợp bản thân học phần bị trùng lịch (LT trùng TH của nhóm đã chọn)
            if ($hasTh) {
                $ltOnly = CourseOfferingScheduleConflictService::slotsFromRequestArrays(
                    [$offering->thu_ly_thuyet, ...collect($offering->schedules ?? [])->where('loai', 'ly_thuyet')->sortBy('id')->pluck('thu')->all()],
                    [$offering->tiet_ly_thuyet, ...collect($offering->schedules ?? [])->where('loai', 'ly_thuyet')->sortBy('id')->pluck('tiet')->all()],
                    [],
                    []
                );
                $thOnly = CourseOfferingScheduleConflictService::slotsFromRequestArrays(
                    [],
                    [],
                    [($thGroups[$selectedThGroupIndex - 1]['thu'] ?? null)],
                    [($thGroups[$selectedThGroupIndex - 1]['tiet'] ?? '')]
                );
                foreach ($ltOnly as $lt) {
                    foreach ($thOnly as $th) {
                        if ($lt['thu'] !== $th['thu']) {
                            continue;
                        }
                        $intersect = array_values(array_intersect($lt['periods'], $th['periods']));
                        if ($intersect === []) {
                            continue;
                        }
                        sort($intersect);
                        $tietStr = implode(', ', $intersect);
                        $thuLabel = $weekdays[$lt['thu']] ?? ('Thứ '.$lt['thu']);

                        return back()->with('error', 'Học phần này bị trùng lịch nội bộ: Lý thuyết trùng với Thực hành ('.$thuLabel.', tiết '.$tietStr.'). Vui lòng báo admin chỉnh lại lịch.');
                    }
                }
            }

            foreach ($myOfferings as $other) {
                if ((int) $other->id === (int) $offering->id) {
                    continue;
                }

                $startA = $offering->ngay_bat_dau_hoc;
                $endA = $offering->ngay_ket_thuc_hoc;
                $startB = $other->ngay_bat_dau_hoc;
                $endB = $other->ngay_ket_thuc_hoc;

                // Nếu thiếu ngày học, vẫn check theo thứ/tiết (an toàn hơn, tránh lọt trùng lịch)
                if ($startA && $endA && $startB && $endB) {
                    // Không giao nhau về thời gian học -> không cần so lịch tuần
                    if ($startA->gt($endB) || $endA->lt($startB)) {
                        continue;
                    }
                }

                $regOther = $myRegsByOfferingId->get($other->id);
                $otherThIndex = $regOther?->th_group_index ? (int) $regOther->th_group_index : null;

                $otherThGroups = [];
                $otherThSchedules = collect($other->schedules ?? collect())->where('loai', 'thuc_hanh')->sortBy('id')->values();
                if ($other->thu_thuc_hanh && $other->tiet_thuc_hanh) {
                    $otherThGroups[] = ['thu' => (int) $other->thu_thuc_hanh, 'tiet' => (string) $other->tiet_thuc_hanh];
                }
                foreach ($otherThSchedules as $osc) {
                    if ($osc->thu && $osc->tiet) {
                        $otherThGroups[] = ['thu' => (int) $osc->thu, 'tiet' => (string) $osc->tiet];
                    }
                }
                $otherHasTh = count($otherThGroups) > 0;

                $otherThuTh = null;
                $otherTietTh = '';
                if ($otherHasTh && $otherThIndex && $otherThIndex >= 1 && $otherThIndex <= count($otherThGroups)) {
                    $otherThuTh = $otherThGroups[$otherThIndex - 1]['thu'];
                    $otherTietTh = $otherThGroups[$otherThIndex - 1]['tiet'];
                }

                // Nếu học phần cũ có TH nhưng chưa có nhóm TH đã chọn (th_group_index null),
                // thì check trùng với tất cả buổi TH của học phần đó để tránh lọt.
                if ($otherHasTh && ! $otherThIndex) {
                    $otherSlots = CourseOfferingScheduleConflictService::slotsFromOffering($other);
                } else {
                    $otherSlots = CourseOfferingScheduleConflictService::slotsFromRequestArrays(
                        [$other->thu_ly_thuyet, ...collect($other->schedules ?? [])->where('loai', 'ly_thuyet')->sortBy('id')->pluck('thu')->all()],
                        [$other->tiet_ly_thuyet, ...collect($other->schedules ?? [])->where('loai', 'ly_thuyet')->sortBy('id')->pluck('tiet')->all()],
                        [$otherThuTh],
                        [$otherTietTh]
                    );
                }

                foreach ($newSlots as $ns) {
                    foreach ($otherSlots as $os) {
                        if ($ns['thu'] !== $os['thu']) {
                            continue;
                        }
                        $intersect = array_values(array_intersect($ns['periods'], $os['periods']));
                        if ($intersect === []) {
                            continue;
                        }
                        sort($intersect);
                        $tietStr = implode(', ', $intersect);
                        $thuLabel = $weekdays[$ns['thu']] ?? ('Thứ '.$ns['thu']);

                        return back()->with(
                            'error',
                            'Trùng lịch học với học phần "'.$other->ten_hoc_phan.'" ('.$thuLabel.', tiết '.$tietStr.').'
                        );
                    }
                }
            }
        }

        $reg = SubjectRegistration::where('student_id', $student->id)
            ->where('course_offering_id', $offering->id)
            ->first();

        if ($reg && $reg->status !== 'cancelled') {
            return back()->with('success', 'Bạn đã đăng ký học phần này rồi.');
        }

        if ($reg) {
            $reg->update(['status' => 'approved', 'th_group_index' => $selectedThGroupIndex]);
        } else {
            SubjectRegistration::create([
                'course_offering_id' => $offering->id,
                'student_id' => $student->id,
                'subject_id' => $offering->subject_id,
                'class_room_id' => $offering->class_room_id,
                'th_group_index' => $selectedThGroupIndex,
                'status' => 'approved',
            ]);
        }

        return back()->with('success', 'Đăng ký học phần thành công.');
    }

    public function cancelOffering(Request $request, $courseOfferingId)
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();
        if (!$student) {
            return back()->with('error', 'Không tìm thấy hồ sơ học sinh.');
        }

        $reg = SubjectRegistration::where('student_id', $student->id)
            ->where('course_offering_id', $courseOfferingId)
            ->first();

        if (!$reg || $reg->status === 'cancelled') {
            return back()->with('success', 'Bạn chưa đăng ký học phần này.');
        }

        $reg->update(['status' => 'cancelled']);
        return back()->with('success', 'Đã hủy đăng ký học phần.');
    }

    public function notifications()
    {
        $user = Auth::user();
        return view('student.notifications', compact('user'));
    }
}
