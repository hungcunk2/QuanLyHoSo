@extends('layouts.teacher')

@section('title', 'Lớp học của tôi')
@section('page-title', '')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="fw-bold mb-0">Lớp phân công giảng dạy</h5>
                        </div>
                        <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Bảng điều khiển
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($offerings->isEmpty())
                <p class="text-muted mb-0">Chưa có học phần nào được phân công. Liên hệ quản trị để thêm bạn làm giáo viên phụ trách trong <strong>Quản lý đăng ký học phần</strong>.</p>
            @else
                <div class="table-responsive admin-table-wrap">
                    <table class="table table-striped table-hover border align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tên học phần</th>
                                <th>Môn học</th>
                                <th>Phòng</th>
                                <th class="text-center">Sĩ số</th>
                                <th>Thời gian học</th>
                                <th class="text-nowrap" style="min-width: 12.5rem">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offerings as $o)
                                @php
                                    $sub = $o->subject;
                                    $room = $o->classRoom;
                                    $enrolled = (int) ($o->enrolled_count ?? 0);
                                    $siSo = (int) ($o->si_so_lop ?? 0);
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $o->ten_hoc_phan }}</td>
                                    <td>{{ $sub ? ($sub->ma_mon_hoc.' — '.$sub->ten_mon_hoc) : '—' }}</td>
                                    <td>{{ $room ? ($room->ma_lop.' — '.$room->ten_lop) : '—' }}</td>
                                    <td class="text-center">{{ $enrolled }} / {{ $siSo }}</td>
                                    <td class="small">
                                        {{ optional($o->ngay_bat_dau_hoc)->format('d/m/Y') ?? '—' }}
                                        →
                                        {{ optional($o->ngay_ket_thuc_hoc)->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('teacher.my-classes.roster', $o) }}" class="btn btn-sm btn-primary d-inline-flex align-items-center text-nowrap">
                                            <i class="fas fa-users me-1"></i> Danh sách SV
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
