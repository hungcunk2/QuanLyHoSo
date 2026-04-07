<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubjectRegistration;
use App\Models\CourseOffering;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class SubjectRegistrationController extends Controller
{
    public function index()
    {
        $classes = ClassRoom::orderBy('ma_lop')->get(['id', 'ma_lop', 'ten_lop']);
        $subjects = Subject::orderBy('ma_mon_hoc')->get(['id', 'ma_mon_hoc', 'ten_mon_hoc']);
        $teachers = Teacher::orderBy('ho_ten')->get(['id', 'msgv', 'ho_ten']);
        $weekdays = CourseOffering::weekdays();
        $periodLabels = CourseOffering::periodLabels();
        return view('admin.subject-registrations', compact('classes', 'subjects', 'teachers', 'weekdays', 'periodLabels'));
    }

    public function getData(Request $request)
    {
        $query = CourseOffering::with(['subject', 'classRoom', 'teacherLyThuyet', 'teacherThucHanh'])
            ->orderBy('created_at', 'desc');

        $weekdays = CourseOffering::weekdays();

        return DataTables::of($query)
            ->orderColumn('created_at_formatted', 'created_at $1')
            ->addColumn('created_at_formatted', function ($row) {
                return $row->created_at ? $row->created_at->format('d/m/Y H:i') : '—';
            })
            ->addColumn('subject_info', function ($row) {
                return $row->subject ? $row->subject->ma_mon_hoc . ' - ' . $row->subject->ten_mon_hoc : '—';
            })
            ->addColumn('class_info', function ($row) {
                return $row->classRoom ? $row->classRoom->ma_lop . ' - ' . $row->classRoom->ten_lop : '—';
            })
            ->addColumn('teacher_info', function ($row) {
                $lt = $row->teacherLyThuyet?->ho_ten;
                $th = $row->teacherThucHanh?->ho_ten;
                if ($lt && $th && $lt !== $th) {
                    return 'LT: ' . $lt . '<br>TH: ' . $th;
                }
                if ($lt) {
                    return $lt;
                }
                if ($th) {
                    return $th;
                }
                return '—';
            })
            ->addColumn('date_range', function ($row) {
                $start = $row->ngay_bat_dau_hoc ? $row->ngay_bat_dau_hoc->format('d/m/Y') : '—';
                $end = $row->ngay_ket_thuc_hoc ? $row->ngay_ket_thuc_hoc->format('d/m/Y') : '—';
                return $start . ' → ' . $end;
            })
            ->addColumn('schedule_summary', function ($row) use ($weekdays) {
                $parts = [];
                if ($row->thu_ly_thuyet && $row->tiet_ly_thuyet) {
                    $parts[] = 'LT: ' . ($weekdays[$row->thu_ly_thuyet] ?? 'T' . $row->thu_ly_thuyet) . ' tiết ' . $row->tiet_ly_thuyet;
                }
                if ($row->thu_thuc_hanh && $row->tiet_thuc_hanh) {
                    $parts[] = 'TH: ' . ($weekdays[$row->thu_thuc_hanh] ?? 'T' . $row->thu_thuc_hanh) . ' tiết ' . $row->tiet_thuc_hanh;
                }
                return $parts ? implode('; ', $parts) : '—';
            })
            ->addColumn('offering_status', function ($row) {
                $today = Carbon::today();
                if ($row->ngay_bat_dau_hoc && $row->ngay_bat_dau_hoc->lte($today)) {
                    return '<span class="badge bg-success">Đang học</span>';
                }
                if (
                    $row->ngay_mo_dang_ky && $row->ngay_ket_thuc_dang_ky &&
                    $row->ngay_mo_dang_ky->lte($today) && $row->ngay_ket_thuc_dang_ky->gte($today)
                ) {
                    return '<span class="badge bg-warning text-dark">Đang chờ sinh viên đăng kí</span>';
                }
                return '<span class="badge bg-light text-dark">—</span>';
            })
            ->addColumn('action', function ($row) {
                $daBatDau = $row->ngay_bat_dau_hoc && $row->ngay_bat_dau_hoc->lte(Carbon::today());
                if ($daBatDau) {
                    $nameAttr = e($row->ten_hoc_phan);
                    return '<div class="d-inline-flex gap-2 align-items-center flex-wrap">'
                        . '<span class="text-muted small me-1" title="Không cho chỉnh sửa khi đã bắt đầu học"><i class="fas fa-lock me-1"></i>Đã bắt đầu</span>'
                        . '<button type="button" class="btn btn-sm btn-danger delete-offering-btn" data-id="' . $row->id . '" data-name="' . $nameAttr . '" title="Xóa học phần"><i class="fas fa-trash"></i></button>'
                        . '</div>';
                }
                $nameAttr = e($row->ten_hoc_phan);

                return '<div class="d-inline-flex gap-2 align-items-center flex-wrap">'
                    . '<button type="button" class="btn btn-sm btn-primary edit-offering-btn" data-id="' . $row->id . '" title="Chỉnh sửa"><i class="fas fa-edit"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-danger delete-offering-btn" data-id="' . $row->id . '" data-name="' . $nameAttr . '" title="Xóa học phần"><i class="fas fa-trash"></i></button>'
                    . '</div>';
            })
            ->rawColumns(['teacher_info', 'offering_status', 'action'])
            ->make(true);
    }
}
