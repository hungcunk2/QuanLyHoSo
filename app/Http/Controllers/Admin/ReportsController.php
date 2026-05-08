<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseOffering;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        // Filters (optional)
        $khoaHoc = trim((string) $request->query('khoa_hoc', ''));
        $lop = trim((string) $request->query('lop', ''));

        $studentsQuery = Student::query();
        if ($khoaHoc !== '') {
            $studentsQuery->where('khoa_hoc', $khoaHoc);
        }
        if ($lop !== '') {
            $studentsQuery->where('lop', $lop);
        }

        $totalStudents = (clone $studentsQuery)->count();

        // Data completeness
        $missingFields = [
            'ma_ho_so' => 'Mã hồ sơ',
            'so_cccd' => 'CCCD',
            'ngay_sinh' => 'Ngày sinh',
            'so_dien_thoai' => 'SĐT',
            'email' => 'Email',
            'dia_chi' => 'Địa chỉ',
            'avatar' => 'Ảnh đại diện',
        ];

        $missingCounts = [];
        foreach (array_keys($missingFields) as $field) {
            $missingCounts[$field] = (clone $studentsQuery)
                ->where(function ($q) use ($field) {
                    $q->whereNull($field);
                    if (!in_array($field, ['ngay_sinh'], true)) {
                        $q->orWhere($field, '=', '');
                    }
                })
                ->count();
        }

        $completenessPct = 100;
        if ($totalStudents > 0) {
            // "Complete" means no missing on key identity fields.
            $completeCount = (clone $studentsQuery)
                ->whereNotNull('mssv')->where('mssv', '!=', '')
                ->whereNotNull('ho_ten')->where('ho_ten', '!=', '')
                ->whereNotNull('lop')->where('lop', '!=', '')
                ->whereNotNull('khoa_hoc')->where('khoa_hoc', '!=', '')
                ->count();
            $completenessPct = (int) round($completeCount * 100 / $totalStudents);
        }

        // Duplicates (top lists)
        $dupSpecs = [
            'mssv' => 'MSSV',
            'so_cccd' => 'CCCD',
            'email' => 'Email',
            'ma_ho_so' => 'Mã hồ sơ',
        ];

        $duplicates = [];
        foreach ($dupSpecs as $field => $label) {
            $duplicates[$field] = (clone $studentsQuery)
                ->select($field, DB::raw('COUNT(*) as cnt'))
                ->whereNotNull($field)
                ->where($field, '!=', '')
                ->groupBy($field)
                ->having('cnt', '>', 1)
                ->orderByDesc('cnt')
                ->limit(10)
                ->get();
        }

        // Academic operations: offerings by semester
        $offeringsBase = CourseOffering::query()->where('is_cancelled', false);
        $offeringsByDot = (clone $offeringsBase)
            ->select('hoc_ky', 'khoa_hoc', DB::raw('COUNT(*) as cnt'))
            ->groupBy('hoc_ky', 'khoa_hoc')
            ->orderByDesc('khoa_hoc')
            ->orderByDesc('hoc_ky')
            ->limit(12)
            ->get();

        // Grades finalized progress
        $totalOfferings = (clone $offeringsBase)->count();
        $finalizedOfferings = (clone $offeringsBase)->whereNotNull('grades_finalized_at')->count();
        $finalizedPct = $totalOfferings > 0 ? (int) round($finalizedOfferings * 100 / $totalOfferings) : 0;

        $finalizedByDot = (clone $offeringsBase)
            ->select(
                'hoc_ky',
                'khoa_hoc',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN grades_finalized_at IS NULL THEN 0 ELSE 1 END) as finalized')
            )
            ->groupBy('hoc_ky', 'khoa_hoc')
            ->orderByDesc('khoa_hoc')
            ->orderByDesc('hoc_ky')
            ->limit(12)
            ->get()
            ->map(function ($r) {
                $total = (int) ($r->total ?? 0);
                $finalized = (int) ($r->finalized ?? 0);
                $pct = $total > 0 ? (int) round($finalized * 100 / $total) : 0;
                $r->pct = $pct;
                return $r;
            });

        // Filter options
        $khoaHocOptions = Student::query()
            ->whereNotNull('khoa_hoc')->where('khoa_hoc', '!=', '')
            ->distinct()
            ->orderBy('khoa_hoc')
            ->pluck('khoa_hoc');

        $lopOptions = Student::query()
            ->whereNotNull('lop')->where('lop', '!=', '')
            ->distinct()
            ->orderBy('lop')
            ->pluck('lop');

        return view('admin.reports.index', [
            'khoaHoc' => $khoaHoc,
            'lop' => $lop,
            'khoaHocOptions' => $khoaHocOptions,
            'lopOptions' => $lopOptions,

            'totalStudents' => $totalStudents,
            'missingFields' => $missingFields,
            'missingCounts' => $missingCounts,
            'completenessPct' => $completenessPct,

            'duplicates' => $duplicates,

            'totalOfferings' => $totalOfferings,
            'finalizedOfferings' => $finalizedOfferings,
            'finalizedPct' => $finalizedPct,
            'offeringsByDot' => $offeringsByDot,
            'finalizedByDot' => $finalizedByDot,
        ]);
    }

    public function exportDuplicates(Request $request, string $field): StreamedResponse
    {
        $allowed = ['mssv', 'so_cccd', 'email', 'ma_ho_so'];
        abort_unless(in_array($field, $allowed, true), 404);

        $filename = 'duplicates_'.$field.'_'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($field) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [$field, 'count'], ',');

            Student::query()
                ->select($field, DB::raw('COUNT(*) as cnt'))
                ->whereNotNull($field)
                ->where($field, '!=', '')
                ->groupBy($field)
                ->having('cnt', '>', 1)
                ->orderByDesc('cnt')
                ->chunk(500, function ($rows) use ($out, $field) {
                    foreach ($rows as $r) {
                        fputcsv($out, [(string) $r->{$field}, (int) $r->cnt], ',');
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

