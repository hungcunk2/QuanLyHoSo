<?php

namespace App\Services;

use App\Models\CourseOffering;
use App\Models\CourseOfferingSchedule;
use App\Support\OfferingWeekCalendar;
use Carbon\Carbon;

class CourseOfferingScheduleConflictService
{
    /**
     * @return int[] sorted unique period numbers 1..16
     */
    public static function parsePeriods(?string $tiet): array
    {
        if ($tiet === null || $tiet === '') {
            return [];
        }
        $parts = array_filter(array_map('trim', explode(',', $tiet)));
        $out = [];
        foreach ($parts as $p) {
            $n = (int) $p;
            if ($n >= 1 && $n <= 16) {
                $out[$n] = true;
            }
        }
        $keys = array_keys($out);
        sort($keys);

        return $keys;
    }

    /**
     * @param  array<int, mixed>  $thuLt
     * @param  array<int, mixed>  $tietLt
     * @param  array<int, mixed>  $thuTh
     * @param  array<int, mixed>  $tietTh
     * @return list<array{thu: int, periods: int[]}>
     */
    public static function slotsFromRequestArrays(array $thuLt, array $tietLt, array $thuTh, array $tietTh): array
    {
        $slots = [];
        $n = count($thuLt);
        for ($i = 0; $i < $n; $i++) {
            $thu = isset($thuLt[$i]) ? (int) $thuLt[$i] : 0;
            $tietStr = isset($tietLt[$i]) ? (string) $tietLt[$i] : '';
            if ($thu < 2 || $thu > 8 || $tietStr === '') {
                continue;
            }
            $periods = self::parsePeriods($tietStr);
            if ($periods !== []) {
                $slots[] = ['thu' => $thu, 'periods' => $periods];
            }
        }
        $nTh = count($thuTh);
        for ($i = 0; $i < $nTh; $i++) {
            $rawThu = $thuTh[$i] ?? null;
            if ($rawThu === null || $rawThu === '') {
                continue;
            }
            $thu = (int) $rawThu;
            $tietStr = isset($tietTh[$i]) ? (string) $tietTh[$i] : '';
            if ($thu < 2 || $thu > 8 || $tietStr === '') {
                continue;
            }
            $periods = self::parsePeriods($tietStr);
            if ($periods !== []) {
                $slots[] = ['thu' => $thu, 'periods' => $periods];
            }
        }

        return $slots;
    }

    /**
     * @return list<array{thu: int, periods: int[]}>
     */
    public static function slotsFromOffering(CourseOffering $o): array
    {
        $slots = [];
        $o->loadMissing('schedules');
        $paused = $o->schedules
            ->where('loai', 'tam_ngung')
            ->map(fn ($s) => ((int) ($s->thu ?? 0)).'|'.((string) ($s->tiet ?? '')))
            ->filter(fn ($k) => $k !== '0|')
            ->values()
            ->all();
        $pausedSet = array_fill_keys($paused, true);

        if ($o->thu_ly_thuyet && ($o->tiet_ly_thuyet ?? '') !== '') {
            $k = ((int) $o->thu_ly_thuyet).'|'.((string) $o->tiet_ly_thuyet);
            if (isset($pausedSet[$k])) {
                // buổi gốc đã bị tạm ngưng -> không tính là "tiết học"
            } else {
            $periods = self::parsePeriods((string) $o->tiet_ly_thuyet);
            if ($periods !== []) {
                $slots[] = ['thu' => (int) $o->thu_ly_thuyet, 'periods' => $periods];
            }
            }
        }
        if ($o->thu_thuc_hanh && ($o->tiet_thuc_hanh ?? '') !== '' && $o->tiet_thuc_hanh !== null) {
            $k = ((int) $o->thu_thuc_hanh).'|'.((string) $o->tiet_thuc_hanh);
            if (isset($pausedSet[$k])) {
                // buổi gốc đã bị tạm ngưng -> không tính
            } else {
            $periods = self::parsePeriods((string) $o->tiet_thuc_hanh);
            if ($periods !== []) {
                $slots[] = ['thu' => (int) $o->thu_thuc_hanh, 'periods' => $periods];
            }
            }
        }
        foreach ($o->schedules as $s) {
            // Các buổi tạm ngưng không phải "tiết học" để đem đi check trùng
            if (($s->loai ?? '') === 'tam_ngung') {
                continue;
            }
            if (! $s->thu || ($s->tiet ?? '') === '') {
                continue;
            }
            $periods = self::parsePeriods((string) $s->tiet);
            if ($periods !== []) {
                $slots[] = ['thu' => (int) $s->thu, 'periods' => $periods];
            }
        }

        return $slots;
    }

