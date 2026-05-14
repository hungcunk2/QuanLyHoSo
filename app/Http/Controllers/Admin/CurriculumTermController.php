<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurriculumTerm;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CurriculumTermController extends Controller
{
    public function index()
    {
        $items = CurriculumTerm::query()
            ->with('subjects')
            ->orderBy('thu_tu')
            ->paginate(15);

        return view('admin.curriculum-terms.index', compact('items'));
    }

    public function create()
    {
        $item = new CurriculumTerm();
        $subjects = Subject::query()
            ->orderBy('ten_mon_hoc')
            ->get();

        return view('admin.curriculum-terms.form', compact('item', 'subjects'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $item = CurriculumTerm::create([
            'ten_ky' => $data['ten_ky'],
            'thu_tu' => $data['thu_tu'],
            'ghi_chu' => $data['ghi_chu'] ?? null,
        ]);

        $this->syncSubjects(
            $item,
            $data['required_subject_ids'] ?? [],
            $data['elective_subject_ids'] ?? [],
            $data['elective_group_numbers'] ?? [],
            $data['elective_required_credits'] ?? []
        );

        return redirect()
            ->route('admin.curriculum-terms.index')
            ->with('success', 'Đã tạo kỳ chương trình khung.');
    }

    public function edit(CurriculumTerm $curriculumTerm)
    {
        $item = $curriculumTerm->load('subjects');
        $subjects = Subject::query()
            ->orderBy('ten_mon_hoc')
            ->get();

        return view('admin.curriculum-terms.form', compact('item', 'subjects'));
    }

    public function update(Request $request, CurriculumTerm $curriculumTerm)
    {
        $data = $this->validated($request, $curriculumTerm->id);

        $curriculumTerm->update([
            'ten_ky' => $data['ten_ky'],
            'thu_tu' => $data['thu_tu'],
            'ghi_chu' => $data['ghi_chu'] ?? null,
        ]);

        $this->syncSubjects(
            $curriculumTerm,
            $data['required_subject_ids'] ?? [],
            $data['elective_subject_ids'] ?? [],
            $data['elective_group_numbers'] ?? [],
            $data['elective_required_credits'] ?? []
        );

        return redirect()
            ->route('admin.curriculum-terms.index')
            ->with('success', 'Đã cập nhật kỳ chương trình khung.');
    }

    public function destroy(CurriculumTerm $curriculumTerm)
    {
        $curriculumTerm->delete();

        return redirect()
            ->route('admin.curriculum-terms.index')
            ->with('success', 'Đã xóa kỳ chương trình khung.');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return Validator::make($request->all(), [
            'ten_ky' => [
                'required',
                'string',
                'max:255',
                Rule::unique('curriculum_terms', 'ten_ky')->ignore($ignoreId),
            ],
            'thu_tu' => [
                'required',
                'integer',
                'min:1',
                'max:50',
                Rule::unique('curriculum_terms', 'thu_tu')->ignore($ignoreId),
            ],
            'ghi_chu' => ['nullable', 'string'],
            'required_subject_ids' => ['nullable', 'array'],
            'required_subject_ids.*' => ['integer', 'distinct', 'exists:subjects,id'],
            'elective_subject_ids' => ['nullable', 'array'],
            'elective_subject_ids.*' => ['integer', 'distinct', 'exists:subjects,id'],
            'elective_group_numbers' => ['nullable', 'array'],
            'elective_group_numbers.*' => ['nullable', 'integer', 'min:0', 'max:100'],
            'elective_required_credits' => ['nullable', 'array'],
            'elective_required_credits.*' => ['nullable', 'integer', 'min:0', 'max:100'],
        ], [
            'ten_ky.required' => 'Vui lòng nhập tên kỳ.',
            'ten_ky.unique' => 'Tên kỳ đã tồn tại.',
            'thu_tu.required' => 'Vui lòng nhập thứ tự kỳ.',
            'thu_tu.unique' => 'Thứ tự kỳ đã tồn tại.',
            'required_subject_ids.*.exists' => 'Có học phần bắt buộc không tồn tại trong hệ thống.',
            'elective_subject_ids.*.exists' => 'Có học phần tự chọn không tồn tại trong hệ thống.',
            'elective_group_numbers.*.integer' => 'Nhóm tự chọn phải là số nguyên.',
            'elective_required_credits.*.integer' => 'Số TC bắt buộc của nhóm phải là số nguyên.',
        ])->after(function ($validator) use ($request) {
            $requiredIds = collect($request->input('required_subject_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values();
            $electiveIds = collect($request->input('elective_subject_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values();

            if ($requiredIds->intersect($electiveIds)->isNotEmpty()) {
                $validator->errors()->add(
                    'elective_subject_ids',
                    'Một môn học chỉ được nằm trong một nhóm: bắt buộc hoặc tự chọn.'
                );
            }

            $electiveGroupNumbers = collect($request->input('elective_group_numbers', []));
            $electiveRequiredCredits = collect($request->input('elective_required_credits', []));

            foreach ($electiveIds as $subjectId) {
                $groupNumber = (int) ($electiveGroupNumbers->get((string) $subjectId, 0) ?: 0);
                $requiredCredits = (int) ($electiveRequiredCredits->get((string) $subjectId, 0) ?: 0);

                if ($groupNumber < 0) {
                    $validator->errors()->add('elective_group_numbers', 'Nhóm tự chọn không hợp lệ.');
                }

                if ($requiredCredits < 0) {
                    $validator->errors()->add('elective_required_credits', 'Số TC bắt buộc của nhóm không hợp lệ.');
                }
            }
        })->validate();
    }

    protected function syncSubjects(
        CurriculumTerm $item,
        array $requiredSubjectIds,
        array $electiveSubjectIds,
        array $electiveGroupNumbers,
        array $electiveRequiredCredits
    ): void
    {
        $syncData = [];

        foreach (array_values($requiredSubjectIds) as $index => $subjectId) {
            $syncData[(int) $subjectId] = [
                'loai_hoc_phan' => 'bat_buoc',
                'nhom_tu_chon' => 0,
                'so_tc_bat_buoc_cua_nhom' => 0,
                'sort_order' => $index + 1,
            ];
        }

        foreach (array_values($electiveSubjectIds) as $index => $subjectId) {
            $subjectId = (int) $subjectId;
            $syncData[(int) $subjectId] = [
                'loai_hoc_phan' => 'tu_chon',
                'nhom_tu_chon' => (int) ($electiveGroupNumbers[$subjectId] ?? 0),
                'so_tc_bat_buoc_cua_nhom' => (int) ($electiveRequiredCredits[$subjectId] ?? 0),
                'sort_order' => $index + 1,
            ];
        }

        $item->subjects()->sync($syncData);
    }
}
