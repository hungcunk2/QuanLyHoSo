<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacultyReportStat extends Model
{
    /** @return array<string, string> hoc_ky => nhãn hiển thị */
    public static function reportTermOptions(): array
    {
        return [
            'HK1-2026-2027' => 'Học kỳ 1 (2026-2027)',
            'HK2-2025-2026' => 'Học kỳ 2 (2025-2026)',
            'HK3-2025-2026' => 'Học kỳ 3 — Hè (2025-2026)',
        ];
    }

    protected $table = 'faculty_report_stats';

    protected $fillable = [
        'hoc_ky',
        'sv_nam_1',
        'sv_nam_2',
        'sv_nam_3',
        'sv_nam_4',
        'sv_sau_nam_4',
        'canh_bao_lan_1',
        'canh_bao_lan_2',
        'nghi_hoc',
        'lop_hp_mo_ky_nay',
        'lop_hp_da_chot_diem',
        'lop_ca_lop_kha',
        'lop_ca_lop_gioi',
        'lop_ca_lop_trung_binh',
        'sv_rot_mon',
        'sv_bao_luu',
        'ghi_chu',
    ];

    protected function casts(): array
    {
        return [
            'sv_nam_1' => 'integer',
            'sv_nam_2' => 'integer',
            'sv_nam_3' => 'integer',
            'sv_nam_4' => 'integer',
            'sv_sau_nam_4' => 'integer',
            'canh_bao_lan_1' => 'integer',
            'canh_bao_lan_2' => 'integer',
            'nghi_hoc' => 'integer',
            'lop_hp_mo_ky_nay' => 'integer',
            'lop_hp_da_chot_diem' => 'integer',
            'lop_ca_lop_kha' => 'integer',
            'lop_ca_lop_gioi' => 'integer',
            'lop_ca_lop_trung_binh' => 'integer',
            'sv_rot_mon' => 'integer',
            'sv_bao_luu' => 'integer',
        ];
    }

    public function totalStudentsByYear(): int
    {
        return (int) $this->sv_nam_1
            + (int) $this->sv_nam_2
            + (int) $this->sv_nam_3
            + (int) $this->sv_nam_4
            + (int) $this->sv_sau_nam_4;
    }

    public function totalAcademicWarnings(): int
    {
        return (int) $this->canh_bao_lan_1
            + (int) $this->canh_bao_lan_2
            + (int) $this->nghi_hoc;
    }
}
