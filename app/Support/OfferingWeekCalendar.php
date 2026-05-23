<?php

namespace App\Support;

use App\Models\CourseOffering;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Lưới tuần (Sáng / Chiều / Tối × 7 ngày) từ lịch học phần trong khoảng ngày bắt đầu – kết thúc học.
 * Dời / tạm ngưng theo ngày (course_offering_schedules.ngay_ap_dung) chỉ áp dụng đúng ngày đó.
 */
class OfferingWeekCalendar
{
    /** Carbon: 0=CN … 6=T7 → thứ VN 2–8 (2=T2, 8=CN). */
    public static function thuVnFromDate(Carbon $date): int
    {
        $dow = (int) $date->dayOfWeek;

        return $dow === 0 ? 8 : $dow + 1;
    }

    public static function minPeriodFromTiet(?string $tiet): ?int
    {
        if ($tiet === null || $tiet === '') {
            return null;
        }
        foreach (preg_split('/[,]+/', $tiet) as $part) {
            $part = trim((string) $part);
            if ($part === '') {
                continue;
            }
            if (preg_match('/(\d+)/', $part, $m)) {
                $n = (int) $m[1];
                if ($n >= 1 && $n <= 16) {
                    return $n;
                }
            }
        }

        return null;
    }

    /**
     * @return list<int>
     */
    public static function parseTietPeriods(?string $tiet): array
    {
        if ($tiet === null || $tiet === '') {
            return [];
        }
        $out = [];
        foreach (preg_split('/[,]+/', $tiet) as $part) {
            $part = trim((string) $part);
            if ($part === '') {
                continue;
            }
            if (preg_match('/(\d+)/', $part, $m)) {
                $n = (int) $m[1];
                if ($n >= 1 && $n <= 16) {
                    $out[$n] = true;
                }
            }
        }

        return array_keys($out);
    }

    public static function tietPeriodsOverlap(?string $tietA, ?string $tietB): bool
    {
        $a = self::parseTietPeriods($tietA);
        $b = self::parseTietPeriods($tietB);
        if ($a === [] || $b === []) {
            return false;
        }
        $setA = array_fill_keys($a, true);
        foreach ($b as $p) {
            if (isset($setA[$p])) {
                return true;
            }
        }

        return false;
    }

    public static function sessionKeyFromMinPeriod(?int $period): string
    {
        if ($period === null) {
            return 'morning';
        }
        if ($period <= 6) {
            return 'morning';
        }
        if ($period <= 12) {
            return 'afternoon';
        }

        return 'evening';
    }

    /**
     * Buổi lặp cố định (không có ngay_ap_dung) — dùng tính ngày thi.
     *
     * @return list<array{loai: string, thu: int, tiet: string, thi_buoi_thu: int|null}>
     */
    public static function recurringSessionsForExams(CourseOffering $o): array
    {
        $o->loadMissing('schedules');
        $rows = [];

        if ($o->thu_ly_thuyet && $o->tiet_ly_thuyet) {
            $rows[] = [
                'loai' => 'ly_thuyet',
                'thu' => (int) $o->thu_ly_thuyet,
                'tiet' => (string) $o->tiet_ly_thuyet,
                'thi_buoi_thu' => $o->ngay_thi_ly_thuyet_buoi_thu ? (int) $o->ngay_thi_ly_thuyet_buoi_thu : null,
            ];
        }
        foreach ($o->schedules->where('loai', 'ly_thuyet') as $sc) {
            if ($sc->ngay_ap_dung) {
                continue;
            }
            if (! $sc->thu || ($sc->tiet ?? '') === '') {
                continue;
            }
            $rows[] = [
                'loai' => 'ly_thuyet',
                'thu' => (int) $sc->thu,
                'tiet' => (string) $sc->tiet,
                'thi_buoi_thu' => $sc->thi_buoi_thu ? (int) $sc->thi_buoi_thu : null,
            ];
        }

        if ($o->thu_thuc_hanh && $o->tiet_thuc_hanh) {
            $rows[] = [
                'loai' => 'thuc_hanh',
                'thu' => (int) $o->thu_thuc_hanh,
                'tiet' => (string) $o->tiet_thuc_hanh,
                'thi_buoi_thu' => $o->ngay_thi_thuc_hanh_buoi_thu ? (int) $o->ngay_thi_thuc_hanh_buoi_thu : null,
            ];
        }
        foreach ($o->schedules->where('loai', 'thuc_hanh') as $sc) {
            if ($sc->ngay_ap_dung) {
                continue;
            }
            if (! $sc->thu || ($sc->tiet ?? '') === '') {
                continue;
            }
            $rows[] = [
                'loai' => 'thuc_hanh',
                'thu' => (int) $sc->thu,
                'tiet' => (string) $sc->tiet,
                'thi_buoi_thu' => $sc->thi_buoi_thu ? (int) $sc->thi_buoi_thu : null,
            ];
        }

        return $rows;
    }

