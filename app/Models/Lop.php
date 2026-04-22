<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Lop extends Model
{
    use HasFactory;

    protected $table = 'lops';

    protected $fillable = [
        'ma_lop',
        'ten_lop',
    ];

    public static function generateNextMaLop(string $prefix = 'ML', int $minNumberWidth = 2): string
    {
        $prefixLen = mb_strlen($prefix);
        $latest = DB::table('lops')
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
}
