<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseOfferingSchedule extends Model
{
    protected $table = 'course_offering_schedules';

    protected $fillable = [
        'course_offering_id',
        'teacher_id',
        'class_room_id',
        'loai',
        'thu',
        'tiet',
        'ngay_ap_dung',
        'paused_session_key',
        'origin_session_key',
        'thi_buoi_thu',
        'moved_from',
    ];

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_room_id');
    }
}
