<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CourseOffering;
use App\Models\CourseOfferingGrade;
use App\Models\SubjectRegistration;
use App\Models\Teacher;
use App\Support\OfferingWeekCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Giáo viên đăng nhập qua User.username = Teacher.msgv.
     */
    protected function currentTeacher(): ?Teacher
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'teacher') {
            return null;
        }

        return Teacher::where('msgv', $user->username)->first();
    }

    public function index()
    {
        $user = Auth::user();
        $teacher = $this->currentTeacher();

        return view('teacher.dashboard', compact('user', 'teacher'));
    }

    public function myClasses()
    {
        $teacher = $this->currentTeacher();
        if (! $teacher) {
            abort(403, 'Không tìm thấy hồ sơ giáo viên gắn với tài khoản này (MSGV / username).');
        }

        $offerings = CourseOffering::query()
            ->where(function ($q) use ($teacher) {
                $q->where('teacher_id_ly_thuyet', $teacher->id)
                    ->orWhere('teacher_id_thuc_hanh', $teacher->id)
                    ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacher->id));
            })
            ->with(['subject', 'classRoom', 'schedules'])
            ->withCount([
                'subjectRegistrations as enrolled_count' => function ($q) {
                    $q->where('status', '!=', 'cancelled');
                },
            ])
            ->orderByDesc('created_at')
            ->get();

        return view('teacher.my-classes', compact('teacher', 'offerings'));
    }

    /**
     * Học phần đã đến hoặc qua ngày bắt đầu học (để chấm điểm / danh sách lớp đang diễn ra).
     */
    public function grading()
    {
        $teacher = $this->currentTeacher();
        if (! $teacher) {
            abort(403, 'Không tìm thấy hồ sơ giáo viên gắn với tài khoản này (MSGV / username).');
        }

        $today = Carbon::today();
        $baseQuery = CourseOffering::query()
            ->where(function ($q) use ($teacher) {
                $q->where('teacher_id_ly_thuyet', $teacher->id)
                    ->orWhere('teacher_id_thuc_hanh', $teacher->id)
                    ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacher->id));
            })
            ->with(['subject', 'classRoom', 'schedules'])
            ->withCount([
                'subjectRegistrations as enrolled_count' => function ($q) {
                    $q->where('status', '!=', 'cancelled');
                },
            ]);

        // 1) Các lớp đang trong thời gian đăng ký (có thể chưa bắt đầu học)
        $offeringsRegister = (clone $baseQuery)
            ->whereDate('ngay_mo_dang_ky', '<=', $today)
            ->whereDate('ngay_ket_thuc_dang_ky', '>=', $today)
            ->orderByDesc('ngay_mo_dang_ky')
            ->orderByDesc('created_at')
            ->get();

        // 2) Các lớp đang học (đã bắt đầu và chưa kết thúc)
        $offeringsStudy = (clone $baseQuery)
            ->whereDate('ngay_bat_dau_hoc', '<=', $today)
            ->whereDate('ngay_ket_thuc_hoc', '>=', $today)
            ->orderByDesc('ngay_bat_dau_hoc')
            ->orderByDesc('created_at')
            ->get();

        return view('teacher.grading', compact('teacher', 'offeringsRegister', 'offeringsStudy'));
    }

    public function offeringRoster(Request $request, CourseOffering $courseOffering)
    {
        $teacher = $this->currentTeacher();
        if (! $teacher) {
            abort(403, 'Không tìm thấy hồ sơ giáo viên.');
        }
        $isAssigned = ((int) $courseOffering->teacher_id_ly_thuyet === (int) $teacher->id)
            || ((int) $courseOffering->teacher_id_thuc_hanh === (int) $teacher->id)
            || $courseOffering->schedules()->where('teacher_id', $teacher->id)->exists();
        if (! $isAssigned) {
            abort(403, 'Bạn không được phân công dạy học phần này.');
        }

        $courseOffering->load(['subject', 'classRoom', 'schedules']);

        $registrations = SubjectRegistration::query()
            ->where('course_offering_id', $courseOffering->id)
            ->where('status', '!=', 'cancelled')
            ->with('student')
            ->orderBy('created_at')
            ->get();

        $fromGrading = $request->query('from') === 'grading';
        $rosterListUrl = $fromGrading ? route('teacher.grading') : route('teacher.my-classes');
        $rosterListLabel = $fromGrading ? 'Chấm điểm' : 'Lớp học của tôi';

        return view('teacher.offering-roster', compact(
            'teacher',
            'courseOffering',
            'registrations',
            'rosterListUrl',
            'rosterListLabel'
        ));
    }

    public function gradingClass(CourseOffering $courseOffering)
    {
        $teacher = $this->currentTeacher();
        if (! $teacher) {
            abort(403, 'Không tìm thấy hồ sơ giáo viên.');
        }

        $isAssigned = ((int) $courseOffering->teacher_id_ly_thuyet === (int) $teacher->id)
            || ((int) $courseOffering->teacher_id_thuc_hanh === (int) $teacher->id)
            || $courseOffering->schedules()->where('teacher_id', $teacher->id)->exists();
        if (! $isAssigned) {
            abort(403, 'Bạn không được phân công dạy học phần này.');
        }

        $courseOffering->load(['subject', 'classRoom']);

        $registrations = SubjectRegistration::query()
            ->where('course_offering_id', $courseOffering->id)
            ->where('status', '!=', 'cancelled')
            ->with('student')
            ->orderBy('created_at')
            ->get();

        $grades = CourseOfferingGrade::query()
            ->where('course_offering_id', $courseOffering->id)
            ->get()
            ->keyBy('student_id');

        return view('teacher.grading-class', compact('teacher', 'courseOffering', 'registrations', 'grades'));
    }

    public function saveGrades(Request $request, CourseOffering $courseOffering)
    {
        $teacher = $this->currentTeacher();
        if (! $teacher) {
            abort(403, 'Không tìm thấy hồ sơ giáo viên.');
        }
        $isAssigned = ((int) $courseOffering->teacher_id_ly_thuyet === (int) $teacher->id)
            || ((int) $courseOffering->teacher_id_thuc_hanh === (int) $teacher->id)
            || $courseOffering->schedules()->where('teacher_id', $teacher->id)->exists();
        if (! $isAssigned) {
            abort(403, 'Bạn không được phân công dạy học phần này.');
        }

        $rows = $request->input('rows', []);
        if (! is_array($rows)) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.'], 422);
        }

        foreach ($rows as $row) {
            $studentId = (int) ($row['student_id'] ?? 0);
            if ($studentId <= 0) {
                continue;
            }
            CourseOfferingGrade::updateOrCreate(
                ['course_offering_id' => $courseOffering->id, 'student_id' => $studentId],
                [
                    'thuong_xuyen' => $row['thuong_xuyen'] ?? null,
                    'thuc_hanh' => $row['thuc_hanh'] ?? null,
                    'giua_ky' => $row['giua_ky'] ?? null,
                    'cuoi_ky' => $row['cuoi_ky'] ?? null,
                    'diem_tong_ket' => $row['diem_tong_ket'] ?? null,
                    'thang_diem_4' => $row['thang_diem_4'] ?? null,
                    'diem_chu' => $row['diem_chu'] ?? null,
                    'xep_loai' => $row['xep_loai'] ?? null,
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Đã lưu điểm.']);
    }

    public function schedule(Request $request)
    {
        $user = Auth::user();
        $teacher = $this->currentTeacher();

        $dateParam = $request->query('date');
        $currentDate = $dateParam ? Carbon::parse($dateParam) : Carbon::now();

        $offerings = collect();
        if ($teacher) {
            $offerings = CourseOffering::query()
                ->where(function ($q) use ($teacher) {
                    $q->where('teacher_id_ly_thuyet', $teacher->id)
                        ->orWhere('teacher_id_thuc_hanh', $teacher->id)
                        ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacher->id));
                })
                ->with(['subject', 'classRoom', 'schedules'])
                ->get();
        }

        $scheduleGrid = OfferingWeekCalendar::buildGrid($offerings, $currentDate->copy());

        return view('teacher.schedule', compact('user', 'teacher', 'currentDate', 'scheduleGrid'));
    }
}
