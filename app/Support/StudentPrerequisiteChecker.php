<?php

namespace App\Support;

use App\Models\CourseOfferingGrade;
use App\Models\Subject;

class StudentPrerequisiteChecker
{
    /**
     * @return list<int> subject_id đã có điểm chốt (học phần đã finalize, có điểm tổng kết)
     */
    public static function finalizedSubjectIdsForStudent(?int $studentId): array
    {
        if (! $studentId) {
            return [];
        }

        return CourseOfferingGrade::query()
            ->where('student_id', $studentId)
            ->whereNotNull('diem_tong_ket')
            ->whereHas('courseOffering', function ($q) {
                $q->whereNotNull('grades_finalized_at');
            })
            ->with('courseOffering:id,subject_id')
            ->get()
            ->pluck('courseOffering.subject_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public static function hasFinalizedGradeForSubject(int $studentId, int $prerequisiteSubjectId): bool
    {
        return in_array(
            $prerequisiteSubjectId,
            self::finalizedSubjectIdsForStudent($studentId),
            true
        );
    }

    /**
     * Sinh viên đủ điều kiện đăng ký môn này (đã có điểm chốt môn tiên quyết nếu có).
     */
    public static function studentMeetsPrerequisite(?int $studentId, ?Subject $subject): bool
    {
        if (! $subject || ! $subject->mon_tien_quyet_id) {
            return true;
        }

        if (! $studentId) {
            return false;
        }

        return self::hasFinalizedGradeForSubject($studentId, (int) $subject->mon_tien_quyet_id);
    }

    /**
     * Thông báo lỗi tiếng Việt hoặc null nếu đủ điều kiện.
     */
    public static function prerequisiteBlockMessage(?int $studentId, ?Subject $subject): ?string
    {
        if (self::studentMeetsPrerequisite($studentId, $subject)) {
            return null;
        }

        $pre = $subject->monTienQuyet ?? Subject::query()->find($subject->mon_tien_quyet_id);
        $preLabel = $pre
            ? trim($pre->ma_mon_hoc.' - '.$pre->ten_mon_hoc)
            : 'môn tiên quyết';

        $monLabel = trim(($subject->ma_mon_hoc ?? '').' - '.($subject->ten_mon_hoc ?? 'môn này'));

        return 'Không thể đăng ký "'.$monLabel.'" vì bạn chưa có điểm đã chốt của môn tiên quyết "'.$preLabel.'".';
    }
}
