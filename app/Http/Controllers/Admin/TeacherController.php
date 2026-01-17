<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TeacherController extends Controller
{
    public function index()
    {
        return view('admin.teachers');
    }

    public function getData(Request $request)
    {
        $query = Teacher::select('id', 'msgv', 'ho_ten', 'chuyen_mon', 'sdt', 'email', 'ngay_sinh', 'created_at', 'updated_at');

        return DataTables::of($query)
            ->addColumn('check', function ($teacher) {
                return '<input type="checkbox" class="form-check-input row-checkbox" name="selected_ids[]" value="' . $teacher->id . '">';
            })
            ->addColumn('action', function ($teacher) {
                return '
                    <button class="btn btn-sm btn-primary me-1 edit-btn" data-id="' . $teacher->id . '">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="' . $teacher->id . '">
                        <i class="fas fa-trash"></i>
                    </button>
                ';
            })
            ->editColumn('ngay_sinh', function ($teacher) {
                return $teacher->ngay_sinh ? $teacher->ngay_sinh->format('d/m/Y') : '';
            })
            ->rawColumns(['check', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $teacher = Teacher::findOrFail($id);
        return response()->json($teacher);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'msgv' => 'required|string|max:50|unique:teachers,msgv,' . $id,
            'ho_ten' => 'required|string|max:255',
            'chuyen_mon' => 'nullable|string|max:255',
            'sdt' => 'nullable|string|max:20',
            'dia_chi' => 'nullable|string',
            'email' => 'nullable|email|max:255|unique:teachers,email,' . $id,
            'ngay_sinh' => 'nullable|date',
        ]);

        $teacher = Teacher::findOrFail($id);
        $teacher->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin giáo viên thành công!'
        ]);
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        $teacher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa giáo viên thành công!'
        ]);
    }
}
