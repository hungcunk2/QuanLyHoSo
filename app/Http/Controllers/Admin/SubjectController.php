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
        return view('admin.subjects');
    }

    public function getData(Request $request)
    {
        $query = Subject::select(
            'id',
            'ma_mon_hoc',
            'ten_mon_hoc',
            'so_tin_chi',
            'so_tiet_ly_thuyet',
            'so_tiet_thuc_hanh',
            'nhom_thuc_hanh',
            'so_tc_bat_buoc_cua_nhom',
            'created_at',
            'updated_at'
        );

        return DataTables::of($query)
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
            'nhom_thuc_hanh' => 'required|integer|min:0|max:100',
            'so_tc_bat_buoc_cua_nhom' => 'required|integer|min:0|max:100',
        ], [
            'ma_mon_hoc.required' => 'Vui lòng nhập mã môn học.',
            'ma_mon_hoc.unique' => 'Mã môn học đã tồn tại trong hệ thống.',
            'ten_mon_hoc.required' => 'Vui lòng nhập tên môn học.',
            'so_tin_chi.required' => 'Vui lòng nhập số tín chỉ.',
            'so_tiet_ly_thuyet.required' => 'Vui lòng nhập số tiết lý thuyết.',
            'so_tiet_thuc_hanh.required' => 'Vui lòng nhập số tiết thực hành.',
            'nhom_thuc_hanh.required' => 'Vui lòng nhập nhóm thực hành.',
            'so_tc_bat_buoc_cua_nhom.required' => 'Vui lòng nhập số TC bắt buộc của nhóm.',
        ]);

        $subject = Subject::create($request->only([
            'ma_mon_hoc',
            'ten_mon_hoc',
            'so_tin_chi',
            'so_tiet_ly_thuyet',
            'so_tiet_thuc_hanh',
            'nhom_thuc_hanh',
            'so_tc_bat_buoc_cua_nhom',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Tạo môn học mới thành công!',
            'data' => $subject
        ]);
    }

    public function show($id)
    {
        $subject = Subject::findOrFail($id);
        return response()->json($subject);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'ma_mon_hoc' => 'required|string|max:50|unique:subjects,ma_mon_hoc,' . $id,
            'ten_mon_hoc' => 'required|string|max:255',
            'so_tin_chi' => 'required|integer|min:0|max:30',
            'so_tiet_ly_thuyet' => 'required|integer|min:0|max:500',
            'so_tiet_thuc_hanh' => 'required|integer|min:0|max:500',
            'nhom_thuc_hanh' => 'required|integer|min:0|max:100',
            'so_tc_bat_buoc_cua_nhom' => 'required|integer|min:0|max:100',
        ]);

        $subject = Subject::findOrFail($id);
        $subject->update($request->only([
            'ma_mon_hoc',
            'ten_mon_hoc',
            'so_tin_chi',
            'so_tiet_ly_thuyet',
            'so_tiet_thuc_hanh',
            'nhom_thuc_hanh',
            'so_tc_bat_buoc_cua_nhom',
        ]));

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
}
