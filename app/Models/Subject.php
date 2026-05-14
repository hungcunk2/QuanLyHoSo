<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'subjects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ma_mon_hoc',
        'ten_mon_hoc',
        'so_tin_chi',
        'so_tiet_ly_thuyet',
        'so_tiet_thuc_hanh',
        'nhom_thuc_hanh',
        'so_tc_bat_buoc_cua_nhom',
    ];

    public function curriculumTerms()
    {
        return $this->belongsToMany(CurriculumTerm::class, 'curriculum_term_subject')
            ->withPivot('sort_order', 'loai_hoc_phan', 'nhom_tu_chon', 'so_tc_bat_buoc_cua_nhom')
            ->withTimestamps();
    }
}
