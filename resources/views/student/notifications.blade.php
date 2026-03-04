@extends('layouts.student')

@section('title', 'Thông Báo')
@section('page-title', 'Thông Báo')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Thông Báo từ Khoa và Giảng Viên</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">
            Đây là trang xem các thông báo gửi đến sinh viên từ khoa và giảng viên.
        </p>
        <div class="alert alert-info mb-0">
            Sau này có thể thêm danh sách thông báo, bộ lọc theo loại thông báo, và trạng thái đã đọc / chưa đọc tại đây.
        </div>
    </div>
</div>
@endsection

