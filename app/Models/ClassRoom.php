<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ClassRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'classes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ma_lop',
        'ten_lop',
        'giao_vien_chu_nhiem_id',
        'subject_id',
    ];

    public static function generateNextMaLop(string $prefix = 'PH', int $minNumberWidth = 2): string
    {
        $prefixLen = mb_strlen($prefix);
        $latest = DB::table('classes')
            ->select('ma_lop')
            ->whereNotNull('ma_lop')
            ->where('ma_lop', 'like', $prefix.'%')
            ->orderByRaw('CAST(SUBSTRING(ma_lop, ?) AS UNSIGNED) DESC', [$prefixLen + 1])
            ->orderByDesc('ma_lop')
            ->value('ma_lop');

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

    /**
     * Get the teacher that is the homeroom teacher for this class.
     */
    public function giaoVienChuNhiem(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'giao_vien_chu_nhiem_id');
    }

    /**
     * Get the subject for this class.
     */
    public function monHoc(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
