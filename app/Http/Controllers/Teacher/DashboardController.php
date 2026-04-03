<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CourseOffering;
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
            ->where('teacher_id', $teacher->id)
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
        $offerings = CourseOffering::query()
            ->where('teacher_id', $teacher->id)
            ->whereDate('ngay_bat_dau_hoc', '<=', $today)
            ->with(['subject', 'classRoom', 'schedules'])
            ->withCount([
                'subjectRegistrations as enrolled_count' => function ($q) {
                    $q->where('status', '!=', 'cancelled');
                },
            ])
            ->orderByDesc('ngay_bat_dau_hoc')
            ->orderByDesc('created_at')
            ->get();

        return view('teacher.grading', compact('teacher', 'offerings'));
    }

    public function offeringRoster(Request $request, CourseOffering $courseOffering)
    {
        $teacher = $this->currentTeacher();
        if (! $teacher) {
            abort(403, 'Không tìm thấy hồ sơ giáo viên.');
        }
        if ((int) $courseOffering->teacher_id !== (int) $teacher->id) {
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

    public function schedule(Request $request)
    {
        $user = Auth::user();
        $teacher = $this->currentTeacher();

        $dateParam = $request->query('date');
        $currentDate = $dateParam ? Carbon::parse($dateParam) : Carbon::now();

        $offerings = collect();
        if ($teacher) {
            $offerings = CourseOffering::query()
                ->where('teacher_id', $teacher->id)
                ->with(['subject', 'classRoom', 'schedules'])
                ->get();
        }

        $scheduleGrid = OfferingWeekCalendar::buildGrid($offerings, $currentDate->copy());

        return view('teacher.schedule', compact('user', 'teacher', 'currentDate', 'scheduleGrid'));
    }
}
