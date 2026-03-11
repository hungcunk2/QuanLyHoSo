<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseOfferingSchedule extends Model
{
    protected $table = 'course_offering_schedules';

    protected $fillable = ['course_offering_id', 'loai', 'thu', 'tiet', 'thi_buoi_thu'];

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }
}
