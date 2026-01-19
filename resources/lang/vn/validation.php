<?php

return [
    'unique' => ':attribute đã tồn tại trong hệ thống.',
    'required' => 'Vui lòng nhập :attribute.',
    'email' => ':attribute phải là địa chỉ email hợp lệ.',
    'max' => [
        'string' => ':attribute không được vượt quá :max ký tự.',
    ],
    'custom' => [
        'mssv' => [
            'unique' => 'Mã số học sinh đã tồn tại trong hệ thống.',
            'required' => 'Vui lòng nhập mã số học sinh.',
        ],
        'email' => [
            'unique' => 'Email đã được sử dụng bởi học sinh khác.',
            'required' => 'Vui lòng nhập email.',
            'email' => 'Email không hợp lệ.',
        ],
        'ho_ten' => [
            'required' => 'Vui lòng nhập họ và tên.',
        ],
        'lop' => [
            'required' => 'Vui lòng nhập lớp.',
        ],
    ],
    'attributes' => [
        'mssv' => 'mã số học sinh',
        'email' => 'email',
        'ho_ten' => 'họ và tên',
        'lop' => 'lớp',
    ],
];
