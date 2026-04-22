<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class LopController extends Controller
{
    public function index()
    {
        return view('admin.lops');
    }

    public function getData(Request $request)
    {
        $query = Lop::query()->select('lops.id', 'lops.ma_lop', 'lops.ten_lop', 'lops.created_at');

        return DataTables::of($query)
            ->addColumn('check', function (Lop $lop) {
                return '<input type="checkbox" class="form-check-input lop-row-checkbox" name="selected_ids[]" value="' . $lop->id . '">';
            })
            ->addColumn('action', function (Lop $lop) {
                return '<button type="button" class="btn btn-sm btn-primary me-1 edit-lop-btn" data-id="' . $lop->id . '" title="Sửa">'
                    . '<i class="fas fa-edit"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-danger delete-lop-btn" data-id="' . $lop->id . '" data-ma="' . e($lop->ma_lop) . '" title="Xóa">'
                    . '<i class="fas fa-trash"></i></button>';
            })
            ->rawColumns(['check', 'action'])
            ->make(true);
    }

    public function nextMaLop()
    {
        return response()->json([
            'success' => true,
            'next_ma_lop' => Lop::generateNextMaLop('ML', 2),
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
                Rule::unique('lops', 'ma_lop'),
            ],
            'ten_lop' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lops', 'ten_lop'),
            ],
        ], [
            'ma_lop.unique' => 'Mã lớp đã tồn tại.',
            'ten_lop.required' => 'Vui lòng nhập tên lớp.',
            'ten_lop.unique' => 'Tên lớp đã tồn tại.',
        ]);

        $lop = DB::transaction(function () use ($validated, $request) {
            $ma = $request->ma_lop;
            if (! is_string($ma) || $ma === '') {
                DB::table('lops')
                    ->select('ma_lop')
                    ->where('ma_lop', 'like', 'ML%')
                    ->orderByRaw('CAST(SUBSTRING(ma_lop, 3) AS UNSIGNED) DESC')
                    ->lockForUpdate()
                    ->first();

                $ma = Lop::generateNextMaLop('ML', 2);
            }

            return Lop::create([
                'ma_lop' => $ma,
                'ten_lop' => $validated['ten_lop'],
            ]);
        });

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

        $request->merge([
            'ma_lop' => is_string($request->ma_lop) ? trim($request->ma_lop) : $request->ma_lop,
            'ten_lop' => is_string($request->ten_lop) ? trim($request->ten_lop) : $request->ten_lop,
        ]);

        $validated = $request->validate([
            'ma_lop' => [
                'required',
                'string',
                'max:50',
                Rule::unique('lops', 'ma_lop')->ignore($id),
            ],
            'ten_lop' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lops', 'ten_lop')->ignore($id),
            ],
        ], [
            'ma_lop.required' => 'Vui lòng nhập mã lớp.',
            'ma_lop.unique' => 'Mã lớp đã tồn tại.',
            'ten_lop.required' => 'Vui lòng nhập tên lớp.',
            'ten_lop.unique' => 'Tên lớp đã tồn tại.',
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

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('selected_ids', []);
        if (! is_array($ids) || count($ids) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng chọn ít nhất một lớp để xóa.',
            ], 422);
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (count($ids) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Danh sách lớp không hợp lệ.',
            ], 422);
        }

        $deleted = Lop::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa ' . $deleted . ' lớp.',
        ]);
    }
}