    /**
     * Các buổi hiển thị đúng một ngày (lặp tuần + ngoại lệ dời/tạm ngưng theo ngay_ap_dung).
     *
     * @return list<array{loai: string, thu: int, tiet: string, thi_buoi_thu: int|null, moved_from: string|null}>
     */
    public static function sessionsForDate(CourseOffering $o, Carbon $date, ?int $thGroupIndex = null): array
    {
        $o->loadMissing('schedules', 'classRoom', 'classRoomThucHanh');
        $thuVn = self::thuVnFromDate($date);
        $dateStr = $date->toDateString();
        $rows = [];

        $isPausedOnDate = function (string $sessionKey) use ($o, $dateStr): bool {
            return $o->schedules
                ->where('loai', 'tam_ngung')
                ->contains(function ($sc) use ($dateStr, $sessionKey) {
                    if (! $sc->ngay_ap_dung) {
                        return false;
                    }
                    if (Carbon::parse($sc->ngay_ap_dung)->toDateString() !== $dateStr) {
                        return false;
                    }

                    return (string) ($sc->paused_session_key ?? '') === $sessionKey;
                });
        };

        $isLegacyPausedSlot = function (int $thu, string $tiet) use ($o, $dateStr): bool {
            return $o->schedules
                ->where('loai', 'tam_ngung')
                ->contains(function ($sc) use ($dateStr, $thu, $tiet) {
                    if (! $sc->ngay_ap_dung) {
                        return false;
                    }
                    if (Carbon::parse($sc->ngay_ap_dung)->toDateString() !== $dateStr) {
                        return false;
                    }
                    if ((string) ($sc->paused_session_key ?? '') !== '') {
                        return false;
                    }

                    return (int) $sc->thu === $thu && (string) $sc->tiet === $tiet;
                });
        };

        // Bản ghi tạm ngưng cũ (không có ngay_ap_dung): không biết ngày → không hiển thị, không chặn cả kỳ.

        // Ngoại lệ theo ngày: tạm ngưng / buổi dời (chỉ đúng ngay_ap_dung).
        foreach ($o->schedules as $sc) {
            if (! $sc->ngay_ap_dung) {
                continue;
            }
            if (Carbon::parse($sc->ngay_ap_dung)->toDateString() !== $dateStr) {
                continue;
            }
            if (! $sc->thu || ($sc->tiet ?? '') === '') {
                continue;
            }
            $rows[] = [
                'loai' => (string) $sc->loai,
                'thu' => $thuVn,
                'tiet' => (string) $sc->tiet,
                'thi_buoi_thu' => null,
                'moved_from' => $sc->moved_from ?: null,
                'schedule_id' => (int) $sc->id,
            ];
        }

        // Lý thuyết lặp hàng tuần.
        if ($o->thu_ly_thuyet && ($o->tiet_ly_thuyet ?? '') !== '' && $thuVn === (int) $o->thu_ly_thuyet) {
            if (! $isPausedOnDate('base_lt') && ! $isLegacyPausedSlot((int) $o->thu_ly_thuyet, (string) $o->tiet_ly_thuyet)) {
                $rows[] = [
                    'loai' => 'ly_thuyet',
                    'thu' => (int) $o->thu_ly_thuyet,
                    'tiet' => (string) $o->tiet_ly_thuyet,
                    'thi_buoi_thu' => $o->ngay_thi_ly_thuyet_buoi_thu ? (int) $o->ngay_thi_ly_thuyet_buoi_thu : null,
                    'moved_from' => $o->lt_moved_from ?: null,
                    'schedule_id' => null,
                ];
            }
        }
        foreach ($o->schedules->where('loai', 'ly_thuyet') as $sc) {
            if ($sc->ngay_ap_dung || ($sc->moved_from && ! $sc->ngay_ap_dung)) {
                continue;
            }
            if ((int) $sc->thu !== $thuVn) {
                continue;
            }
            if ($o->thu_ly_thuyet && (int) $sc->thu === (int) $o->thu_ly_thuyet
                && (string) $sc->tiet === (string) $o->tiet_ly_thuyet) {
                continue;
            }
            $key = 'sc_'.$sc->id;
            if ($isPausedOnDate($key) || $isLegacyPausedSlot((int) $sc->thu, (string) $sc->tiet)) {
                continue;
            }
            $rows[] = [
                'loai' => 'ly_thuyet',
                'thu' => (int) $sc->thu,
                'tiet' => (string) $sc->tiet,
                'thi_buoi_thu' => $sc->thi_buoi_thu ? (int) $sc->thi_buoi_thu : null,
                'moved_from' => $sc->moved_from ?: null,
                'schedule_id' => (int) $sc->id,
            ];
        }

        // Thực hành lặp hàng tuần (có thể lọc theo nhóm SV).
        $thRows = [];
        if ($o->thu_thuc_hanh && ($o->tiet_thuc_hanh ?? '') !== '' && $thuVn === (int) $o->thu_thuc_hanh) {
            if (! $isPausedOnDate('base_th') && ! $isLegacyPausedSlot((int) $o->thu_thuc_hanh, (string) $o->tiet_thuc_hanh)) {
                $thRows[] = [
                    'loai' => 'thuc_hanh',
                    'thu' => (int) $o->thu_thuc_hanh,
                    'tiet' => (string) $o->tiet_thuc_hanh,
                    'thi_buoi_thu' => $o->ngay_thi_thuc_hanh_buoi_thu ? (int) $o->ngay_thi_thuc_hanh_buoi_thu : null,
                    'moved_from' => $o->th_moved_from ?: null,
                    'schedule_id' => null,
                ];
            }
        }
        foreach ($o->schedules->where('loai', 'thuc_hanh') as $sc) {
            if ($sc->ngay_ap_dung || ($sc->moved_from && ! $sc->ngay_ap_dung)) {
                continue;
            }
            if ((int) $sc->thu !== $thuVn) {
                continue;
            }
            if ($o->thu_thuc_hanh && (int) $sc->thu === (int) $o->thu_thuc_hanh
                && (string) $sc->tiet === (string) $o->tiet_thuc_hanh) {
                continue;
            }
            $key = 'sc_'.$sc->id;
            if ($isPausedOnDate($key) || $isLegacyPausedSlot((int) $sc->thu, (string) $sc->tiet)) {
                continue;
            }
            $thRows[] = [
                'loai' => 'thuc_hanh',
                'thu' => (int) $sc->thu,
                'tiet' => (string) $sc->tiet,
                'thi_buoi_thu' => $sc->thi_buoi_thu ? (int) $sc->thi_buoi_thu : null,
                'moved_from' => $sc->moved_from ?: null,
                'schedule_id' => (int) $sc->id,
            ];
        }
        if ($thGroupIndex !== null && $thGroupIndex >= 1 && $thGroupIndex <= count($thRows)) {
            $rows[] = $thRows[$thGroupIndex - 1];
        } else {
            $rows = array_merge($rows, $thRows);
        }

        return $rows;
    }

