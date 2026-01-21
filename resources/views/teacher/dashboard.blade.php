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
                        Chào mừng, {{ $user->email ?? 'Giáo Viên' }}!
                    </h5>
                    <p class="card-text">Đây là trang quản lý dành cho giáo viên.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-school fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Lớp Học</h6>
                            <p class="text-muted mb-0">Quản lý lớp học</p>
                        </div>
                    </div>
                </div>
            </div>
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
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clipboard-list fa-2x text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Chấm Điểm</h6>
                            <p class="text-muted mb-0">Chấm điểm học sinh</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-alt fa-2x text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Lịch Dạy</h6>
                            <p class="text-muted mb-0">Xem lịch dạy</p>
                        </div>
                    </div>
                </div>
            </div>
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
