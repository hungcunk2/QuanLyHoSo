@extends('layouts.teacher')

@section('title', 'Bảng Điều Khiển')
@section('page-title', '')

@section('content')
<div class="dashboard-container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-chalkboard-teacher me-2"></i>
                        Chào mừng,
                        @if(isset($teacher) && $teacher)
                            <strong>{{ $teacher->ho_ten }}</strong>
                        @else
                            {{ $user->email ?? 'Giáo Viên' }}
                        @endif
                        !
                    </h5>
                    <p class="card-text mb-0">Đây là trang quản lý dành cho giáo viên. Vào <a href="{{ route('teacher.my-classes') }}">Lớp học của tôi</a> để xem các học phần được phân công.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-3">
            <a href="{{ route('teacher.my-classes') }}" class="text-decoration-none text-reset">
                <div class="card h-100 shadow-sm border-primary border-opacity-25">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-school fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Lớp học của tôi</h6>
                                <p class="text-muted mb-0 small">Học phần được phân công</p>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-graduate fa-2x text-info"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Học Sinh</h6>
                            <p class="text-muted mb-0">Quản lý học sinh</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <a href="{{ route('teacher.grading') }}" class="text-decoration-none text-reset">
                <div class="card h-100 shadow-sm border-success border-opacity-25">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-clipboard-list fa-2x text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Chấm điểm</h6>
                                <p class="text-muted mb-0 small">Lớp đã bắt đầu · danh sách SV</p>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('teacher.schedule') }}" class="text-decoration-none text-reset">
                <div class="card h-100 shadow-sm border-warning border-opacity-25">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-calendar-alt fa-2x text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Lịch dạy</h6>
                                <p class="text-muted mb-0 small">Theo học phần được phân công</p>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thông Tin Cá Nhân</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Email:</strong> {{ $user->email ?? 'N/A' }}</p>
                            <p><strong>Username:</strong> {{ $user->username ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Vai trò:</strong> Giáo Viên</p>
                            <p><strong>Trạng thái:</strong> 
                                <span class="badge bg-{{ $user->status ? 'success' : 'danger' }}">
                                    {{ $user->status ? 'Hoạt động' : 'Vô hiệu hóa' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
