<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
