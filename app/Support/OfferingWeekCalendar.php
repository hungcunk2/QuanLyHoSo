<?php

namespace App\Support;

use App\Models\CourseOffering;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Lưới tuần (Sáng / Chiều / Tối × 7 ngày) từ lịch cố định học phần trong khoảng ngày bắt đầu – kết thúc học.
 * Học phần xóa khỏi DB sẽ không còn trong truy vấn → tiết tự biến mất.
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
     * @return list<array{loai: string, thu: int, tiet: string, thi_buoi_thu: int|null}>
     */
    public static function flattenOfferingSessions(CourseOffering $o): array
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
            $rows[] = [
                'loai' => 'thuc_hanh',
                'thu' => (int) $sc->thu,
                'tiet' => (string) $sc->tiet,
                'thi_buoi_thu' => $sc->thi_buoi_thu ? (int) $sc->thi_buoi_thu : null,
            ];
        }

        return $rows;
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
     * @return array{morning: array<int, list<array{kind: string, title: string, meta: string, badge: string}>>, afternoon: array, evening: array}
     */
    public static function buildGrid(Collection $offerings, Carbon $weekStart): array
    {
        $weekStart = $weekStart->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();

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

            $sessions = self::flattenOfferingSessions($offering);
            $room = $offering->classRoom;

            // Tính ngày thi cho từng buổi (nếu có).
            $examDates = [];
            foreach ($sessions as $idx => $sess) {
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

                $thuVn = self::thuVnFromDate($date);

                foreach ($sessions as $idx => $sess) {
                    if ((int) $sess['thu'] !== $thuVn) {
                        continue;
                    }

                    // Nếu buổi này là ngày thi, bỏ lịch học của buổi đó (chỉ hiển thị lịch thi).
                    if (isset($examDates[$idx]) && $examDates[$idx]->isSameDay($date)) {
                        continue;
                    }

                    $minP = self::minPeriodFromTiet($sess['tiet']);
                    $bucket = self::sessionKeyFromMinPeriod($minP);
                    $isLt = $sess['loai'] === 'ly_thuyet';
                    $badge = $isLt ? '#3498db' : '#27ae60';

                    $grid[$bucket][$d][] = [
                        'kind' => 'study',
                        'title' => $offering->ten_hoc_phan,
                        'meta' => trim(
                            ($room ? $room->ma_lop.' · ' : '')
                            .($isLt ? 'Lý thuyết' : 'Thực hành')
                            .' · Tiết '.$sess['tiet']
                        ),
                        'badge' => $badge,
                    ];
                }

                // Đổ lịch thi (màu vàng) nếu ngày thi rơi vào tuần này.
                foreach ($examDates as $idx => $examDate) {
                    if (! $examDate->isSameDay($date)) {
                        continue;
                    }
                    $sess = $sessions[$idx];
                    $minP = self::minPeriodFromTiet($sess['tiet']);
                    $bucket = self::sessionKeyFromMinPeriod($minP);
                    $isLt = $sess['loai'] === 'ly_thuyet';
                    $grid[$bucket][$d][] = [
                        'kind' => 'exam',
                        'title' => 'Thi: ' . $offering->ten_hoc_phan,
                        'meta' => trim(
                            ($room ? $room->ma_lop.' · ' : '')
                            .($isLt ? 'Lý thuyết' : 'Thực hành')
                            .' · Tiết '.$sess['tiet']
                        ),
                        'badge' => '#f39c12',
                    ];
                }
            }
        }

        return $grid;
    }
}
