<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseOffering extends Model
{
    use HasFactory;

    protected $table = 'course_offerings';

    protected $fillable = [
        'ten_hoc_phan',
        'hoc_ky',
        'khoa_hoc',
        'class_room_id',
        'class_room_id_thuc_hanh',
        'subject_id',
        'teacher_id',
        'teacher_id_ly_thuyet',
        'teacher_id_thuc_hanh',
        'si_so_lop',
        'si_so_thuc_hanh_nhom_1',
        'si_so_thuc_hanh_nhom_2',
        'ngay_mo_dang_ky',
        'ngay_ket_thuc_dang_ky',
        'ngay_bat_dau_hoc',
        'ngay_ket_thuc_hoc',
        'is_cancelled',
        'cancel_reason',
        'cancelled_at',
        'grades_finalized_at',
        'thu_ly_thuyet',
        'tiet_ly_thuyet',
        'lt_moved_from',
        'ngay_thi_ly_thuyet_buoi_thu',
        'thu_thuc_hanh',
        'tiet_thuc_hanh',
        'th_moved_from',
        'ngay_thi_thuc_hanh_buoi_thu',
    ];

    protected function casts(): array
    {
        return [
            'ngay_mo_dang_ky' => 'date',
            'ngay_ket_thuc_dang_ky' => 'date',
            'ngay_bat_dau_hoc' => 'date',
            'ngay_ket_thuc_hoc' => 'date',
            'is_cancelled' => 'boolean',
            'cancelled_at' => 'datetime',
            'grades_finalized_at' => 'datetime',
        ];
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_room_id');
    }

    public function classRoomThucHanh(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_room_id_thuc_hanh');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function teacherLyThuyet(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id_ly_thuyet');
    }

    public function teacherThucHanh(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id_thuc_hanh');
    }

    public function subjectRegistrations(): HasMany
    {
        return $this->hasMany(SubjectRegistration::class, 'course_offering_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(CourseOfferingSchedule::class);
    }

    /** Thứ trong tuần: 2=Thứ 2, ..., 8=Chủ nhật */
    public static function weekdays(): array
    {
        return [
            2 => 'Thứ 2',
            3 => 'Thứ 3',
            4 => 'Thứ 4',
            5 => 'Thứ 5',
            6 => 'Thứ 6',
            7 => 'Thứ 7',
            8 => 'Chủ nhật',
        ];
    }

    /** Tiết 1-16 theo lịch (sáng 1-6, chiều 7-12, tối 13-16) */
    public static function periodLabels(): array
    {
        return [
            1 => 'Tiết 1 (6h30-7h20)',
            2 => 'Tiết 2 (7h20-8h10)',
            3 => 'Tiết 3 (8h10-9h00)',
            4 => 'Tiết 4 (9h10-10h00)',
            5 => 'Tiết 5 (10h00-10h50)',
            6 => 'Tiết 6 (10h50-11h40)',
            7 => 'Tiết 7 (12h30-13h20)',
            8 => 'Tiết 8 (13h20-14h10)',
            9 => 'Tiết 9 (14h10-15h00)',
            10 => 'Tiết 10 (15h10-16h00)',
            11 => 'Tiết 11 (16h00-16h50)',
            12 => 'Tiết 12 (16h50-17h40)',
            13 => 'Tiết 13 (18h00-18h50)',
            14 => 'Tiết 14 (18h50-19h40)',
            15 => 'Tiết 15 (19h50-20h40)',
            16 => 'Tiết 16 (20h40-21h30)',
        ];
    }
}
