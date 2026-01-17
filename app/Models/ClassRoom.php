<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
