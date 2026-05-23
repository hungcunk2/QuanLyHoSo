<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportEntry extends Model
{
    protected $fillable = [
        'section',
        'scope_khoa_hoc',
        'scope_lop',
        'metric_key',
        'label',
        'value_num',
        'value_text',
        'row_json',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'value_num' => 'decimal:2',
            'row_json' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function displayValue(): string
    {
        if ($this->value_text !== null && $this->value_text !== '') {
            return (string) $this->value_text;
        }

        if ($this->value_num !== null) {
            return rtrim(rtrim(number_format((float) $this->value_num, 2, '.', ''), '0'), '.');
        }

        return '0';
    }

    public function scopeForScope($query, string $khoaHoc, string $lop)
    {
        return $query
            ->where('scope_khoa_hoc', $khoaHoc)
            ->where('scope_lop', $lop);
    }

    public function numericValue(): float
    {
        if ($this->value_num !== null) {
            return (float) $this->value_num;
        }

        if (is_numeric($this->value_text)) {
            return (float) $this->value_text;
        }

        return 0.0;
    }
}