    /**
     * Metadata chọn buổi dời lịch (admin) — gắn vào ô lịch khi buildGrid(..., withRescheduleMeta: true).
     *
     * @param  array{loai: string, thu: int, tiet: string, schedule_id?: int|null}  $sess
     * @return array{session_key: string, selectable: bool, pick_label: string, date: string, thu: int, tiet: string}
     */
    /**
     * Danh sách buổi trong tuần cho modal dời lịch admin (JSON → JS vẽ lưới).
     *
     * @return list<array{key: string, loai: string, label: string, date: string, thu: int, tiet: string, teacher: string, room: string, moved_from: string|null, selectable: bool}>
     */
    public static function adminWeekSessions(CourseOffering $o, Carbon $anchorDate): array
    {
        $o->loadMissing('schedules.teacher', 'schedules.classRoom', 'classRoom', 'classRoomThucHanh', 'teacherLyThuyet', 'teacherThucHanh');
        $weekStart = $anchorDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $start = $o->ngay_bat_dau_hoc?->copy()->startOfDay();
        $end = $o->ngay_ket_thuc_hoc?->copy()->endOfDay();
        $sessions = [];

        for ($d = 0; $d < 7; $d++) {
            $date = $weekStart->copy()->addDays($d);
            if ($start && $date->lt($start)) {
                continue;
            }
            if ($end && $date->gt($end)) {
                continue;
            }

            foreach (self::sessionsForDate($o, $date) as $sess) {
                $meta = self::rescheduleMetaForSession($o, $sess, $date);
                if (($meta['session_key'] ?? '') === '') {
                    continue;
                }

                $scheduleId = $sess['schedule_id'] ?? null;
                $loai = (string) $sess['loai'];
                $teacher = '';
                $room = '';

                if ($loai === 'tam_ngung' && $scheduleId) {
                    $label = 'Tạm ngưng ('.$date->format('d/m/Y').')';
                } elseif ($scheduleId && $o->schedules->firstWhere('id', $scheduleId)?->ngay_ap_dung) {
                    $sc = $o->schedules->firstWhere('id', $scheduleId);
                    $teacher = $sc?->teacher?->ho_ten ?? '';
                    $room = $sc?->classRoom?->ma_lop ? ($sc->classRoom->ma_lop.' - '.$sc->classRoom->ten_lop) : '';
                    $label = $loai === 'ly_thuyet' ? 'Lý thuyết (Học bù)' : 'Thực hành (Học bù)';
                } elseif ($loai === 'ly_thuyet') {
                    if ($scheduleId === null) {
                        $teacher = $o->teacherLyThuyet?->ho_ten ?? '';
                        $room = $o->classRoom?->ma_lop ? ($o->classRoom->ma_lop.' - '.$o->classRoom->ten_lop) : '';
                    } else {
                        $sc = $o->schedules->firstWhere('id', $scheduleId);
                        $teacher = $sc?->teacher?->ho_ten ?? '';
                        $room = $o->classRoom?->ma_lop ? ($o->classRoom->ma_lop.' - '.$o->classRoom->ten_lop) : '';
                    }
                    $label = $meta['pick_label'];
                } else {
                    if ($scheduleId === null) {
                        $teacher = $o->teacherThucHanh?->ho_ten ?? '';
                        $room = $o->classRoomThucHanh?->ma_lop
                            ? ($o->classRoomThucHanh->ma_lop.' - '.$o->classRoomThucHanh->ten_lop)
                            : '';
                    } else {
                        $sc = $o->schedules->firstWhere('id', $scheduleId);
                        $teacher = $sc?->teacher?->ho_ten ?? '';
                        $room = $sc?->classRoom?->ma_lop ? ($sc->classRoom->ma_lop.' - '.$sc->classRoom->ten_lop) : '';
                    }
                    $label = $meta['pick_label'];
                }

                $sessions[] = [
                    'key' => $meta['session_key'],
                    'loai' => $loai,
                    'label' => $label,
                    'date' => $meta['date'],
                    'thu' => (int) $meta['thu'],
                    'tiet' => (string) $meta['tiet'],
                    'teacher' => $teacher,
                    'room' => $room,
                    'moved_from' => $sess['moved_from'] ?? null,
                    'selectable' => (bool) $meta['selectable'],
                ];
            }
        }

        return $sessions;
    }

