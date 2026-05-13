<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\ClassRoom;
use App\Models\CourseOffering;
use App\Models\CourseOfferingGrade;
use App\Models\Lop;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectRegistration;
use App\Models\Teacher;
use App\Models\User;
use App\Support\OfferingWeekCalendar;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AiChatContextService
{
    public function buildForUser(User $user, string $message): string
    {
        return match ($user->role) {
            'student' => $this->buildStudentContext($user, $message),
            'teacher' => $this->buildTeacherContext($user, $message),
            'admin' => $this->buildAdminContext($user, $message),
            default => "Khong xac dinh duoc vai tro nguoi dung hien tai.\nCau hoi: {$message}",
        };
    }

    protected function buildStudentContext(User $user, string $message): string
    {
        $student = Student::query()
            ->where('email', $user->email)
            ->orWhere('mssv', $user->username)
            ->first();

        if (! $student) {
            return 'Khong tim thay ho so sinh vien gan voi tai khoan nay.';
        }

        $sections = [];
        $sections[] = implode("\n", array_filter([
            'Vai tro: sinh vien',
            'Ho ten: '.$student->ho_ten,
            'MSSV: '.($student->mssv ?: 'khong ro'),
            'Email: '.($student->email ?: $user->email),
            'Lop: '.($student->lop ?: 'khong ro'),
            'Nganh: '.($student->nganh ?: 'khong ro'),
            'Khoa hoc: '.($student->khoa_hoc ?: 'khong ro'),
        ]));

        $offeringIds = SubjectRegistration::query()
            ->where('student_id', $student->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('course_offering_id')
            ->pluck('course_offering_id')
            ->unique()
            ->values();

        $offerings = collect();
        $gradesByOffering = collect();

        if ($offeringIds->isNotEmpty()) {
            $offerings = CourseOffering::query()
                ->whereIn('id', $offeringIds)
                ->where('is_cancelled', false)
                ->with(['subject', 'classRoom', 'classRoomThucHanh', 'teacherLyThuyet', 'teacherThucHanh', 'schedules.teacher', 'schedules.classRoom'])
                ->get();

            $gradesByOffering = CourseOfferingGrade::query()
                ->whereIn('course_offering_id', $offerings->pluck('id'))
                ->where('student_id', $student->id)
                ->get()
                ->keyBy('course_offering_id');
        }

        $sections[] = implode("\n", [
            'Tong quan hoc tap:',
            '- So hoc phan da dang ky: '.$offerings->count(),
            '- So hoc phan da co diem chot: '.$offerings->filter(fn ($o) => (bool) $o->grades_finalized_at)->count(),
            '- So tin chi dang hoc/da dang ky: '.$offerings->sum(fn ($o) => (int) ($o->subject?->so_tin_chi ?? 0)),
        ]);

        if ($this->messageHasAny($message, ['lich', 'thoi khoa bieu', 'hoc hom nay', 'hoc tuan nay']) || $sections === []) {
            $scheduleLines = $offerings
                ->sortBy(fn ($o) => (string) ($o->subject?->ten_mon_hoc ?? $o->ten_hoc_phan ?? ''))
                ->take(8)
                ->map(fn (CourseOffering $offering) => '- '.$this->formatOfferingForChat($offering))
                ->values();

            $sections[] = $scheduleLines->isNotEmpty()
                ? "Lich hoc/mon dang theo:\n".$scheduleLines->implode("\n")
                : 'Lich hoc/mon dang theo: chua co hoc phan nao.';
        }

        if ($this->messageHasAny($message, ['diem', 'ket qua', 'bang diem', 'gpa', 'tong ket']) || $offerings->isNotEmpty()) {
            $gradeLines = $offerings
                ->filter(fn ($o) => (bool) $o->grades_finalized_at)
                ->sortByDesc(fn ($o) => (string) ($o->ngay_ket_thuc_hoc?->format('Y-m-d') ?? $o->created_at?->format('Y-m-d') ?? ''))
                ->take(8)
                ->map(function (CourseOffering $offering) use ($gradesByOffering): string {
                    $grade = $gradesByOffering->get($offering->id);

                    return sprintf(
                        '- %s | tong ket: %s | diem chu: %s | xep loai: %s',
                        $offering->subject?->ten_mon_hoc ?? $offering->ten_hoc_phan ?? 'Hoc phan',
                        $grade?->diem_tong_ket ?? 'chua co',
                        $grade?->diem_chu ?? 'chua co',
                        $grade?->xep_loai ?? 'chua co'
                    );
                })
                ->values();

            if ($gradeLines->isNotEmpty()) {
                $sections[] = "Ket qua hoc tap gan day:\n".$gradeLines->implode("\n");
            }
        }

        if ($this->messageHasAny($message, ['dang ky', 'hoc phan', 'mon hoc']) || $offerings->isNotEmpty()) {
            $registrationLines = $offerings
                ->sortByDesc(fn ($o) => (string) ($o->created_at?->format('Y-m-d H:i:s') ?? ''))
                ->take(8)
                ->map(function (CourseOffering $offering): string {
                    $subject = $offering->subject?->ten_mon_hoc ?? $offering->ten_hoc_phan ?? 'Hoc phan';
                    $credits = (int) ($offering->subject?->so_tin_chi ?? 0);

                    return '- '.$subject.' | hoc ky '.($offering->hoc_ky ?? '?').' | khoa hoc '.($offering->khoa_hoc ?? '?').' | '.$credits.' tin chi';
                })
                ->values();

            $sections[] = $registrationLines->isNotEmpty()
                ? "Hoc phan dang ky:\n".$registrationLines->implode("\n")
                : 'Hoc phan dang ky: chua co du lieu.';
        }

        $announcementLines = $this->studentAnnouncementLines($student);
        if ($announcementLines->isNotEmpty()) {
            $sections[] = "Thong bao lien quan:\n".$announcementLines->map(fn ($line) => '- '.$line)->implode("\n");
        }

        return implode("\n\n", $sections);
    }

    protected function buildTeacherContext(User $user, string $message): string
    {
        $teacher = Teacher::query()
            ->where('email', $user->email)
            ->orWhere('msgv', $user->username)
            ->first();

        if (! $teacher) {
            return 'Khong tim thay ho so giao vien gan voi tai khoan nay.';
        }

        $offerings = CourseOffering::query()
            ->where('is_cancelled', false)
            ->where(function ($query) use ($teacher) {
                $query->where('teacher_id_ly_thuyet', $teacher->id)
                    ->orWhere('teacher_id_thuc_hanh', $teacher->id)
                    ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacher->id));
            })
            ->with(['subject', 'classRoom', 'classRoomThucHanh', 'schedules.classRoom'])
            ->withCount([
                'subjectRegistrations as enrolled_count' => function ($query) {
                    $query->where('status', '!=', 'cancelled');
                },
            ])
            ->get();

        $sections = [];
        $sections[] = implode("\n", array_filter([
            'Vai tro: giao vien',
            'Ho ten: '.$teacher->ho_ten,
            'MSGV: '.($teacher->msgv ?: 'khong ro'),
            'Email: '.($teacher->email ?: $user->email),
            'Chuyen mon: '.($teacher->chuyen_mon ?: 'khong ro'),
        ]));

        $sections[] = implode("\n", [
            'Tong quan giang day:',
            '- Tong hoc phan duoc phan cong: '.$offerings->count(),
            '- Hoc phan da chot diem: '.$offerings->filter(fn ($o) => (bool) $o->grades_finalized_at)->count(),
            '- Hoc phan chua chot diem: '.$offerings->filter(fn ($o) => ! $o->grades_finalized_at)->count(),
            '- Tong so sinh vien dang theo hoc phan: '.$offerings->sum(fn ($o) => (int) ($o->enrolled_count ?? 0)),
        ]);

        $today = Carbon::today();
        $todaySchedule = $this->buildScheduleLinesForDate($offerings, $today);
        $weekSchedule = $this->buildWeekScheduleLines($offerings, $today);
        $sections[] = 'Hom nay la '.$today->format('d/m/Y').' ('.$this->formatVietnameseWeekday($today).').';
        $sections[] = $todaySchedule !== []
            ? "Lich day hom nay:\n".collect($todaySchedule)->map(fn ($line) => '- '.$line)->implode("\n")
            : 'Lich day hom nay: khong co tiet day nao trong du lieu lich hien tai.';
        $sections[] = $weekSchedule !== []
            ? "Lich day tuan nay:\n".collect($weekSchedule)->map(fn ($line) => '- '.$line)->implode("\n")
            : 'Lich day tuan nay: khong co lich day nao trong tuan hien tai.';

        $terms = $this->extractSearchTerms($message);
        $matchedOfferings = $terms === []
            ? $offerings
            : $offerings->filter(function (CourseOffering $offering) use ($terms): bool {
                $haystack = $this->normalized(implode(' ', [
                    $offering->ten_hoc_phan,
                    $offering->subject?->ten_mon_hoc,
                    $offering->subject?->ma_mon_hoc,
                    $offering->classRoom?->ten_lop,
                    $offering->classRoom?->ma_lop,
                ]));

                foreach ($terms as $term) {
                    if (str_contains($haystack, $this->normalized($term))) {
                        return true;
                    }
                }

                return false;
            })->values();

        if ($this->messageHasAny($message, ['lich', 'day', 'thoi khoa bieu']) || $matchedOfferings->isNotEmpty()) {
            $lines = $matchedOfferings
                ->sortBy(fn ($o) => (string) ($o->subject?->ten_mon_hoc ?? $o->ten_hoc_phan ?? ''))
                ->take(8)
                ->map(fn (CourseOffering $offering) => '- '.$this->formatOfferingForChat($offering, true))
                ->values();

            $sections[] = $lines->isNotEmpty()
                ? "Lich day va hoc phan phu trach:\n".$lines->implode("\n")
                : 'Lich day va hoc phan phu trach: chua tim thay hoc phan phu hop.';
        }

        if ($this->messageHasAny($message, ['lop', 'sinh vien', 'roster', 'phu trach']) || $matchedOfferings->isNotEmpty()) {
            $rosterLines = $matchedOfferings
                ->sortByDesc(fn ($o) => (int) ($o->enrolled_count ?? 0))
                ->take(8)
                ->map(function (CourseOffering $offering): string {
                    return sprintf(
                        '- %s | phong/lop: %s | so SV: %d | chot diem: %s',
                        $offering->subject?->ten_mon_hoc ?? $offering->ten_hoc_phan ?? 'Hoc phan',
                        $offering->classRoom?->ten_lop ?? $offering->classRoom?->ma_lop ?? 'chua ro',
                        (int) ($offering->enrolled_count ?? 0),
                        $offering->grades_finalized_at ? 'da chot' : 'chua chot'
                    );
                })
                ->values();

            $sections[] = $rosterLines->isNotEmpty()
                ? "Lop hoc phan va si so:\n".$rosterLines->implode("\n")
                : 'Lop hoc phan va si so: chua co du lieu.';
        }

        $announcementLines = Announcement::query()
            ->whereIn('audience', ['all', 'teacher'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Announcement $announcement) => $announcement->title.($announcement->summary ? ' - '.$announcement->summary : ''));

        if ($announcementLines->isNotEmpty()) {
            $sections[] = "Thong bao lien quan:\n".$announcementLines->map(fn ($line) => '- '.$line)->implode("\n");
        }

        return implode("\n\n", $sections);
    }

    protected function buildAdminContext(User $user, string $message): string
    {
        $sections = [];
        $sections[] = implode("\n", [
            'Vai tro: quan tri vien',
            'Tai khoan: '.($user->username ?: $user->email),
            'Tong quan he thong:',
            '- Sinh vien: '.Student::query()->count(),
            '- Giao vien: '.Teacher::query()->count(),
            '- Lop hanh chinh: '.Lop::query()->count(),
            '- Phong hoc: '.ClassRoom::query()->count(),
            '- Mon hoc: '.Subject::query()->count(),
            '- Hoc phan dang mo/khong huy: '.CourseOffering::query()->where('is_cancelled', false)->count(),
            '- Hoc phan da huy: '.CourseOffering::query()->where('is_cancelled', true)->count(),
            '- Hoc phan da chot diem: '.CourseOffering::query()->whereNotNull('grades_finalized_at')->count(),
        ]);

        $offeringsByDot = CourseOffering::query()
            ->where('is_cancelled', false)
            ->select('hoc_ky', 'khoa_hoc', DB::raw('COUNT(*) as cnt'))
            ->groupBy('hoc_ky', 'khoa_hoc')
            ->orderByDesc('khoa_hoc')
            ->orderByDesc('hoc_ky')
            ->limit(6)
            ->get()
            ->map(fn ($row) => '- Hoc ky '.($row->hoc_ky ?? '?').' | khoa hoc '.($row->khoa_hoc ?? '?').' | '.$row->cnt.' hoc phan');

        if ($offeringsByDot->isNotEmpty()) {
            $sections[] = "Thong ke hoc phan theo dot:\n".$offeringsByDot->implode("\n");
        }

        $searchSections = $this->buildAdminLookupSections($message);
        if ($searchSections !== []) {
            $sections = array_merge($sections, $searchSections);
        }

        $announcementLines = Announcement::query()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Announcement $announcement) => $announcement->title.($announcement->summary ? ' - '.$announcement->summary : ''));

        if ($announcementLines->isNotEmpty()) {
            $sections[] = "Thong bao gan day:\n".$announcementLines->map(fn ($line) => '- '.$line)->implode("\n");
        }

        return implode("\n\n", $sections);
    }

    /**
     * @return array<int, string>
     */
    protected function buildAdminLookupSections(string $message): array
    {
        $terms = $this->extractSearchTerms($message);

        if ($terms === []) {
            return [];
        }

        $sections = [];

        $students = Student::query()
            ->where(function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $query->orWhere('ho_ten', 'like', '%'.$term.'%')
                        ->orWhere('mssv', 'like', '%'.$term.'%')
                        ->orWhere('ma_ho_so', 'like', '%'.$term.'%')
                        ->orWhere('email', 'like', '%'.$term.'%')
                        ->orWhere('lop', 'like', '%'.$term.'%');
                }
            })
            ->limit(5)
            ->get();

        if ($students->isNotEmpty()) {
            $sections[] = "Sinh vien tim thay:\n".$students
                ->map(fn (Student $student) => sprintf(
                    '- %s | MSSV: %s | lop: %s | email: %s',
                    $student->ho_ten ?? 'Chua ro ten',
                    $student->mssv ?? 'chua co',
                    $student->lop ?? 'chua co',
                    $student->email ?? 'chua co'
                ))
                ->implode("\n");
        }

        $teachers = Teacher::query()
            ->where(function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $query->orWhere('ho_ten', 'like', '%'.$term.'%')
                        ->orWhere('msgv', 'like', '%'.$term.'%')
                        ->orWhere('email', 'like', '%'.$term.'%')
                        ->orWhere('chuyen_mon', 'like', '%'.$term.'%');
                }
            })
            ->limit(5)
            ->get();

        if ($teachers->isNotEmpty()) {
            $sections[] = "Giao vien tim thay:\n".$teachers
                ->map(fn (Teacher $teacher) => sprintf(
                    '- %s | MSGV: %s | chuyen mon: %s | email: %s',
                    $teacher->ho_ten ?? 'Chua ro ten',
                    $teacher->msgv ?? 'chua co',
                    $teacher->chuyen_mon ?? 'chua co',
                    $teacher->email ?? 'chua co'
                ))
                ->implode("\n");
        }

        $subjects = Subject::query()
            ->where(function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $query->orWhere('ten_mon_hoc', 'like', '%'.$term.'%')
                        ->orWhere('ma_mon_hoc', 'like', '%'.$term.'%');
                }
            })
            ->limit(5)
            ->get();

        if ($subjects->isNotEmpty()) {
            $sections[] = "Mon hoc tim thay:\n".$subjects
                ->map(fn (Subject $subject) => sprintf(
                    '- %s | ma mon: %s | so tin chi: %s',
                    $subject->ten_mon_hoc ?? 'Chua ro ten',
                    $subject->ma_mon_hoc ?? 'chua co',
                    $subject->so_tin_chi ?? 'chua co'
                ))
                ->implode("\n");
        }

        $lops = Lop::query()
            ->where(function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $query->orWhere('ten_lop', 'like', '%'.$term.'%')
                        ->orWhere('ma_lop', 'like', '%'.$term.'%');
                }
            })
            ->limit(5)
            ->get();

        if ($lops->isNotEmpty()) {
            $sections[] = "Lop hanh chinh tim thay:\n".$lops
                ->map(fn (Lop $lop) => sprintf(
                    '- %s | ma lop: %s',
                    $lop->ten_lop ?? 'Chua ro ten',
                    $lop->ma_lop ?? 'chua co'
                ))
                ->implode("\n");
        }

        $classRooms = ClassRoom::query()
            ->where(function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $query->orWhere('ten_lop', 'like', '%'.$term.'%')
                        ->orWhere('ma_lop', 'like', '%'.$term.'%');
                }
            })
            ->limit(5)
            ->get();

        if ($classRooms->isNotEmpty()) {
            $sections[] = "Phong hoc/lop hoc phan tim thay:\n".$classRooms
                ->map(fn (ClassRoom $classRoom) => sprintf(
                    '- %s | ma phong/lop: %s',
                    $classRoom->ten_lop ?? 'Chua ro ten',
                    $classRoom->ma_lop ?? 'chua co'
                ))
                ->implode("\n");
        }

        return $sections;
    }

    /**
     * @return Collection<int, string>
     */
    protected function studentAnnouncementLines(Student $student): Collection
    {
        $offeringIds = SubjectRegistration::query()
            ->where('student_id', $student->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('course_offering_id')
            ->pluck('course_offering_id')
            ->unique()
            ->values()
            ->all();

        $targetedIds = ! empty($offeringIds)
            ? DB::table('announcement_offering_targets')
                ->whereIn('course_offering_id', $offeringIds)
                ->pluck('announcement_id')
                ->unique()
                ->values()
                ->all()
            : [];

        return Announcement::query()
            ->where(function ($query) use ($targetedIds) {
                $query->where('audience', 'all')
                    ->orWhere(function ($sq) use ($targetedIds) {
                        $sq->where('audience', 'student');
                        if ($targetedIds !== []) {
                            $sq->whereIn('id', $targetedIds);
                        } else {
                            $sq->whereRaw('1=0');
                        }
                    });
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Announcement $announcement) => $announcement->title.($announcement->summary ? ' - '.$announcement->summary : ''));
    }

    protected function formatOfferingForChat(CourseOffering $offering, bool $includeEnrollment = false): string
    {
        $offering->loadMissing(['subject', 'classRoom', 'classRoomThucHanh', 'teacherLyThuyet', 'teacherThucHanh', 'schedules.classRoom']);

        $name = $offering->subject?->ten_mon_hoc ?? $offering->ten_hoc_phan ?? 'Hoc phan';
        $slots = [];

        if ($offering->thu_ly_thuyet && $offering->tiet_ly_thuyet) {
            $slots[] = 'LT '.$this->formatDayAndPeriod((int) $offering->thu_ly_thuyet, (string) $offering->tiet_ly_thuyet);
        }

        if ($offering->thu_thuc_hanh && $offering->tiet_thuc_hanh) {
            $slots[] = 'TH '.$this->formatDayAndPeriod((int) $offering->thu_thuc_hanh, (string) $offering->tiet_thuc_hanh);
        }

        foreach ($offering->schedules->where('loai', '!=', 'tam_ngung')->take(4) as $schedule) {
            if (! $schedule->thu || ! $schedule->tiet) {
                continue;
            }

            $label = match ($schedule->loai) {
                'ly_thuyet' => 'LT',
                'thuc_hanh' => 'TH',
                default => strtoupper((string) $schedule->loai),
            };

            $room = $schedule->classRoom?->ten_lop ?? $schedule->classRoom?->ma_lop;
            $slots[] = trim($label.' '.$this->formatDayAndPeriod((int) $schedule->thu, (string) $schedule->tiet).' '.($room ? "| {$room}" : ''));
        }

        $chunks = [
            $name,
            'hoc ky '.($offering->hoc_ky ?? '?'),
            'khoa hoc '.($offering->khoa_hoc ?? '?'),
        ];

        if ($slots !== []) {
            $chunks[] = implode('; ', array_unique($slots));
        }

        $room = $offering->classRoom?->ten_lop ?? $offering->classRoom?->ma_lop;
        if ($room) {
            $chunks[] = 'lop/phong '.$room;
        }

        if ($includeEnrollment) {
            $chunks[] = 'so SV '.(int) ($offering->enrolled_count ?? 0);
        }

        return implode(' | ', $chunks);
    }

    protected function formatDayAndPeriod(int $thu, string $tiet): string
    {
        $weekday = CourseOffering::weekdays()[$thu] ?? ('Thu '.$thu);

        return $weekday.' tiet '.$tiet;
    }

    /**
     * @param  Collection<int, CourseOffering>  $offerings
     * @return array<int, string>
     */
    protected function buildScheduleLinesForDate(Collection $offerings, Carbon $date): array
    {
        $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
        $grid = OfferingWeekCalendar::buildGrid($offerings, $weekStart);
        $dayIndex = $weekStart->diffInDays($date->copy()->startOfDay());

        if ($dayIndex < 0 || $dayIndex > 6) {
            return [];
        }

        $lines = [];
        foreach (['morning', 'afternoon', 'evening'] as $bucket) {
            foreach ($grid[$bucket][$dayIndex] ?? [] as $entry) {
                $lines[] = $this->formatGridEntryForChat($entry);
            }
        }

        return array_values(array_unique($lines));
    }

    /**
     * @param  Collection<int, CourseOffering>  $offerings
     * @return array<int, string>
     */
    protected function buildWeekScheduleLines(Collection $offerings, Carbon $date): array
    {
        $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
        $grid = OfferingWeekCalendar::buildGrid($offerings, $weekStart);
        $lines = [];

        for ($dayIndex = 0; $dayIndex < 7; $dayIndex++) {
            $day = $weekStart->copy()->addDays($dayIndex);
            foreach (['morning', 'afternoon', 'evening'] as $bucket) {
                foreach ($grid[$bucket][$dayIndex] ?? [] as $entry) {
                    $lines[] = $this->formatVietnameseWeekday($day).': '.$this->formatGridEntryForChat($entry);
                }
            }
        }

        return array_values(array_unique($lines));
    }

    /**
     * @param  array{kind?: string, title?: string, meta?: string}  $entry
     */
    protected function formatGridEntryForChat(array $entry): string
    {
        $kindLabel = match ($entry['kind'] ?? 'study') {
            'exam' => 'Thi',
            'pause' => 'Tam ngung',
            default => 'Day',
        };

        return trim($kindLabel.': '.($entry['title'] ?? 'Tiet hoc').(! empty($entry['meta']) ? ' | '.$entry['meta'] : ''));
    }

    protected function formatVietnameseWeekday(Carbon $date): string
    {
        return CourseOffering::weekdays()[OfferingWeekCalendar::thuVnFromDate($date)] ?? $date->translatedFormat('l');
    }

    /**
     * @return array<int, string>
     */
    protected function extractSearchTerms(string $message): array
    {
        $parts = preg_split('/[^\p{L}\p{N}@._-]+/u', $message) ?: [];

        $stopWords = [
            'cho', 'toi', 'xem', 'kiem', 'tra', 'giup', 've', 'va', 'cua', 'cho', 'voi',
            'sinh', 'vien', 'giao', 'admin', 'quan', 'tri', 'mon', 'hoc', 'phan', 'lop',
            'phong', 'thong', 'bao', 'lich', 'diem', 'ket', 'qua', 'nay', 'kia', 'duoc',
            'day', 'tuan', 'hom', 'hien', 'tai', 'thoi', 'khoa', 'bieu', 'phu', 'trach',
            'chi', 'tiet', 'co', 'bao', 'nhieu', 'xin', 'chao', 'la', 'ai',
        ];

        $terms = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            $normalized = $this->normalized($part);
            if (mb_strlen($normalized) < 3 || in_array($normalized, $stopWords, true)) {
                continue;
            }

            $terms[$normalized] = $part;
        }

        return array_slice(array_values($terms), 0, 4);
    }

    protected function messageHasAny(string $message, array $keywords): bool
    {
        $normalized = $this->normalized($message);
        foreach ($keywords as $keyword) {
            if (str_contains($normalized, $this->normalized($keyword))) {
                return true;
            }
        }

        return false;
    }

    protected function normalized(?string $text): string
    {
        return Str::ascii(Str::lower((string) $text));
    }
}
