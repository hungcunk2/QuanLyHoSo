<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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

    /**
     * @return list<string>
     */
    public static function coSoOptions(): array
    {
        return [
            'Cơ sở Hồ Chí Minh',
            'Phân hiệu Quảng Ngãi',
            'Phân hiệu Thanh Hóa',
        ];
    }

    public static function generateNextMssv(int $width = 8): string
    {
        $latest = DB::table('students')
            ->select('mssv')
            ->whereNotNull('mssv')
            ->whereRaw("mssv REGEXP '^[0-9]+$'")
            ->orderByRaw('CAST(mssv AS UNSIGNED) DESC')
            ->orderByDesc('mssv')
            ->value('mssv');

        $latestNumber = 0;
        if (is_string($latest) && $latest !== '' && preg_match('/^\d+$/', $latest)) {
            $latestNumber = (int) $latest;
        }

        $next = $latestNumber + 1;

        return str_pad((string) $next, $width, '0', STR_PAD_LEFT);
    }

    /**
     * Generate next numeric MSSV (width digits) that does not exist in students/users.
     * Intended to be called inside a transaction with a lock when concurrency matters.
     */
    public static function generateNextAvailableMssv(int $width = 8): string
    {
        $candidate = self::generateNextMssv($width);

        while (
            DB::table('students')->where('mssv', $candidate)->exists()
            || DB::table('users')->where('username', $candidate)->exists()
        ) {
            $num = (int) $candidate;
            $candidate = str_pad((string) ($num + 1), $width, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    public static function generateNextMaHoSo(string $prefix = 'HS', int $minNumberWidth = 2): string
    {
        $prefixLen = mb_strlen($prefix);
        $latest = DB::table('students')
            ->select('ma_ho_so')
            ->whereNotNull('ma_ho_so')
            ->where('ma_ho_so', 'like', $prefix.'%')
            ->orderByRaw('CAST(SUBSTRING(ma_ho_so, ?) AS UNSIGNED) DESC', [$prefixLen + 1])
            ->orderByDesc('ma_ho_so')
            ->value('ma_ho_so');

        $latestNumber = 0;
        $latestWidth = $minNumberWidth;

        if (is_string($latest) && $latest !== '') {
            $numeric = mb_substr($latest, $prefixLen);
            if (preg_match('/^\d+$/', $numeric)) {
                $latestNumber = (int) $numeric;
                $latestWidth = max($minNumberWidth, mb_strlen($numeric));
            }
        }

        $next = $latestNumber + 1;
        return $prefix.str_pad((string) $next, $latestWidth, '0', STR_PAD_LEFT);
    }

    public static function generateNextAvailableMaHoSo(string $prefix = 'HS', int $minNumberWidth = 2): string
    {
        $candidate = self::generateNextMaHoSo($prefix, $minNumberWidth);

        while (DB::table('students')->where('ma_ho_so', $candidate)->exists()) {
            $num = (int) mb_substr($candidate, mb_strlen($prefix));
            $width = max($minNumberWidth, mb_strlen((string) $num));
            $candidate = $prefix.str_pad((string) ($num + 1), $width, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    protected function casts(): array
    {
        return [
            'ngay_sinh' => 'date',
            'ngay_vao_truong' => 'date',
            'ngay_cap_cccd' => 'date',
        ];
    }
}
