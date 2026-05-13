@extends('layouts.teacher')

@section('title', 'Thông tin cá nhân')
@section('page-title', 'Thông tin cá nhân')

@section('content')
@php
    $t = $teacher ?? null;
    $emptyLabel = 'Chưa cập nhật';
    $v = function($key, $default = null) use ($t, $user, $emptyLabel) {
        $default = $default ?? $emptyLabel;
        if (!$t) {
            return $key === 'email' ? ($user->email ?? $emptyLabel) : $default;
        }
        $map = [
            'msgv' => $t->msgv ?? ($user->username ?? ''),
            'ho_ten' => $t->ho_ten ?? '',
            'chuyen_mon' => $t->chuyen_mon ?? '',
            'ngay_sinh' => $t->ngay_sinh ? $t->ngay_sinh->format('d/m/Y') : '',
            'sdt' => $t->sdt ?? '',
            'email' => $t->email ?? ($user->email ?? ''),
            'dia_chi' => $t->dia_chi ?? '',
        ];
        $raw = $map[$key] ?? '';
        return (is_string($raw) && trim((string)$raw) !== '') ? $raw : $default;
    };
@endphp

<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('message'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="mb-3 text-end">
        <a href="{{ route('teacher.profile.edit') }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i>Chỉnh sửa
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Thông tin giảng viên</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-auto mb-3 mb-md-0">
                    <div class="rounded-circle overflow-hidden bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 120px; height: 120px;">
                        @if($teacher && $teacher->avatar)
                            <img src="{{ asset('storage/' . $teacher->avatar) }}" alt="Ảnh đại diện" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <i class="fas fa-chalkboard-teacher fa-3x"></i>
                        @endif
                    </div>
                </div>
                <div class="col">
                    <div class="row g-2">
                        <div class="col-md-6 col-lg-4"><strong>MSGV:</strong> {{ $v('msgv') }}</div>
                        <div class="col-md-6 col-lg-4"><strong>Họ tên:</strong> {{ $v('ho_ten') }}</div>
                        <div class="col-md-6 col-lg-4"><strong>Chuyên môn:</strong> {{ $v('chuyen_mon') }}</div>
                        <div class="col-md-6 col-lg-4"><strong>Ngày sinh:</strong> {{ $v('ngay_sinh') }}</div>
                        <div class="col-md-6 col-lg-4"><strong>Điện thoại:</strong> {{ $v('sdt') }}</div>
                        <div class="col-md-6 col-lg-4"><strong>Email:</strong> {{ $v('email') }}</div>
                        <div class="col-12"><strong>Địa chỉ:</strong> {{ $v('dia_chi') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

