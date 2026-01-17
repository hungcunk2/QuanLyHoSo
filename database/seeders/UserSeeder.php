<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = [
            [
                'mssv' => 'HS001',
                'name' => 'Nguyễn Văn A',
                'ho_ten' => 'Nguyễn Văn A',
                'email' => 'nguyenvana@example.com',
                'password' => Hash::make('password'),
                'lop' => '10A1',
                'so_dien_thoai' => '0901234567',
                'ngay_sinh' => Carbon::parse('2008-05-15'),
                'dia_chi' => '123 Đường ABC, Quận 1, TP.HCM',
                'ho_ten_cha' => 'Nguyễn Văn Bố',
                'sdt_cha' => '0901234568',
                'ho_ten_me' => 'Nguyễn Thị Mẹ',
                'sdt_me' => '0901234569',
            ],
            [
                'mssv' => 'HS002',
                'name' => 'Trần Thị B',
                'ho_ten' => 'Trần Thị B',
                'email' => 'tranthib@example.com',
                'password' => Hash::make('password'),
                'lop' => '10A1',
                'so_dien_thoai' => '0901234570',
                'ngay_sinh' => Carbon::parse('2008-07-20'),
                'dia_chi' => '456 Đường XYZ, Quận 2, TP.HCM',
                'ho_ten_cha' => 'Trần Văn Bố',
                'sdt_cha' => '0901234571',
                'ho_ten_me' => 'Trần Thị Mẹ',
                'sdt_me' => '0901234572',
            ],
            [
                'mssv' => 'HS003',
                'name' => 'Lê Văn C',
                'ho_ten' => 'Lê Văn C',
                'email' => 'levanc@example.com',
                'password' => Hash::make('password'),
                'lop' => '10A2',
                'so_dien_thoai' => '0901234573',
                'ngay_sinh' => Carbon::parse('2008-03-10'),
                'dia_chi' => '789 Đường DEF, Quận 3, TP.HCM',
                'ho_ten_cha' => 'Lê Văn Bố',
                'sdt_cha' => '0901234574',
                'ho_ten_me' => 'Lê Thị Mẹ',
                'sdt_me' => '0901234575',
            ],
            [
                'mssv' => 'HS004',
                'name' => 'Phạm Thị D',
                'ho_ten' => 'Phạm Thị D',
                'email' => 'phamthid@example.com',
                'password' => Hash::make('password'),
                'lop' => '10A2',
                'so_dien_thoai' => '0901234576',
                'ngay_sinh' => Carbon::parse('2008-09-25'),
                'dia_chi' => '321 Đường GHI, Quận 4, TP.HCM',
                'ho_ten_cha' => 'Phạm Văn Bố',
                'sdt_cha' => '0901234577',
                'ho_ten_me' => 'Phạm Thị Mẹ',
                'sdt_me' => '0901234578',
            ],
            [
                'mssv' => 'HS005',
                'name' => 'Hoàng Văn E',
                'ho_ten' => 'Hoàng Văn E',
                'email' => 'hoangvane@example.com',
                'password' => Hash::make('password'),
                'lop' => '11A1',
                'so_dien_thoai' => '0901234579',
                'ngay_sinh' => Carbon::parse('2007-11-30'),
                'dia_chi' => '654 Đường JKL, Quận 5, TP.HCM',
                'ho_ten_cha' => 'Hoàng Văn Bố',
                'sdt_cha' => '0901234580',
                'ho_ten_me' => 'Hoàng Thị Mẹ',
                'sdt_me' => '0901234581',
            ],
        ];

        // Thêm 40 học sinh nữa
        $ho = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Vũ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý', 'Võ', 'Đinh'];
        $tenDem = ['Văn', 'Thị', 'Minh', 'Thanh', 'Thành', 'Hữu', 'Đức', 'Quang', 'Thế', 'Xuân', 'Hồng', 'Thu', 'Lan', 'Hương', 'Anh'];
        $ten = ['An', 'Bình', 'Cường', 'Dũng', 'Em', 'Giang', 'Hoa', 'Hùng', 'Khang', 'Linh', 'Mai', 'Nam', 'Oanh', 'Phong', 'Quân', 'Sơn', 'Tâm', 'Uyên', 'Việt', 'Yến'];
        $lop = ['10A1', '10A2', '10A3', '11A1', '11A2', '11A3', '12A1', '12A2', '12A3'];
        $quan = ['Quận 1', 'Quận 2', 'Quận 3', 'Quận 4', 'Quận 5', 'Quận 6', 'Quận 7', 'Quận 8', 'Quận 9', 'Quận 10', 'Quận 11', 'Quận 12', 'Bình Thạnh', 'Tân Bình', 'Phú Nhuận'];

        for ($i = 6; $i <= 45; $i++) {
            $mssv = 'HS' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $hoRandom = $ho[array_rand($ho)];
            $tenDemRandom = $tenDem[array_rand($tenDem)];
            $tenRandom = $ten[array_rand($ten)];
            $hoTen = $hoRandom . ' ' . $tenDemRandom . ' ' . $tenRandom;
            $lopRandom = $lop[array_rand($lop)];
            $quanRandom = $quan[array_rand($quan)];
            
            // Tạo ngày sinh ngẫu nhiên từ 2007-2009
            $namSinh = rand(2007, 2009);
            $thangSinh = rand(1, 12);
            $ngaySinh = rand(1, 28);
            
            $students[] = [
                'mssv' => $mssv,
                'name' => $hoTen,
                'ho_ten' => $hoTen,
                'email' => strtolower(str_replace(' ', '', $hoTen)) . $i . '@example.com',
                'password' => Hash::make('password'),
                'lop' => $lopRandom,
                'so_dien_thoai' => '09' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'ngay_sinh' => Carbon::parse("$namSinh-$thangSinh-$ngaySinh"),
                'dia_chi' => rand(1, 999) . ' Đường ' . chr(65 + rand(0, 25)) . chr(65 + rand(0, 25)) . ', ' . $quanRandom . ', TP.HCM',
                'ho_ten_cha' => $hoRandom . ' Văn Bố ' . $i,
                'sdt_cha' => '09' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'ho_ten_me' => $hoRandom . ' Thị Mẹ ' . $i,
                'sdt_me' => '09' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
            ];
        }

        foreach ($students as $student) {
            User::updateOrCreate(
                ['mssv' => $student['mssv']],
                $student
            );
        }
    }
}