    /**
     * @param  list<array{thu: int, periods: int[]}>  $newSlots
     * @param  int[]  $teacherIds
     * @return string|null Thông báo lỗi tiếng Việt nếu trùng lịch
     */
    public static function findConflict(
        array $newSlots,
        array $teacherIds,
        int $classRoomId,
        Carbon $ngayBatDau,
        Carbon $ngayKetThuc,
        ?int $ignoreOfferingId = null
    ): ?string {
        if ($newSlots === []) {
            return null;
        }
        $teacherIds = array_values(array_unique(array_filter(array_map('intval', $teacherIds))));

        $query = CourseOffering::query()
            ->with('schedules')
            ->where(function ($q) use ($teacherIds, $classRoomId) {
                $q->where('class_room_id', $classRoomId);
                if ($teacherIds !== []) {
                    $q->orWhereIn('teacher_id', $teacherIds)
                        ->orWhereIn('teacher_id_ly_thuyet', $teacherIds)
                        ->orWhereIn('teacher_id_thuc_hanh', $teacherIds)
                        ->orWhereHas('schedules', function ($sq) use ($teacherIds) {
                            $sq->whereIn('teacher_id', $teacherIds);
                        });
                }
            })
            ->whereDate('ngay_bat_dau_hoc', '<=', $ngayKetThuc)
            ->whereDate('ngay_ket_thuc_hoc', '>=', $ngayBatDau);

        if ($ignoreOfferingId !== null) {
            $query->where('id', '!=', $ignoreOfferingId);
        }

        $weekdays = CourseOffering::weekdays();

        foreach ($query->get() as $other) {
            if (! $other->ngay_bat_dau_hoc || ! $other->ngay_ket_thuc_hoc) {
                continue;
            }

            $otherSlots = self::slotsFromOffering($other);

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

                    $teacherConflict = false;
                    if ($teacherIds !== []) {
                        $teacherConflict = ($other->teacher_id !== null && in_array((int) $other->teacher_id, $teacherIds, true))
                            || ($other->teacher_id_ly_thuyet !== null && in_array((int) $other->teacher_id_ly_thuyet, $teacherIds, true))
                            || ($other->teacher_id_thuc_hanh !== null && in_array((int) $other->teacher_id_thuc_hanh, $teacherIds, true))
                            || $other->schedules->whereNotNull('teacher_id')->pluck('teacher_id')->map(fn ($x) => (int) $x)->intersect($teacherIds)->isNotEmpty();
                    }

                    if ($teacherConflict) {
                        return 'Giáo viên bị trùng tiết: đã có học phần "'.$other->ten_hoc_phan.'" '
                            .'('.$thuLabel.', tiết '.$tietStr.') trong khoảng thời gian học giao nhau.';
                    }
                    if ($other->class_room_id !== null && (int) $other->class_room_id === (int) $classRoomId) {
                        return 'Phòng học bị trùng tiết: đã có học phần "'.$other->ten_hoc_phan.'" '
                            .'('.$thuLabel.', tiết '.$tietStr.') trong khoảng thời gian học giao nhau.';
                    }
                }
            }
        }

        return null;
    }

    /**
     * GV của buổi học (theo dòng lịch sessionsForDate).
     *
     * @param  array{loai?: string, schedule_id?: int|null}  $sess
     * @return int[]
     */
    public static function teacherIdsFromSessionRow(CourseOffering $o, array $sess): array
    {
        $ids = [];
        $scheduleId = $sess['schedule_id'] ?? null;
        if ($scheduleId) {
            $sc = $o->schedules->firstWhere('id', (int) $scheduleId);
            if ($sc && $sc->teacher_id) {
                $ids[] = (int) $sc->teacher_id;
            }
        }
        $loai = (string) ($sess['loai'] ?? '');
        if ($loai === 'ly_thuyet' && $o->teacher_id_ly_thuyet) {
            $ids[] = (int) $o->teacher_id_ly_thuyet;
        }
        if ($loai === 'thuc_hanh' && $o->teacher_id_thuc_hanh) {
            $ids[] = (int) $o->teacher_id_thuc_hanh;
        }
        if ($o->teacher_id) {
            $ids[] = (int) $o->teacher_id;
        }

        return array_values(array_unique($ids));
    }

    /**
     * GV của buổi admin chọn dời (base_lt / base_th / sc_*).
     *
     * @return int[]
     */
    public static function teacherIdsForRescheduleSession(
        CourseOffering $o,
        string $sessionKey,
        ?CourseOfferingSchedule $schedule = null
    ): array {
        if ($sessionKey === 'base_lt') {
            $ids = [];
            if ($o->teacher_id_ly_thuyet) {
                $ids[] = (int) $o->teacher_id_ly_thuyet;
            }
            if ($o->teacher_id) {
                $ids[] = (int) $o->teacher_id;
            }

            return array_values(array_unique($ids));
        }
        if ($sessionKey === 'base_th') {
            $ids = [];
            if ($o->teacher_id_thuc_hanh) {
                $ids[] = (int) $o->teacher_id_thuc_hanh;
            }
            if ($o->teacher_id) {
                $ids[] = (int) $o->teacher_id;
            }

            return array_values(array_unique($ids));
        }
        if (str_starts_with($sessionKey, 'sc_') && $schedule) {
            if ($schedule->teacher_id) {
                return [(int) $schedule->teacher_id];
            }
            if ($schedule->loai === 'ly_thuyet' && $o->teacher_id_ly_thuyet) {
                return [(int) $o->teacher_id_ly_thuyet];
            }
            if ($schedule->loai === 'thuc_hanh' && $o->teacher_id_thuc_hanh) {
                return [(int) $o->teacher_id_thuc_hanh];
            }
        }

        return [];
    }

    /**
     * Trùng lịch GV đúng một ngày (lặp tuần + buổi dời có ngay_ap_dung).
     *
     * @param  array{thu: int, periods: int[]}  $newSlot
     * @param  int[]  $teacherIds
     */
    public static function findTeacherConflictOnDate(
        Carbon $date,
        array $newSlot,
        array $teacherIds,
        ?int $ignoreOfferingId = null
    ): ?string {
        if ($newSlot['periods'] === []) {
            return null;
        }
        $teacherIds = array_values(array_unique(array_filter(array_map('intval', $teacherIds))));
        if ($teacherIds === []) {
            return null;
        }

        $dateStr = $date->toDateString();
        $query = CourseOffering::query()
            ->with('schedules')
            ->where(function ($q) use ($teacherIds) {
                $q->whereIn('teacher_id', $teacherIds)
                    ->orWhereIn('teacher_id_ly_thuyet', $teacherIds)
                    ->orWhereIn('teacher_id_thuc_hanh', $teacherIds)
                    ->orWhereHas('schedules', function ($sq) use ($teacherIds) {
                        $sq->whereIn('teacher_id', $teacherIds);
                    });
            })
            ->whereDate('ngay_bat_dau_hoc', '<=', $dateStr)
            ->whereDate('ngay_ket_thuc_hoc', '>=', $dateStr);

        if ($ignoreOfferingId !== null) {
            $query->where('id', '!=', $ignoreOfferingId);
        }

        $weekdays = CourseOffering::weekdays();
        $dateLabel = $date->format('d/m/Y');

        foreach ($query->get() as $other) {
            foreach (OfferingWeekCalendar::sessionsForDate($other, $date) as $sess) {
                if (($sess['loai'] ?? '') === 'tam_ngung') {
                    continue;
                }
                $osPeriods = self::parsePeriods($sess['tiet'] ?? '');
                if ($osPeriods === [] || (int) $sess['thu'] !== (int) $newSlot['thu']) {
                    continue;
                }
                $intersect = array_values(array_intersect($osPeriods, $newSlot['periods']));
                if ($intersect === []) {
                    continue;
                }
                $otherTeacherIds = self::teacherIdsFromSessionRow($other, $sess);
                if (array_intersect($teacherIds, $otherTeacherIds) === []) {
                    continue;
                }
                sort($intersect);
                $tietStr = implode(', ', $intersect);
                $thuLabel = $weekdays[$newSlot['thu']] ?? ('Thứ '.$newSlot['thu']);

                return 'Giáo viên bị trùng lịch ngày '.$dateLabel.': đã có học phần "'.$other->ten_hoc_phan.'" '
                    .'('.$thuLabel.', tiết '.$tietStr.').';
            }
        }

        return null;
    }
}
