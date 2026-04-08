<?php

namespace App\Exports;

use App\Models\CourseOffering;
use App\Models\CourseOfferingGrade;
use App\Models\SubjectRegistration;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class CourseOfferingGradesExport implements FromArray, WithTitle
{
    public function __construct(
        protected CourseOffering $courseOffering,
    ) {}

    public function title(): string
    {
        return 'Bang diem';
    }

    public function array(): array
    {
        $o = $this->courseOffering->loadMissing(['subject', 'classRoom']);

        $registrations = SubjectRegistration::query()
            ->where('course_offering_id', $o->id)
            ->where('status', '!=', 'cancelled')
            ->with('student')
            ->orderBy('created_at')
            ->get();

        $grades = CourseOfferingGrade::query()
            ->where('course_offering_id', $o->id)
            ->get()
            ->keyBy('student_id');

        $rows = [];
        $rows[] = ['BẢNG ĐIỂM'];
        $rows[] = ['Học phần', $o->ten_hoc_phan];
        if ($o->subject) {
            $rows[] = ['Môn', $o->subject->ma_mon_hoc . ' — ' . $o->subject->ten_mon_hoc];
            $rows[] = ['Số tín chỉ', $o->subject->so_tin_chi];
        }
        if ($o->classRoom) {
            $rows[] = ['Phòng', $o->classRoom->ma_lop . ' — ' . $o->classRoom->ten_lop];
        }
        $rows[] = [];

        $rows[] = [
            'MSSV',
            'Họ tên',
            'Giữa kỳ',
            'TX1', 'TX2', 'TX3', 'TX4', 'TX5',
            'TH1', 'TH2', 'TH3', 'TH4', 'TH5',
            'Cuối kỳ',
            'Điểm tổng kết',
            'Thang điểm 4',
            'Điểm chữ',
            'Xếp loại',
        ];

        foreach ($registrations as $reg) {
            $s = $reg->student;
            if (! $s) continue;

            $g = $grades[$s->id] ?? null;
            $tx = is_array($g?->thuong_xuyen) ? $g->thuong_xuyen : [];
            $th = is_array($g?->thuc_hanh) ? $g->thuc_hanh : [];

            $rows[] = [
                $s->mssv ?? '',
                $s->ho_ten ?? '',
                $g?->giua_ky ?? '',
                $tx[1] ?? '', $tx[2] ?? '', $tx[3] ?? '', $tx[4] ?? '', $tx[5] ?? '',
                $th[1] ?? '', $th[2] ?? '', $th[3] ?? '', $th[4] ?? '', $th[5] ?? '',
                $g?->cuoi_ky ?? '',
                $g?->diem_tong_ket ?? '',
                $g?->thang_diem_4 ?? '',
                $g?->diem_chu ?? '',
                $g?->xep_loai ?? '',
            ];
        }

        return $rows;
    }
}

