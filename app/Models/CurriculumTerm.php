<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CurriculumTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'ten_ky',
        'thu_tu',
        'ghi_chu',
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'curriculum_term_subject')
            ->withPivot('sort_order', 'loai_hoc_phan', 'nhom_tu_chon', 'so_tc_bat_buoc_cua_nhom')
            ->withTimestamps()
            ->orderBy('curriculum_term_subject.sort_order')
            ->orderBy('subjects.ten_mon_hoc');
    }

    /**
     * Tổng TC học phần tự chọn: cùng một khóa nhóm (Subject::electiveCreditPoolKey > 0) chỉ tính một lần TC (max trong nhóm).
     */
    public function sumElectiveCreditsCountOncePerGroup(): int
    {
        $electives = $this->subjects->filter(
            fn (Subject $subject) => ($subject->pivot->loai_hoc_phan ?? 'bat_buoc') === 'tu_chon'
        );

        $standalone = $electives->filter(
            fn (Subject $subject) => $subject->electiveCreditPoolKey($subject->pivot) === 0
        );
        $pooled = $electives->filter(
            fn (Subject $subject) => $subject->electiveCreditPoolKey($subject->pivot) > 0
        );

        $standaloneSum = (int) $standalone->sum('so_tin_chi');

        $pooledSum = (int) $pooled
            ->groupBy(fn (Subject $subject) => $subject->electiveCreditPoolKey($subject->pivot))
            ->sum(fn (Collection $group) => (int) $group->max('so_tin_chi'));

        return $standaloneSum + $pooledSum;
    }

    public function sumRequiredCredits(): int
    {
        return (int) $this->subjects
            ->filter(fn (Subject $subject) => ($subject->pivot->loai_hoc_phan ?? 'bat_buoc') === 'bat_buoc')
            ->sum('so_tin_chi');
    }

    /** Tổng TC kỳ (bắt buộc + tự chọn đã gộp nhóm) — dùng cho dòng tổng kỳ / tổng CT. */
    public function sumCreditsForCurriculumTotal(): int
    {
        return $this->sumRequiredCredits() + $this->sumElectiveCreditsCountOncePerGroup();
    }
}