    public static function rescheduleMetaForSession(CourseOffering $o, array $sess, Carbon $date): array
    {
        $scheduleId = $sess['schedule_id'] ?? null;
        $loai = (string) ($sess['loai'] ?? '');
        $base = [
            'date' => $date->toDateString(),
            'thu' => (int) $sess['thu'],
            'tiet' => (string) $sess['tiet'],
        ];

        if ($loai === 'tam_ngung' && $scheduleId) {
            return array_merge($base, [
                'session_key' => 'pause_'.$scheduleId,
                'selectable' => false,
                'pick_label' => 'Tạm ngưng ('.$date->format('d/m/Y').')',
            ]);
        }

        if ($scheduleId) {
            $sc = $o->schedules->firstWhere('id', $scheduleId);
            if ($sc && $sc->ngay_ap_dung) {
                $label = $loai === 'ly_thuyet' ? 'Lý thuyết (Học bù)' : 'Thực hành (Học bù)';

                return array_merge($base, [
                    'session_key' => 'one_'.$scheduleId,
                    'selectable' => false,
                    'pick_label' => $label,
                ]);
            }
        }

        if ($loai === 'ly_thuyet') {
            $key = $scheduleId === null ? 'base_lt' : 'sc_'.$scheduleId;

            return array_merge($base, [
                'session_key' => $key,
                'selectable' => true,
                'pick_label' => $scheduleId === null ? 'Lý thuyết' : 'Lý thuyết (buổi phụ)',
            ]);
        }

        if ($loai === 'thuc_hanh') {
            $key = $scheduleId === null ? 'base_th' : 'sc_'.$scheduleId;

            return array_merge($base, [
                'session_key' => $key,
                'selectable' => true,
                'pick_label' => $scheduleId === null ? 'Thực hành (Nhóm 1)' : 'Thực hành',
            ]);
        }

        return array_merge($base, [
            'session_key' => '',
            'selectable' => false,
            'pick_label' => '',
        ]);
    }

