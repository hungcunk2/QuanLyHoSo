<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'students';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mssv', 'ho_ten', 'email', 'avatar', 'gioi_tinh', 'trang_thai', 'ma_ho_so', 'ngay_vao_truong',
        'lop', 'co_so', 'bac_dao_tao', 'loai_hinh_dao_tao', 'khoa', 'nganh', 'chuyen_nganh', 'khoa_hoc',
        'so_dien_thoai', 'ngay_sinh', 'dia_chi', 'dan_toc', 'ton_giao', 'quoc_tich', 'khu_vuc',
        'so_cccd', 'ngay_cap_cccd', 'noi_cap_cccd', 'doi_tuong', 'dien_chinh_sach', 'ngay_vao_doan', 'ngay_vao_dang',
        'dia_chi_lien_he', 'noi_sinh', 'ho_khau_thuong_tru',
        'ho_ten_cha', 'nam_sinh_cha', 'nghe_nghiep_cha', 'quoc_tich_cha', 'dan_toc_cha', 'ton_giao_cha', 'co_quan_cha', 'chuc_vu_cha', 'sdt_cha',
        'ho_ten_me', 'sdt_me',
    ];

    protected function casts(): array
    {
        return [
            'ngay_sinh' => 'date',
            'ngay_vao_truong' => 'date',
            'ngay_cap_cccd' => 'date',
        ];
    }
}
