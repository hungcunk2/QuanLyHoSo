<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
