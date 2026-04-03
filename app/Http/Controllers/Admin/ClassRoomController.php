<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ma_lop' => 'required|string|max:50|unique:classes,ma_lop',
            'ten_lop' => 'required|string|max:255',
        ], [
            'ma_lop.required' => 'Vui lòng nhập mã phòng.',
            'ma_lop.unique' => 'Mã phòng đã tồn tại trong hệ thống.',
            'ten_lop.required' => 'Vui lòng nhập tên phòng.',
        ]);

        $class = ClassRoom::create([
            'ma_lop' => $validated['ma_lop'],
            'ten_lop' => $validated['ten_lop'],
            'giao_vien_chu_nhiem_id' => null,
            'subject_id' => null,
        ]);

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
        $validated = $request->validate([
            'ma_lop' => 'required|string|max:50|unique:classes,ma_lop,' . $id,
            'ten_lop' => 'required|string|max:255',
        ], [
            'ma_lop.required' => 'Vui lòng nhập mã phòng.',
            'ma_lop.unique' => 'Mã phòng đã tồn tại trong hệ thống.',
            'ten_lop.required' => 'Vui lòng nhập tên phòng.',
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
