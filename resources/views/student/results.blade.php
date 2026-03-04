@extends('layouts.student')

@section('title', 'Kết Quả Học Tập')
@section('page-title', 'Kết Quả Học Tập')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Kết Quả Học Tập</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">
            Đây là trang xem kết quả học tập của bản thân. Bạn có thể hiển thị bảng điểm theo năm học / học kỳ tại đây.
        </p>
        <div class="alert alert-info mb-0">
            Chức năng chi tiết (lọc học kỳ, bảng điểm, GPA, ...) sẽ được bổ sung sau.
        </div>
    </div>
</div>
@endsection