    /**
     * @deprecated Dùng sessionsForDate — giữ alias để tương thích nếu có chỗ gọi cũ.
     *
     * @return list<array{loai: string, thu: int, tiet: string, thi_buoi_thu: int|null, moved_from: string|null}>
     */
    public static function flattenOfferingSessions(CourseOffering $o, ?int $thGroupIndex = null): array
    {
        return self::recurringSessionsForExams($o);
    }

    public static function nthOccurrenceDate(Carbon $start, Carbon $end, int $thuVn, int $nth): ?Carbon
    {
        if ($nth < 1) {
            return null;
        }
        $d = $start->copy()->startOfDay();
        $count = 0;
        while ($d->lte($end)) {
            if (self::thuVnFromDate($d) === $thuVn) {
                $count++;
                if ($count === $nth) {
                    return $d->copy();
                }
            }
            $d->addDay();
        }

        return null;
    }

    /**
     * @param  Collection<int, CourseOffering>  $offerings
     * @return array{morning: array<int, list<array{kind: string, title: string, meta: string, badge: string, sort_period?: int}>>, afternoon: array, evening: array}
     */
    public static function buildGrid(
        Collection $offerings,
        Carbon $weekStart,
        array $thGroupIndexByOfferingId = [],
        bool $withRescheduleMeta = false
    ): array {
        $weekStart = $weekStart->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $withRescheduleMeta = $withRescheduleMeta && $offerings->count() === 1;

        $grid = [
            'morning' => array_fill(0, 7, []),
            'afternoon' => array_fill(0, 7, []),
            'evening' => array_fill(0, 7, []),
        ];

        foreach ($offerings as $offering) {
            $start = $offering->ngay_bat_dau_hoc?->copy()->startOfDay();
            $end = $offering->ngay_ket_thuc_hoc?->copy()->endOfDay();
            if (! $start || ! $end) {
                continue;
            }

            $thIdx = $thGroupIndexByOfferingId[(int) $offering->id] ?? null;
            $thIdx = $thIdx === null ? null : (int) $thIdx;
            $examSessions = self::recurringSessionsForExams($offering);
            $room = $offering->classRoom;

            $examDates = [];
            foreach ($examSessions as $idx => $sess) {
                $nth = $sess['thi_buoi_thu'] ?? null;
                if (! $nth) {
                    continue;
                }
                $examDate = self::nthOccurrenceDate($start, $end, (int) $sess['thu'], (int) $nth);
                if ($examDate) {
                    $examDates[$idx] = $examDate;
                }
            }

            for ($d = 0; $d < 7; $d++) {
                $date = $weekStart->copy()->addDays($d);
                if ($date->lt($start) || $date->gt($end)) {
                    continue;
                }

                $sessions = self::sessionsForDate($offering, $date, $thIdx);
                $thuVn = self::thuVnFromDate($date);

                foreach ($sessions as $sess) {
                    if ((int) $sess['thu'] !== $thuVn) {
                        continue;
                    }

                    if ($sess['loai'] !== 'tam_ngung') {
                        $hiddenByExam = false;
                        foreach ($examDates as $exIdx => $examDate) {
                            if (! $examDate->isSameDay($date)) {
                                continue;
                            }
                            $exSess = $examSessions[$exIdx];
                            if ((int) $exSess['thu'] === $thuVn
                                && self::tietPeriodsOverlap($sess['tiet'], $exSess['tiet'])) {
                                $hiddenByExam = true;
                                break;
                            }
                        }
                        if ($hiddenByExam) {
                            continue;
                        }
                    }

                    $minP = self::minPeriodFromTiet($sess['tiet']);
                    $bucket = self::sessionKeyFromMinPeriod($minP);
                    $isLt = $sess['loai'] === 'ly_thuyet';
                    $isPause = $sess['loai'] === 'tam_ngung';
                    $badge = $isPause ? '#e74c3c' : ($isLt ? '#3498db' : '#27ae60');

                    $sortP = $minP ?? 99;
                    $slot = [
                        'kind' => $isPause ? 'pause' : 'study',
                        'title' => $isPause ? ('Tạm ngưng: '.$offering->ten_hoc_phan) : $offering->ten_hoc_phan,
                        'meta' => trim(
                            ($room ? $room->ma_lop.' · ' : '')
                            .($isPause ? 'Tạm ngưng' : ($isLt ? 'Lý thuyết' : 'Thực hành'))
                            .' · Tiết '.$sess['tiet']
                            .(! empty($sess['moved_from']) ? (' · dời từ '.$sess['moved_from']) : '')
                        ),
                        'badge' => $badge,
                        'sort_period' => $sortP,
                    ];
                    if ($withRescheduleMeta) {
                        $slot = array_merge($slot, self::rescheduleMetaForSession($offering, $sess, $date));
                    }
                    $grid[$bucket][$d][] = $slot;
                }

                foreach ($examDates as $idx => $examDate) {
                    if (! $examDate->isSameDay($date)) {
                        continue;
                    }
                    $sess = $examSessions[$idx];
                    $minP = self::minPeriodFromTiet($sess['tiet']);
                    $bucket = self::sessionKeyFromMinPeriod($minP);
                    $isLt = $sess['loai'] === 'ly_thuyet';
                    $sortP = $minP ?? 99;
                    $grid[$bucket][$d][] = [
                        'kind' => 'exam',
                        'title' => 'Thi: '.$offering->ten_hoc_phan,
                        'meta' => trim(
                            ($room ? $room->ma_lop.' · ' : '')
                            .($isLt ? 'Lý thuyết' : 'Thực hành')
                            .' · Tiết '.$sess['tiet']
                        ),
                        'badge' => '#f39c12',
                        'sort_period' => $sortP,
                    ];
                }
            }
        }

        foreach (['morning', 'afternoon', 'evening'] as $bucket) {
            for ($d = 0; $d < 7; $d++) {
                usort($grid[$bucket][$d], function (array $a, array $b): int {
                    $pa = (int) ($a['sort_period'] ?? 99);
                    $pb = (int) ($b['sort_period'] ?? 99);
                    if ($pa !== $pb) {
                        return $pa <=> $pb;
                    }

                    return strcmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
                });
            }
        }

        return $grid;
    }
}
