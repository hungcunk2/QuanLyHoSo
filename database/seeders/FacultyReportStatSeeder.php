<?php

namespace Database\Seeders;

use App\Models\FacultyReportStat;
use Illuminate\Database\Seeder;

class FacultyReportStatSeeder extends Seeder
{
    public function run(): void
    {
        FacultyReportStat::query()->updateOrCreate(
            ['hoc_ky' => 'HK1-2026-2027'],
            [
                'sv_nam_1' => 1280,
                'sv_nam_2' => 1180,
                'sv_nam_3' => 1060,
                'sv_nam_4' => 890,
                'sv_sau_nam_4' => 310,
                'canh_bao_lan_1' => 74,
                'canh_bao_lan_2' => 55,
                'nghi_hoc' => 15,
                'lop_hp_mo_ky_nay' => 352,
                'lop_hp_da_chot_diem' => 330,
                'lop_ca_lop_gioi' => 50,
                'lop_ca_lop_kha' => 265,
                'lop_ca_lop_trung_binh' => 15,
                'sv_rot_mon' => 138,
                'sv_bao_luu' => 12,
                'ghi_chu' => 'Cảnh báo khác (nếu có): 4. Tổng SV khoa = 4720.',
            ]
        );

        FacultyReportStat::query()->updateOrCreate(
            ['hoc_ky' => 'HK2-2025-2026'],
            [
                'sv_nam_1' => 1270,
                'sv_nam_2' => 1175,
                'sv_nam_3' => 1055,
                'sv_nam_4' => 885,
                'sv_sau_nam_4' => 310,
                'canh_bao_lan_1' => 68,
                'canh_bao_lan_2' => 50,
                'nghi_hoc' => 14,
                'lop_hp_mo_ky_nay' => 345,
                'lop_hp_da_chot_diem' => 322,
                'lop_ca_lop_gioi' => 54,
                'lop_ca_lop_kha' => 252,
                'lop_ca_lop_trung_binh' => 16,
                'sv_rot_mon' => 126,
                'sv_bao_luu' => 10,
                'ghi_chu' => 'Cảnh báo khác (nếu có): 4. Tổng SV khoa = 4695.',
            ]
        );

        FacultyReportStat::query()->updateOrCreate(
            ['hoc_ky' => 'HK3-2025-2026'],
            [
                'sv_nam_1' => 180,
                'sv_nam_2' => 320,
                'sv_nam_3' => 410,
                'sv_nam_4' => 320,
                'sv_sau_nam_4' => 150,
                'canh_bao_lan_1' => 18,
                'canh_bao_lan_2' => 13,
                'nghi_hoc' => 4,
                'lop_hp_mo_ky_nay' => 96,
                'lop_hp_da_chot_diem' => 90,
                'lop_ca_lop_gioi' => 18,
                'lop_ca_lop_kha' => 67,
                'lop_ca_lop_trung_binh' => 5,
                'sv_rot_mon' => 34,
                'sv_bao_luu' => 3,
                'ghi_chu' => 'Học kỳ hè. Cảnh báo khác (nếu có): 1. Tổng SV (theo nhóm năm) = 1380.',
            ]
        );

        // Dọn bản ghi học kỳ cũ không còn dùng (nếu có)
        FacultyReportStat::query()
            ->whereIn('hoc_ky', ['HK2-2026-2027', 'HK3-2026-2027'])
            ->delete();
    }
}
