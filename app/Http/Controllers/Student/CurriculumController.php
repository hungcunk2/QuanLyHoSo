<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseOfferingGrade;
use App\Models\CurriculumTerm;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class CurriculumController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = $user ? Student::where('email', $user->email)->first() : null;

        $items = CurriculumTerm::query()
            ->with('subjects')
            ->orderBy('thu_tu')
            ->get();

        $subjectStatuses = [];
        if ($student) {
            $grades = CourseOfferingGrade::query()
                ->where('student_id', $student->id)
                ->with('courseOffering:id,subject_id,grades_finalized_at')
                ->get()
                ->filter(function ($grade) {
                    return $grade->courseOffering
                        && $grade->courseOffering->subject_id
                        && $grade->courseOffering->grades_finalized_at
                        && $grade->diem_tong_ket !== null;
                })
                ->sortByDesc(fn ($grade) => optional($grade->courseOffering->grades_finalized_at)?->timestamp ?? 0);

            foreach ($grades as $grade) {
                $subjectId = (int) $grade->courseOffering->subject_id;
                if (isset($subjectStatuses[$subjectId])) {
                    continue;
                }

                $score = (float) $grade->diem_tong_ket;
                $subjectStatuses[$subjectId] = $score >= 4 ? 'passed' : 'failed';
            }
        }

        return view('student.curriculum', compact('items', 'subjectStatuses'));
    }
}
