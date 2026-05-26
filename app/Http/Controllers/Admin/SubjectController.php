<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SubjectController extends Controller
{
    public function index()
    {
        $subjectOptions = Subject::query()
            ->orderBy('ma_mon_hoc')
            ->get(['id', 'ma_mon_hoc', 'ten_mon_hoc']);

        return view('admin.subjects', compact('subjectOptions'));
    }

    public function getData(Request $request)
    {
        $query = Subject::query()
            ->with('monTienQuyet:id,ma_mon_hoc,ten_mon_hoc')
            ->select(
                'id',
                'ma_mon_hoc',
                'ten_mon_hoc',
                'mon_tien_quyet_id',
                'so_tin_chi',
                'so_tiet_ly_thuyet',
                'so_tiet_thuc_hanh',
                'nhom_thuc_hanh',
                'so_tc_bat_buoc_cua_nhom',
                'created_at',
                'updated_at'
            )
            ->orderByDesc('created_at');

        return DataTables::of($query)
            ->orderColumn('created_at', 'subjects.created_at $1')
            ->addColumn('mon_tien_quyet_label', function ($subject) {
                $pre = $subject->monTienQuyet;
                if (! $pre) {
                    return '—';
                }

                return $pre->ma_mon_hoc.' - '.$pre->ten_mon_hoc;
            })
            ->editColumn('nhom_thuc_hanh', function ($subject) {
                return (string) (int) ($subject->nhom_thuc_hanh ?? 0);
            })
            ->editColumn('so_tc_bat_buoc_cua_nhom', function ($subject) {
                return (string) (int) ($subject->so_tc_bat_buoc_cua_nhom ?? 0);
            })
            ->addColumn('check', function ($subject) {
                return '<input type="checkbox" class="form-check-input row-checkbox" name="selected_ids[]" value="' . $subject->id . '">';
            })
            ->addColumn('action', function ($subject) {
                return '
                    <button class="btn btn-sm btn-primary me-1 edit-btn" data-id="' . $subject->id . '">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="' . $subject->id . '">
                        <i class="fas fa-trash"></i>
                    </button>
                ';
            })
            ->rawColumns(['check', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ma_mon_hoc' => 'required|string|max:50|unique:subjects,ma_mon_hoc',
            'ten_mon_hoc' => 'required|string|max:255',
            'so_tin_chi' => 'required|integer|min:0|max:30',
            'so_tiet_ly_thuyet' => 'required|integer|min:0|max:500',
            'so_tiet_thuc_hanh' => 'required|integer|min:0|max:500',
            'nhom_thuc_hanh' => 'nullable|integer|min:0|max:100',
            'so_tc_bat_buoc_cua_nhom' => 'nullable|integer|min:0|max:100',
            'mon_tien_quyet_id' => 'nullable|integer|exists:subjects,id',
        ], [
            'ma_mon_hoc.required' => 'Vui lòng nhập mã môn học.',
            'ma_mon_hoc.unique' => 'Mã môn học đã tồn tại trong hệ thống.',
            'ten_mon_hoc.required' => 'Vui lòng nhập tên môn học.',
            'so_tin_chi.required' => 'Vui lòng nhập số tín chỉ.',
            'so_tiet_ly_thuyet.required' => 'Vui lòng nhập số tiết lý thuyết.',
            'so_tiet_thuc_hanh.required' => 'Vui lòng nhập số tiết thực hành.',
            'mon_tien_quyet_id.exists' => 'Môn tiên quyết không hợp lệ.',
        ]);

        $payload = $this->subjectPayloadFromRequest($request);

        $subject = Subject::create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Tạo môn học mới thành công!',
            'data' => $subject
        ]);
    }

    public function show($id)
    {
        $subject = Subject::with('monTienQuyet:id,ma_mon_hoc,ten_mon_hoc')->findOrFail($id);

        return response()->json($subject);
    }

    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);

        $request->validate([
            'ma_mon_hoc' => 'required|string|max:50|unique:subjects,ma_mon_hoc,'.$id,
            'ten_mon_hoc' => 'required|string|max:255',
            'so_tin_chi' => 'required|integer|min:0|max:30',
            'so_tiet_ly_thuyet' => 'required|integer|min:0|max:500',
            'so_tiet_thuc_hanh' => 'required|integer|min:0|max:500',
            'nhom_thuc_hanh' => 'nullable|integer|min:0|max:100',
            'so_tc_bat_buoc_cua_nhom' => 'nullable|integer|min:0|max:100',
            'mon_tien_quyet_id' => 'nullable|integer|exists:subjects,id|not_in:'.$id,
        ], [
            'mon_tien_quyet_id.not_in' => 'Môn học không thể là tiên quyết của chính nó.',
        ]);

        $payload = $this->subjectPayloadFromRequest($request);

        $subject->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin môn học thành công!'
        ]);
    }

    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa môn học thành công!'
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function subjectPayloadFromRequest(Request $request): array
    {
        $payload = $request->only([
            'ma_mon_hoc',
            'ten_mon_hoc',
            'mon_tien_quyet_id',
            'so_tin_chi',
            'so_tiet_ly_thuyet',
            'so_tiet_thuc_hanh',
            'nhom_thuc_hanh',
            'so_tc_bat_buoc_cua_nhom',
        ]);

        $payload['mon_tien_quyet_id'] = filled($payload['mon_tien_quyet_id'] ?? null)
            ? (int) $payload['mon_tien_quyet_id']
            : null;
        $payload['nhom_thuc_hanh'] = filled($payload['nhom_thuc_hanh'] ?? null)
            ? (int) $payload['nhom_thuc_hanh']
            : 0;
        $payload['so_tc_bat_buoc_cua_nhom'] = filled($payload['so_tc_bat_buoc_cua_nhom'] ?? null)
            ? (int) $payload['so_tc_bat_buoc_cua_nhom']
            : 0;

        return $payload;
    }
}
