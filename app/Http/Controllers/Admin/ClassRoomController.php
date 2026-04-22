<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ClassRoomController extends Controller
{
    public function index()
    {
        return view('admin.classes');
    }

    public function getData(Request $request)
    {
        $query = ClassRoom::query()
            ->select('classes.id', 'classes.ma_lop', 'classes.ten_lop', 'classes.created_at', 'classes.updated_at');

        return DataTables::of($query)
            ->addColumn('check', function ($class) {
                return '<input type="checkbox" class="form-check-input row-checkbox" name="selected_ids[]" value="' . $class->id . '">';
            })
            ->addColumn('action', function ($class) {
                return '
                    <button class="btn btn-sm btn-primary me-1 edit-btn" data-id="' . $class->id . '">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="' . $class->id . '">
                        <i class="fas fa-trash"></i>
                    </button>
                ';
            })
            ->rawColumns(['check', 'action'])
            ->make(true);
    }

    public function nextMaLop()
    {
        return response()->json([
            'success' => true,
            'next_ma_lop' => ClassRoom::generateNextMaLop('PH', 2),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'ma_lop' => is_string($request->ma_lop) ? trim($request->ma_lop) : $request->ma_lop,
            'ten_lop' => is_string($request->ten_lop) ? trim($request->ten_lop) : $request->ten_lop,
        ]);

        $validated = $request->validate([
            'ma_lop' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('classes', 'ma_lop')->whereNull('deleted_at'),
            ],
            'ten_lop' => [
                'required',
                'string',
                'max:255',
                Rule::unique('classes', 'ten_lop')->whereNull('deleted_at'),
            ],
        ], [
            'ma_lop.unique' => 'Mã phòng đã tồn tại trong hệ thống.',
            'ten_lop.required' => 'Vui lòng nhập tên phòng.',
            'ten_lop.unique' => 'Tên phòng đã tồn tại trong hệ thống.',
        ]);

        $class = DB::transaction(function () use ($validated, $request) {
            $maLop = $request->ma_lop;
            if (! is_string($maLop) || $maLop === '') {
                DB::table('classes')
                    ->select('ma_lop')
                    ->where('ma_lop', 'like', 'PH%')
                    ->orderByRaw('CAST(SUBSTRING(ma_lop, 3) AS UNSIGNED) DESC')
                    ->lockForUpdate()
                    ->first();

                $maLop = ClassRoom::generateNextMaLop('PH', 2);
            }

            return ClassRoom::create([
                'ma_lop' => $maLop,
                'ten_lop' => $validated['ten_lop'],
                'giao_vien_chu_nhiem_id' => null,
                'subject_id' => null,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Tạo phòng học mới thành công!',
            'data' => $class
        ]);
    }

    public function show($id)
    {
        $class = ClassRoom::findOrFail($id);

        return response()->json($class);
    }

    public function update(Request $request, $id)
    {
        $request->merge([
            'ma_lop' => is_string($request->ma_lop) ? trim($request->ma_lop) : $request->ma_lop,
            'ten_lop' => is_string($request->ten_lop) ? trim($request->ten_lop) : $request->ten_lop,
        ]);

        $validated = $request->validate([
            'ma_lop' => [
                'required',
                'string',
                'max:50',
                Rule::unique('classes', 'ma_lop')->ignore($id)->whereNull('deleted_at'),
            ],
            'ten_lop' => [
                'required',
                'string',
                'max:255',
                Rule::unique('classes', 'ten_lop')->ignore($id)->whereNull('deleted_at'),
            ],
        ], [
            'ma_lop.required' => 'Vui lòng nhập mã phòng.',
            'ma_lop.unique' => 'Mã phòng đã tồn tại trong hệ thống.',
            'ten_lop.required' => 'Vui lòng nhập tên phòng.',
            'ten_lop.unique' => 'Tên phòng đã tồn tại trong hệ thống.',
        ]);

        $class = ClassRoom::findOrFail($id);
        $class->update([
            'ma_lop' => $validated['ma_lop'],
            'ten_lop' => $validated['ten_lop'],
            'giao_vien_chu_nhiem_id' => null,
            'subject_id' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin phòng học thành công!'
        ]);
    }

    public function destroy($id)
    {
        $class = ClassRoom::findOrFail($id);
        $class->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa phòng học thành công!'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('selected_ids', []);
        if (!is_array($ids) || count($ids) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng chọn ít nhất một phòng học để xóa.',
            ], 422);
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (count($ids) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Danh sách phòng học không hợp lệ.',
            ], 422);
        }

        $deleted = ClassRoom::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa ' . $deleted . ' phòng học.',
        ]);
    }
}
