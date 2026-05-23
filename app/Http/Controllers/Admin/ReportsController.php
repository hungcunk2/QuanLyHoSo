<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacultyReportStat;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $hocKyOptions = FacultyReportStat::reportTermOptions();
        $hocKyKeys = array_keys($hocKyOptions);

        $this->ensureTermRecordsExist($hocKyKeys);

        $selectedHocKy = (string) $request->query('hoc_ky', $hocKyKeys[0]);
        if (! in_array($selectedHocKy, $hocKyKeys, true)) {
            $selectedHocKy = $hocKyKeys[0];
        }

        $stat = FacultyReportStat::query()
            ->where('hoc_ky', $selectedHocKy)
            ->firstOrFail();

        return view('admin.reports.index', [
            'stat' => $stat,
            'hocKyOptions' => $hocKyOptions,
            'selectedHocKy' => $selectedHocKy,
        ]);
    }

    /**
     * @param  list<string>  $hocKyKeys
     */
    protected function ensureTermRecordsExist(array $hocKyKeys): void
    {
        foreach ($hocKyKeys as $hocKy) {
            FacultyReportStat::query()->firstOrCreate(
                ['hoc_ky' => $hocKy],
                [
                    'ghi_chu' => 'Nhập số liệu kỳ này trong bảng faculty_report_stats (cột hoc_ky = '.$hocKy.').',
                ]
            );
        }
    }
}
