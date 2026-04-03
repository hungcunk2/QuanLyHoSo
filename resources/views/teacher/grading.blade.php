@extends('layouts.teacher')

@section('title', 'Chấm điểm')
@section('page-title', 'Chấm điểm')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="fw-bold mb-0">Lớp đã bắt đầu học</h5>
                            <p class="text-muted small mb-0">Chỉ học phần đã đến ngày bắt đầu học. Bấm <strong>tên học phần</strong> hoặc <strong>Danh sách SV</strong> để xem sinh viên.</p>
                        </div>
                        <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary btn-sm text-nowrap">
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
                <p class="text-muted mb-0">Chưa có lớp nào đã bắt đầu học. Các học phần chưa tới <strong>ngày bắt đầu học</strong> sẽ không hiển thị ở đây — xem thêm tại <a href="{{ route('teacher.my-classes') }}">Lớp học của tôi</a>.</p>
            @else
                <div class="table-responsive">
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
                                    $rosterUrl = route('teacher.my-classes.roster', $o).'?from=grading';
                                @endphp
                                <tr class="align-middle">
                                    <td class="fw-semibold">
                                        <a href="{{ $rosterUrl }}" class="text-decoration-none text-primary">{{ $o->ten_hoc_phan }}</a>
                                    </td>
                                    <td>{{ $sub ? ($sub->ma_mon_hoc.' — '.$sub->ten_mon_hoc) : '—' }}</td>
                                    <td>{{ $room ? ($room->ma_lop.' — '.$room->ten_lop) : '—' }}</td>
                                    <td class="text-center">{{ $enrolled }} / {{ $siSo }}</td>
                                    <td class="small">
                                        {{ optional($o->ngay_bat_dau_hoc)->format('d/m/Y') ?? '—' }}
                                        →
                                        {{ optional($o->ngay_ket_thuc_hoc)->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="{{ $rosterUrl }}" class="btn btn-sm btn-primary d-inline-flex align-items-center text-nowrap">
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
