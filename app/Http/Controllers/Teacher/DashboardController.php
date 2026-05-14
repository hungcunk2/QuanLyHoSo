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
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CourseOfferingGradesExport;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    protected function toFloatOrNull(mixed $v): ?float
    {
        if ($v === null) return null;
        if ($v === '') return null;
        if (is_string($v)) {
            $v = str_replace(',', '.', trim($v));
        }
        if (! is_numeric($v)) return null;
        return (float) $v;
    }

    protected function avg(array $values): ?float
    {
        $nums = [];
        foreach ($values as $v) {
            $f = $this->toFloatOrNull($v);
            if ($f === null) continue;
            $nums[] = $f;
        }
        if (count($nums) === 0) return null;
        return array_sum($nums) / count($nums);
    }

    protected function computeDiemTongKetIUH(?float $txAvg, ?float $thAvg, ?float $gk, ?float $ck): ?float
    {
        if ($txAvg === null || $thAvg === null || $gk === null || $ck === null) {
            return null;
        }
        return round(0.2 * $txAvg + 0.2 * $gk + 0.2 * $thAvg + 0.4 * $ck, 2);
    }

    protected function clamp(?float $n, float $min, float $max): ?float
    {
        if ($n === null) return null;
        if ($n < $min) return $min;
        if ($n > $max) return $max;
        return $n;
    }

    protected function formatDecimal(?float $n, int $scale = 2): ?string
    {
        if ($n === null) return null;
        return number_format($n, $scale, '.', '');
    }

    protected function convertTo4AndLetter(?float $diemTongKet): array
    {
        if ($diemTongKet === null) {
            return [null, null, null];
        }

        $d = $diemTongKet;
        if ($d >= 8.5) return [4.00, 'A', 'Giỏi'];
        if ($d >= 8.0) return [3.50, 'B+', 'Giỏi'];
        if ($d >= 7.0) return [3.00, 'B', 'Khá'];
        if ($d >= 6.5) return [2.50, 'C+', 'Khá'];
        if ($d >= 5.5) return [2.00, 'C', 'Trung bình'];
        if ($d >= 5.0) return [1.50, 'D+', 'Trung bình'];
        if ($d >= 4.0) return [1.00, 'D', 'Yếu'];
        return [0.00, 'F', 'Kém'];
    }
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

        $remindersCount = 0;
        $weekClassCount = 0;
        $weekExamCount = 0;

        if ($user && $user->role === 'teacher') {
            $userId = (int) ($user->id ?? 0);
            if ($userId > 0) {
                $readIds = DB::table('announcement_reads')
                    ->where('user_id', $userId)
                    ->pluck('announcement_id')
                    ->all();

                $remindersCount = (int) \App\Models\Announcement::query()
                    ->whereIn('audience', ['all', 'teacher'])
                    ->when(count($readIds) > 0, fn ($q) => $q->whereNotIn('id', $readIds))
                    ->count();
            }
        }

        if ($teacher) {
            $startWeek = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $endWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY)->endOfDay();
            $today = Carbon::today();

            $offeringsAll = CourseOffering::query()
                ->where(function ($q) use ($teacher) {
                    $q->where('teacher_id_ly_thuyet', $teacher->id)
                        ->orWhere('teacher_id_thuc_hanh', $teacher->id)
                        ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacher->id));
                })
                ->get()
                ->values();

            $weekOfferings = $offeringsAll->filter(function ($o) use ($startWeek, $endWeek) {
                    $from = $o->ngay_bat_dau_hoc ? $o->ngay_bat_dau_hoc->startOfDay() : null;
                    $to = $o->ngay_ket_thuc_hoc ? $o->ngay_ket_thuc_hoc->endOfDay() : null;
                    if ($from && $to) {
                        return $from->lte($endWeek) && $to->gte($startWeek);
                    }
                    return true;
                })
                ->values();

            $weekClassCount = (int) $weekOfferings->count();

            $offeringsStudy = $offeringsAll
                ->filter(function ($o) use ($today) {
                    $from = $o->ngay_bat_dau_hoc ? $o->ngay_bat_dau_hoc->startOfDay() : null;
                    $to = $o->ngay_ket_thuc_hoc ? $o->ngay_ket_thuc_hoc->endOfDay() : null;
                    if ($from && $to) {
                        return $from->lte($today) && $to->gte($today);
                    }
                    return false;
                })
                ->values();
            $weekExamCount = (int) $offeringsStudy->count();
        }

        return view('teacher.dashboard', compact(
            'user',
            'teacher',
            'remindersCount',
            'weekClassCount',
            'weekExamCount',
        ));
    }

    public function profile()
    {
        $user = Auth::user();
        $teacher = $this->currentTeacher();
        return view('teacher.profile', compact('user', 'teacher'));
    }

    public function editProfile()
    {
        $user = Auth::user();
        $teacher = $this->currentTeacher();
        if (! $teacher) {
            return redirect()->route('teacher.dashboard')->with('message', 'Chưa có hồ sơ giáo viên.');
        }
        return view('teacher.profile_edit', compact('user', 'teacher'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $teacher = $this->currentTeacher();
        if (! $teacher) {
            return redirect()->route('teacher.dashboard')->with('message', 'Chưa có hồ sơ giáo viên.');
        }

        $request->validate([
            'ho_ten' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:teachers,email,' . $teacher->id . '|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'ngay_sinh' => 'nullable|date',
            'sdt' => 'nullable|string|max:20',
            'dia_chi' => 'nullable|string',
            'chuyen_mon' => 'nullable|string|max:255',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'avatar.image' => 'File phải là ảnh (jpeg, png, jpg, gif, webp).',
            'avatar.max' => 'Ảnh tối đa 2MB.',
        ]);

        if ($request->hasFile('avatar')) {
            if ($teacher->avatar && Storage::disk('public')->exists($teacher->avatar)) {
                Storage::disk('public')->delete($teacher->avatar);
            }
            Storage::disk('public')->makeDirectory('avatars/teachers');
            $path = $request->file('avatar')->store('avatars/teachers', 'public');
            $teacher->avatar = $path;
        }

        $fillable = array_diff($teacher->getFillable(), ['msgv', 'avatar']);
        $teacher->fill($request->only($fillable));
        $teacher->save();

        if ($user->email !== $request->email) {
            User::where('id', $user->id)->update(['email' => $request->email]);
        }

        return redirect()->route('teacher.profile')->with('success', 'Cập nhật thông tin thành công.');
    }

    public function notifications()
    {
        $user = Auth::user();
        $teacher = $this->currentTeacher();

        return view('teacher.notifications', compact('user', 'teacher'));
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

        $offeringsRegister = (clone $baseQuery)
            ->whereDate('ngay_mo_dang_ky', '<=', $today)
            ->whereDate('ngay_ket_thuc_dang_ky', '>=', $today)
            ->orderByDesc('ngay_mo_dang_ky')
            ->orderByDesc('created_at')
            ->get();

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

        $lopNameByCode = \App\Models\Lop::query()->pluck('ten_lop', 'ma_lop')->all();

        $fromGrading = $request->query('from') === 'grading';
        $rosterListUrl = $fromGrading ? route('teacher.grading') : route('teacher.my-classes');
        $rosterListLabel = $fromGrading ? 'Chấm điểm' : 'Lớp học của tôi';

        return view('teacher.offering-roster', compact(
            'teacher',
            'courseOffering',
            'registrations',
            'lopNameByCode',
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
        if ($courseOffering->grades_finalized_at) {
            abort(403, 'Học phần này đã chốt điểm nên không thể chỉnh sửa.');
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

            $txAvg = $this->avg((array) ($row['thuong_xuyen'] ?? []));
            $thAvg = $this->avg((array) ($row['thuc_hanh'] ?? []));
            $gk = $this->clamp($this->toFloatOrNull($row['giua_ky'] ?? null), 0, 10);
            $ck = $this->clamp($this->toFloatOrNull($row['cuoi_ky'] ?? null), 0, 10);
            $diemTongKet = $this->computeDiemTongKetIUH($txAvg, $thAvg, $gk, $ck);

            $txIn = (array) ($row['thuong_xuyen'] ?? []);
            foreach ($txIn as $k => $v) {
                $txIn[$k] = $this->clamp($this->toFloatOrNull($v), 0, 10);
            }
            $thIn = (array) ($row['thuc_hanh'] ?? []);
            foreach ($thIn as $k => $v) {
                $thIn[$k] = $this->clamp($this->toFloatOrNull($v), 0, 10);
            }

            CourseOfferingGrade::updateOrCreate(
                ['course_offering_id' => $courseOffering->id, 'student_id' => $studentId],
                [
                    'thuong_xuyen' => $txIn ?: null,
                    'thuc_hanh' => $thIn ?: null,
                    'giua_ky' => $this->formatDecimal($gk),
                    'cuoi_ky' => $this->formatDecimal($ck),
                    'diem_tong_ket' => $this->formatDecimal($diemTongKet)
                        ?? $this->formatDecimal($this->clamp($this->toFloatOrNull($row['diem_tong_ket'] ?? null), 0, 10)),
                    'thang_diem_4' => $this->formatDecimal(
                        $this->clamp($this->toFloatOrNull($row['thang_diem_4'] ?? null), 0, 4)
                    ),
                    'diem_chu' => $row['diem_chu'] ?? null,
                    'xep_loai' => $row['xep_loai'] ?? null,
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Đã lưu điểm.']);
    }

    public function finalizeGrades(Request $request, CourseOffering $courseOffering)
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
        if ($courseOffering->grades_finalized_at) {
            return response()->json(['success' => true, 'message' => 'Học phần đã chốt điểm trước đó.']);
        }

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

        foreach ($registrations as $reg) {
            $s = $reg->student;
            if (! $s) continue;
            $g = $grades[$s->id] ?? null;
            $tx = is_array($g?->thuong_xuyen) ? $g->thuong_xuyen : [];
            $th = is_array($g?->thuc_hanh) ? $g->thuc_hanh : [];

            $txAvg = $this->avg($tx);
            $thAvg = $this->avg($th);
            $gk = $this->toFloatOrNull($g?->giua_ky);
            $ck = $this->toFloatOrNull($g?->cuoi_ky);
            $diemTongKet = $this->computeDiemTongKetIUH($txAvg, $thAvg, $gk, $ck);

            [$thang4, $diemChu, $xepLoai] = $this->convertTo4AndLetter($diemTongKet);

            CourseOfferingGrade::updateOrCreate(
                ['course_offering_id' => $courseOffering->id, 'student_id' => $s->id],
                [
                    'diem_tong_ket' => $this->formatDecimal($diemTongKet),
                    'thang_diem_4' => $this->formatDecimal($thang4),
                    'diem_chu' => $diemChu,
                    'xep_loai' => $xepLoai,
                ]
            );
        }

        $courseOffering->forceFill(['grades_finalized_at' => now()])->save();

        return response()->json(['success' => true, 'message' => 'Đã chốt điểm và khóa chỉnh sửa cho học phần này.']);
    }

    public function exportGradesXlsx(CourseOffering $courseOffering)
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

        $filename = 'bang-diem-'.$courseOffering->id.'-'.now()->format('Ymd-His').'.xlsx';
        return Excel::download(new CourseOfferingGradesExport($courseOffering), $filename);
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
                ->where('is_cancelled', false)
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

    public function schedulePdf(Request $request)
    {
        $teacher = $this->currentTeacher();
        if (! $teacher) {
            abort(404);
        }

        $range = (string) $request->query('range', 'week');
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

        $offerings = CourseOffering::query()
            ->where('is_cancelled', false)
            ->where(function ($q) use ($teacher) {
                $q->where('teacher_id_ly_thuyet', $teacher->id)
                    ->orWhere('teacher_id_thuc_hanh', $teacher->id)
                    ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacher->id));
            })
            ->whereDate('ngay_bat_dau_hoc', '<=', $to->toDateString())
            ->whereDate('ngay_ket_thuc_hoc', '>=', $from->toDateString())
            ->with(['subject', 'classRoom', 'schedules'])
            ->get();

        $weeks = [];
        if ($range === 'month') {
            $cursor = $from->copy()->startOfWeek(Carbon::MONDAY);
            $endCursor = $to->copy()->endOfWeek(Carbon::SUNDAY);
            while ($cursor->lte($endCursor)) {
                $weeks[] = [
                    'currentDate' => $cursor->copy(),
                    'scheduleGrid' => OfferingWeekCalendar::buildGrid($offerings, $cursor->copy()),
                ];
                $cursor->addWeek();
            }
        } else {
            $weeks[] = [
                'currentDate' => $from->copy(),
                'scheduleGrid' => OfferingWeekCalendar::buildGrid($offerings, $from->copy()),
            ];
        }

        $fileName = $range === 'month'
            ? ('Lich_day_thang_' . $baseDate->format('Y-m') . '.pdf')
            : ('Lich_day_tuan_' . $from->format('Y-m-d') . '_' . $to->format('Y-m-d') . '.pdf');

        return Pdf::loadView('teacher.schedule-pdf', [
            'teacher' => $teacher,
            'range' => $range,
            'baseDate' => $baseDate,
            'from' => $from,
            'to' => $to,
            'weeks' => $weeks,
        ])->setPaper('a4', 'landscape')->download($fileName);
    }
}
