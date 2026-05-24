<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\CourseOffering;
use App\Models\Student;
use App\Models\SubjectRegistration;
use App\Models\Teacher;
use Illuminate\Support\Collection;

class CourseOfferingChatService
{
    public function isTeacherAssigned(Teacher $teacher, CourseOffering $offering): bool
    {
        if ((int) $offering->teacher_id_ly_thuyet === (int) $teacher->id) {
            return true;
        }
        if ((int) $offering->teacher_id_thuc_hanh === (int) $teacher->id) {
            return true;
        }

        return $offering->schedules()->where('teacher_id', $teacher->id)->exists();
    }

    public function isStudentRegistered(Student $student, CourseOffering $offering): bool
    {
        return SubjectRegistration::query()
            ->where('student_id', $student->id)
            ->where('course_offering_id', $offering->id)
            ->where('status', '!=', 'cancelled')
            ->exists();
    }

    public function canChat(Student $student, Teacher $teacher, CourseOffering $offering): bool
    {
        if ($offering->is_cancelled) {
            return false;
        }

        return $this->isStudentRegistered($student, $offering)
            && $this->isTeacherAssigned($teacher, $offering);
    }

    /**
     * @return Collection<int, int>
     */
    public function teacherIdsForOffering(CourseOffering $offering): Collection
    {
        $ids = collect([
            $offering->teacher_id_ly_thuyet,
            $offering->teacher_id_thuc_hanh,
            $offering->teacher_id,
        ])->filter();

        $scheduleIds = $offering->schedules()
            ->whereNotNull('teacher_id')
            ->pluck('teacher_id');

        return $ids->merge($scheduleIds)->map(fn ($id) => (int) $id)->unique()->values();
    }

    public function findOrCreateConversation(int $studentId, int $teacherId, int $courseOfferingId): ChatConversation
    {
        return ChatConversation::query()->firstOrCreate([
            'student_id' => $studentId,
            'teacher_id' => $teacherId,
            'course_offering_id' => $courseOfferingId,
        ]);
    }

    /**
     * Một dòng / giáo viên (gộp nhiều học phần cùng kỳ).
     *
     * @return list<array{
     *     teacher_id: int,
     *     teacher_name: string,
     *     label: string,
     *     course_offering_id: int,
     *     offerings: list<array{course_offering_id: int, label: string}>
     * }>
     */
    public function newChatOptionsForStudent(Student $student): array
    {
        $registrations = SubjectRegistration::query()
            ->where('student_id', $student->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('course_offering_id')
            ->with(['courseOffering.subject', 'courseOffering.schedules'])
            ->get();

        /** @var array<int, array{teacher_id: int, teacher_name: string, offerings: array<int, array{course_offering_id: int, label: string}>}> $byTeacher */
        $byTeacher = [];

        foreach ($registrations as $registration) {
            $offering = $registration->courseOffering;
            if (! $offering || $offering->is_cancelled) {
                continue;
            }

            $offeringLabel = $this->offeringLabel($offering);
            $teacherIds = $this->teacherIdsForOffering($offering);
            if ($teacherIds->isEmpty()) {
                continue;
            }

            $teachers = Teacher::query()->whereIn('id', $teacherIds)->orderBy('ho_ten')->get();

            foreach ($teachers as $teacher) {
                $tid = (int) $teacher->id;
                if (! isset($byTeacher[$tid])) {
                    $byTeacher[$tid] = [
                        'teacher_id' => $tid,
                        'teacher_name' => $teacher->ho_ten,
                        'offerings' => [],
                    ];
                }
                $byTeacher[$tid]['offerings'][(int) $offering->id] = [
                    'course_offering_id' => (int) $offering->id,
                    'label' => $offeringLabel,
                ];
            }
        }

        $options = [];
        foreach ($byTeacher as $row) {
            $offerings = array_values($row['offerings']);
            usort($offerings, fn (array $a, array $b) => strcmp($a['label'], $b['label']));

            $labels = array_column($offerings, 'label');
            $summaryLabel = count($offerings) === 1
                ? $labels[0]
                : implode(' · ', $labels);

            $options[] = [
                'teacher_id' => $row['teacher_id'],
                'teacher_name' => $row['teacher_name'],
                'label' => $summaryLabel,
                'course_offering_id' => $offerings[0]['course_offering_id'],
                'offerings' => $offerings,
            ];
        }

        usort($options, fn (array $a, array $b) => strcmp($a['teacher_name'], $b['teacher_name']));

        return $options;
    }

    /**
     * @return list<array{course_offering_id:int, student_id:int, label:string, student_name:string}>
     */
    public function newChatOptionsForTeacher(Teacher $teacher): array
    {
        $offerings = $this->assignedOfferingsQuery($teacher)
            ->with(['subject'])
            ->orderByDesc('ngay_bat_dau_hoc')
            ->get();

        $options = [];

        foreach ($offerings as $offering) {
            if ($offering->is_cancelled) {
                continue;
            }

            $offeringLabel = $this->offeringLabel($offering);

            $students = SubjectRegistration::query()
                ->where('course_offering_id', $offering->id)
                ->where('status', '!=', 'cancelled')
                ->with('student')
                ->get()
                ->pluck('student')
                ->filter()
                ->unique('id')
                ->sortBy('ho_ten');

            foreach ($students as $student) {
                $options[] = [
                    'course_offering_id' => (int) $offering->id,
                    'student_id' => (int) $student->id,
                    'label' => $offeringLabel,
                    'student_name' => $student->ho_ten,
                ];
            }
        }

        usort($options, fn (array $a, array $b) => strcmp($a['label'].$a['student_name'], $b['label'].$b['student_name']));

        return $options;
    }

    public function assignedOfferingsQuery(Teacher $teacher)
    {
        return CourseOffering::query()
            ->where(function ($q) use ($teacher) {
                $q->where('teacher_id_ly_thuyet', $teacher->id)
                    ->orWhere('teacher_id_thuc_hanh', $teacher->id)
                    ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacher->id));
            });
    }

    public function offeringLabel(CourseOffering $offering): string
    {
        $subjectName = $offering->subject?->ten_mon_hoc ?? $offering->ten_hoc_phan ?? 'Học phần';
        $parts = [$subjectName];

        if ($offering->hoc_ky) {
            $parts[] = $offering->hoc_ky;
        }
        if ($offering->khoa_hoc) {
            $parts[] = 'KH '.$offering->khoa_hoc;
        }

        return implode(' · ', $parts);
    }
}
