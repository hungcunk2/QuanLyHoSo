<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassRoom;
use App\Models\Teacher;

class ClassRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $khoi = [10, 11, 12];
        $lop = ['A', 'B', 'C', 'D'];
        $so = [1, 2, 3];
        
        $teachers = Teacher::all();
        $subjects = \App\Models\Subject::all();
        
        $classIndex = 1;
        foreach ($khoi as $k) {
            foreach ($lop as $l) {
                foreach ($so as $s) {
                    $maLop = $k . $l . $s;
                    $tenLop = $k . $l . $s;
                    
                    // Gán giáo viên chủ nhiệm ngẫu nhiên
                    $teacher = $teachers->random();
                    
                    // Gán môn học ngẫu nhiên
                    $subject = $subjects->random();
                    
                    ClassRoom::updateOrCreate(
                        ['ma_lop' => $maLop],
                        [
                            'ma_lop' => $maLop,
                            'ten_lop' => $tenLop,
                            'giao_vien_chu_nhiem_id' => $teacher->id,
                            'subject_id' => $subject->id,
                        ]
                    );
                    
                    $classIndex++;
                }
            }
        }
    }
}
