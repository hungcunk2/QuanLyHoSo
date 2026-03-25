<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectRegistration extends Model
{
    use HasFactory;

    protected $table = 'subject_registrations';

    protected $fillable = [
        'course_offering_id',
        'student_id',
        'subject_id',
        'class_room_id',
        'status',
    ];

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class, 'course_offering_id');
    }

    /**
     * Get the student that made the registration.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the subject being registered.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the class room (if assigned).
     */
    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_room_id');
    }

    /**
     * Status labels in Vietnamese.
     */
    public static function statusLabels(): array
    {
        return [
            'approved' => 'Đã đăng ký',
            'cancelled' => 'Đã hủy',
        ];
    }
}
