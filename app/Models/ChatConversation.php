<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChatConversation extends Model
{
    protected $fillable = [
        'student_id',
        'teacher_id',
        'course_offering_id',
        'student_last_read_at',
        'teacher_last_read_at',
    ];

    protected function casts(): array
    {
        return [
            'student_last_read_at' => 'datetime',
            'teacher_last_read_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class, 'course_offering_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

    public function unreadCountForRole(string $role): int
    {
        $lastRead = $role === 'student'
            ? $this->student_last_read_at
            : $this->teacher_last_read_at;

        $query = $this->messages()->where('sender_role', '!=', $role);

        if ($lastRead) {
            $query->where('created_at', '>', $lastRead);
        }

        return $query->count();
    }

    public function markReadForRole(string $role): void
    {
        $column = $role === 'student' ? 'student_last_read_at' : 'teacher_last_read_at';
        $this->forceFill([$column => now()])->save();
    }
}
