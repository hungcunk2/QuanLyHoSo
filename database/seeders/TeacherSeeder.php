<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;
use Carbon\Carbon;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chuyenMon = [
            'Toán học', 'Ngữ văn', 'Tiếng Anh', 'Vật lý', 'Hóa học', 
            'Sinh học', 'Lịch sử', 'Địa lý', 'Giáo dục công dân', 
            'Tin học', 'Thể dục', 'Mỹ thuật', 'Âm nhạc', 'Công nghệ'
        ];

        $ho = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Vũ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý', 'Võ', 'Đinh'];
        $tenDem = ['Văn', 'Thị', 'Minh', 'Thanh', 'Thành', 'Hữu', 'Đức', 'Quang', 'Thế', 'Xuân', 'Hồng', 'Thu', 'Lan', 'Hương', 'Anh'];
        $ten = ['An', 'Bình', 'Cường', 'Dũng', 'Em', 'Giang', 'Hoa', 'Hùng', 'Khang', 'Linh', 'Mai', 'Nam', 'Oanh', 'Phong', 'Quân', 'Sơn', 'Tâm', 'Uyên', 'Việt', 'Yến', 'Hạnh', 'Loan', 'Nga', 'Phương', 'Thảo'];

        $quan = ['Quận 1', 'Quận 2', 'Quận 3', 'Quận 4', 'Quận 5', 'Quận 6', 'Quận 7', 'Quận 8', 'Quận 9', 'Quận 10', 'Quận 11', 'Quận 12', 'Bình Thạnh', 'Tân Bình', 'Phú Nhuận', 'Gò Vấp', 'Tân Phú', 'Bình Tân'];

        for ($i = 1; $i <= 50; $i++) {
            $msgv = 'GV' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $hoRandom = $ho[array_rand($ho)];
            $tenDemRandom = $tenDem[array_rand($tenDem)];
            $tenRandom = $ten[array_rand($ten)];
            $hoTen = $hoRandom . ' ' . $tenDemRandom . ' ' . $tenRandom;
            $chuyenMonRandom = $chuyenMon[array_rand($chuyenMon)];
            $quanRandom = $quan[array_rand($quan)];
            
            // Tạo ngày sinh ngẫu nhiên từ 1975-1990
            $namSinh = rand(1975, 1990);
            $thangSinh = rand(1, 12);
            $ngaySinh = rand(1, 28);
            
            $email = strtolower(str_replace(' ', '', $hoTen)) . $i . '@example.com';
            
            Teacher::updateOrCreate(
                ['msgv' => $msgv],
                [
                    'msgv' => $msgv,
                    'ho_ten' => $hoTen,
                    'chuyen_mon' => $chuyenMonRandom,
                    'sdt' => '09' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'dia_chi' => rand(1, 999) . ' Đường ' . chr(65 + rand(0, 25)) . chr(65 + rand(0, 25)) . ', ' . $quanRandom . ', TP.HCM',
                    'email' => $email,
                    'ngay_sinh' => Carbon::parse("$namSinh-$thangSinh-$ngaySinh"),
                ]
            );
        }
    }
}
