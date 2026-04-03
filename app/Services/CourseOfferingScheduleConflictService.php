<?php

namespace App\Services;

use App\Models\CourseOffering;
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
        if ($o->thu_ly_thuyet && ($o->tiet_ly_thuyet ?? '') !== '') {
            $periods = self::parsePeriods((string) $o->tiet_ly_thuyet);
            if ($periods !== []) {
                $slots[] = ['thu' => (int) $o->thu_ly_thuyet, 'periods' => $periods];
            }
        }
        if ($o->thu_thuc_hanh && ($o->tiet_thuc_hanh ?? '') !== '' && $o->tiet_thuc_hanh !== null) {
            $periods = self::parsePeriods((string) $o->tiet_thuc_hanh);
            if ($periods !== []) {
                $slots[] = ['thu' => (int) $o->thu_thuc_hanh, 'periods' => $periods];
            }
        }
        foreach ($o->schedules as $s) {
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
     * @return string|null Thông báo lỗi tiếng Việt nếu trùng lịch
     */
    public static function findConflict(
        array $newSlots,
        int $teacherId,
        int $classRoomId,
        Carbon $ngayBatDau,
        Carbon $ngayKetThuc,
        ?int $ignoreOfferingId = null
    ): ?string {
        if ($newSlots === []) {
            return null;
        }

        $query = CourseOffering::query()
            ->with('schedules')
            ->where(function ($q) use ($teacherId, $classRoomId) {
                $q->where('teacher_id', $teacherId)->orWhere('class_room_id', $classRoomId);
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

                    if ($other->teacher_id !== null && (int) $other->teacher_id === $teacherId) {
                        return 'Giáo viên bị trùng tiết: đã có học phần "'.$other->ten_hoc_phan.'" '
                            .'('.$thuLabel.', tiết '.$tietStr.') trong khoảng thời gian học giao nhau.';
                    }
                    if ($other->class_room_id !== null && (int) $other->class_room_id === $classRoomId) {
                        return 'Phòng học bị trùng tiết: đã có học phần "'.$other->ten_hoc_phan.'" '
                            .'('.$thuLabel.', tiết '.$tietStr.') trong khoảng thời gian học giao nhau.';
                    }
                }
            }
        }

        return null;
    }
}
