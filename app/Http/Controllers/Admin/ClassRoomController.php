<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Teacher;
use App\Models\Subject;
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
        $query = ClassRoom::with(['giaoVienChuNhiem', 'monHoc'])
            ->select('classes.id', 'classes.ma_lop', 'classes.ten_lop', 'classes.giao_vien_chu_nhiem_id', 'classes.subject_id', 'classes.created_at', 'classes.updated_at');

        return DataTables::of($query)
            ->addColumn('check', function ($class) {
                return '<input type="checkbox" class="form-check-input row-checkbox" name="selected_ids[]" value="' . $class->id . '">';
            })
            ->addColumn('giao_vien_chu_nhiem', function ($class) {
                return $class->giaoVienChuNhiem ? $class->giaoVienChuNhiem->ho_ten : 'Chưa có';
            })
            ->addColumn('mon_hoc', function ($class) {
                return $class->monHoc ? $class->monHoc->ten_mon_hoc : 'Chưa có';
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
        $request->validate([
            'ma_lop' => 'required|string|max:50|unique:classes,ma_lop',
            'ten_lop' => 'required|string|max:255',
            'giao_vien_chu_nhiem_id' => 'nullable|exists:teachers,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ], [
            'ma_lop.required' => 'Vui lòng nhập mã lớp.',
            'ma_lop.unique' => 'Mã lớp đã tồn tại trong hệ thống.',
            'ten_lop.required' => 'Vui lòng nhập tên lớp.',
            'giao_vien_chu_nhiem_id.exists' => 'Giáo viên không tồn tại.',
            'subject_id.exists' => 'Môn học không tồn tại.',
        ]);

        $class = ClassRoom::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tạo lớp học mới thành công!',
            'data' => $class
        ]);
    }

    public function show($id)
    {
        $class = ClassRoom::with(['giaoVienChuNhiem', 'monHoc'])->findOrFail($id);
        return response()->json($class);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'ma_lop' => 'required|string|max:50|unique:classes,ma_lop,' . $id,
            'ten_lop' => 'required|string|max:255',
            'giao_vien_chu_nhiem_id' => 'nullable|exists:teachers,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $class = ClassRoom::findOrFail($id);
        $class->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin lớp học thành công!'
        ]);
    }

    public function destroy($id)
    {
        $class = ClassRoom::findOrFail($id);
        $class->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa lớp học thành công!'
        ]);
    }
}
