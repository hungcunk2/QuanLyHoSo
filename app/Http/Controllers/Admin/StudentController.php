<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function index()
    {
        return view('admin.students');
    }

    public function getData(Request $request)
    {
        $query = User::select('id', 'mssv', 'ho_ten', 'lop', 'so_dien_thoai', 'ngay_sinh', 'created_at', 'updated_at');

        return DataTables::of($query)
            ->addColumn('check', function ($student) {
                return '<input type="checkbox" class="form-check-input row-checkbox" name="selected_ids[]" value="' . $student->id . '">';
            })
            ->addColumn('action', function ($student) {
                return '
                    <button class="btn btn-sm btn-primary me-1 edit-btn" data-id="' . $student->id . '">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="' . $student->id . '">
                        <i class="fas fa-trash"></i>
                    </button>
                ';
            })
            ->editColumn('ngay_sinh', function ($student) {
                return $student->ngay_sinh ? $student->ngay_sinh->format('d/m/Y') : '';
            })
            ->rawColumns(['check', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $student = User::findOrFail($id);
        return response()->json($student);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'mssv' => 'required|string|max:50|unique:users,mssv,' . $id,
            'ho_ten' => 'required|string|max:255',
            'lop' => 'nullable|string|max:50',
            'so_dien_thoai' => 'nullable|string|max:20',
            'ngay_sinh' => 'nullable|date',
            'dia_chi' => 'nullable|string',
            'ho_ten_cha' => 'nullable|string|max:255',
            'sdt_cha' => 'nullable|string|max:20',
            'ho_ten_me' => 'nullable|string|max:255',
            'sdt_me' => 'nullable|string|max:20',
        ]);

        $student = User::findOrFail($id);
        $student->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin học sinh thành công!'
        ]);
    }

    public function destroy($id)
    {
        $student = User::findOrFail($id);
        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa học sinh thành công!'
        ]);
    }
}
