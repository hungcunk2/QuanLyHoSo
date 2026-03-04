@extends('layouts.student')

@section('title', 'Đăng Ký Học Phần')
@section('page-title', 'Đăng Ký Học Phần')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Đăng Ký Học Phần</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">
            Đây là trang đăng ký học phần. Bạn có thể hiển thị danh sách các học phần mở đăng ký, kèm nút đăng ký / hủy.
        </p>
        <div class="alert alert-info mb-0">
            Logic đăng ký (kiểm tra trùng lịch, số tín chỉ, tiên quyết, ...) sẽ được triển khai sau.
        </div>
    </div>
</div>
@endsection

