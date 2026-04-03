<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lop;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LopController extends Controller
{
    public function getData(Request $request)
    {
        $query = Lop::query()->select('lops.id', 'lops.ma_lop', 'lops.ten_lop', 'lops.created_at');

        return DataTables::of($query)
            ->addColumn('action', function (Lop $lop) {
                return '<div class="d-inline-flex gap-2 align-items-center">'
                    . '<button type="button" class="btn btn-sm btn-primary edit-lop-btn" data-id="' . $lop->id . '" title="Sửa"><i class="fas fa-edit"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-danger delete-lop-btn" data-id="' . $lop->id . '" data-ma="' . e($lop->ma_lop) . '" title="Xóa"><i class="fas fa-trash"></i></button>'
                    . '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ma_lop' => 'required|string|max:50|unique:lops,ma_lop',
            'ten_lop' => 'required|string|max:255',
        ], [
            'ma_lop.required' => 'Vui lòng nhập mã lớp.',
            'ma_lop.unique' => 'Mã lớp đã tồn tại.',
            'ten_lop.required' => 'Vui lòng nhập tên lớp.',
        ]);

        $lop = Lop::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm lớp mới.',
            'data' => $lop,
        ]);
    }

    public function show($id)
    {
        $lop = Lop::findOrFail($id);

        return response()->json($lop);
    }

    public function update(Request $request, $id)
    {
        $lop = Lop::findOrFail($id);

        $validated = $request->validate([
            'ma_lop' => 'required|string|max:50|unique:lops,ma_lop,' . $id,
            'ten_lop' => 'required|string|max:255',
        ], [
            'ma_lop.required' => 'Vui lòng nhập mã lớp.',
            'ma_lop.unique' => 'Mã lớp đã tồn tại.',
            'ten_lop.required' => 'Vui lòng nhập tên lớp.',
        ]);

        $lop->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật lớp.',
        ]);
    }

    public function destroy($id)
    {
        $lop = Lop::findOrFail($id);
        $lop->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa lớp.',
        ]);
    }
}
