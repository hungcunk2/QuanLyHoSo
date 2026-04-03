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
     * @return list<array{loai: string, thu: int, tiet: string}>
     */
    public static function flattenOfferingSessions(CourseOffering $o): array
    {
        $o->loadMissing('schedules');

        $rows = [];
        if ($o->thu_ly_thuyet && $o->tiet_ly_thuyet) {
            $rows[] = ['loai' => 'ly_thuyet', 'thu' => (int) $o->thu_ly_thuyet, 'tiet' => (string) $o->tiet_ly_thuyet];
        }
        foreach ($o->schedules->where('loai', 'ly_thuyet') as $sc) {
            $rows[] = ['loai' => 'ly_thuyet', 'thu' => (int) $sc->thu, 'tiet' => (string) $sc->tiet];
        }
        if ($o->thu_thuc_hanh && $o->tiet_thuc_hanh) {
            $rows[] = ['loai' => 'thuc_hanh', 'thu' => (int) $o->thu_thuc_hanh, 'tiet' => (string) $o->tiet_thuc_hanh];
        }
        foreach ($o->schedules->where('loai', 'thuc_hanh') as $sc) {
            $rows[] = ['loai' => 'thuc_hanh', 'thu' => (int) $sc->thu, 'tiet' => (string) $sc->tiet];
        }

        return $rows;
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

            for ($d = 0; $d < 7; $d++) {
                $date = $weekStart->copy()->addDays($d);
                if ($date->lt($start) || $date->gt($end)) {
                    continue;
                }

                $thuVn = self::thuVnFromDate($date);

                foreach ($sessions as $sess) {
                    if ((int) $sess['thu'] !== $thuVn) {
                        continue;
                    }

                    $minP = self::minPeriodFromTiet($sess['tiet']);
                    $bucket = self::sessionKeyFromMinPeriod($minP);
                    $isLt = $sess['loai'] === 'ly_thuyet';
                    $badge = $isLt ? '#3498db' : '#27ae60';
                    $room = $offering->classRoom;

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
            }
        }

        return $grid;
    }
}
