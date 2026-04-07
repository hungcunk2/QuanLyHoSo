<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseOfferingGrade extends Model
{
    protected $table = 'course_offering_grades';

    protected $fillable = [
        'course_offering_id',
        'student_id',
        'thuong_xuyen',
        'thuc_hanh',
        'giua_ky',
        'cuoi_ky',
        'diem_tong_ket',
        'thang_diem_4',
        'diem_chu',
        'xep_loai',
    ];

    protected function casts(): array
    {
        return [
            'thuong_xuyen' => 'array',
            'thuc_hanh' => 'array',
            'giua_ky' => 'decimal:2',
            'cuoi_ky' => 'decimal:2',
            'diem_tong_ket' => 'decimal:2',
            'thang_diem_4' => 'decimal:2',
        ];
    }

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}

