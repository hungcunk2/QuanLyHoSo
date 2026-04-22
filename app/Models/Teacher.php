<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'teachers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'msgv',
        'ho_ten',
        'chuyen_mon',
        'sdt',
        'dia_chi',
        'email',
        'ngay_sinh',
    ];

    /**
     * @return list<string>
     */
    public static function chuyenMonOptions(): array
    {
        return [
            'Kỹ thuật phần mềm',
            'Khoa học máy tính',
            'Hệ thống thông tin',
            'Công nghệ thông tin',
            'Khoa học dữ liệu',
        ];
    }

    public static function generateNextMsgv(string $prefix = 'GV', int $minNumberWidth = 2): string
    {
        $latest = DB::table('teachers')
            ->select('msgv')
            ->whereNotNull('msgv')
            ->where('msgv', 'like', $prefix . '%')
            ->orderByRaw("CAST(SUBSTRING(msgv, ?) AS UNSIGNED) DESC", [mb_strlen($prefix) + 1])
            ->orderByDesc('msgv')
            ->value('msgv');

        $latestNumber = 0;
        $latestWidth = $minNumberWidth;

        if (is_string($latest) && $latest !== '') {
            $numeric = mb_substr($latest, mb_strlen($prefix));
            if (preg_match('/^\d+$/', $numeric)) {
                $latestNumber = (int) $numeric;
                $latestWidth = max($minNumberWidth, mb_strlen($numeric));
            }
        }

        $next = $latestNumber + 1;
        return $prefix . str_pad((string) $next, $latestWidth, '0', STR_PAD_LEFT);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ngay_sinh' => 'date',
        ];
    }

    /**
     * Get the classes where this teacher is the homeroom teacher.
     */
    public function classes(): HasMany
    {
        return $this->hasMany(ClassRoom::class, 'giao_vien_chu_nhiem_id');
    }

    /**
     * Các học phần (lớp học phần) giáo viên được phân công dạy.
     */
    public function courseOfferings(): HasMany
    {
        return $this->hasMany(CourseOffering::class, 'teacher_id');
    }
}
