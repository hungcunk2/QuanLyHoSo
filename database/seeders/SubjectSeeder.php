<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            ['ma_mon_hoc' => 'MH001', 'ten_mon_hoc' => 'Toán học'],
            ['ma_mon_hoc' => 'MH002', 'ten_mon_hoc' => 'Ngữ văn'],
            ['ma_mon_hoc' => 'MH003', 'ten_mon_hoc' => 'Tiếng Anh'],
            ['ma_mon_hoc' => 'MH004', 'ten_mon_hoc' => 'Vật lý'],
            ['ma_mon_hoc' => 'MH005', 'ten_mon_hoc' => 'Hóa học'],
            ['ma_mon_hoc' => 'MH006', 'ten_mon_hoc' => 'Sinh học'],
            ['ma_mon_hoc' => 'MH007', 'ten_mon_hoc' => 'Lịch sử'],
            ['ma_mon_hoc' => 'MH008', 'ten_mon_hoc' => 'Địa lý'],
            ['ma_mon_hoc' => 'MH009', 'ten_mon_hoc' => 'Giáo dục công dân'],
            ['ma_mon_hoc' => 'MH010', 'ten_mon_hoc' => 'Tin học'],
            ['ma_mon_hoc' => 'MH011', 'ten_mon_hoc' => 'Thể dục'],
            ['ma_mon_hoc' => 'MH012', 'ten_mon_hoc' => 'Mỹ thuật'],
            ['ma_mon_hoc' => 'MH013', 'ten_mon_hoc' => 'Âm nhạc'],
            ['ma_mon_hoc' => 'MH014', 'ten_mon_hoc' => 'Công nghệ'],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['ma_mon_hoc' => $subject['ma_mon_hoc']],
                $subject
            );
        }
    }
}
