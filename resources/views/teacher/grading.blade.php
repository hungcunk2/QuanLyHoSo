@extends('layouts.teacher')

@section('title', 'Chấm điểm')
@section('page-title', '')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="fw-bold mb-0">Chấm điểm</h5>
                            <p class="text-muted small mb-0">Gồm 2 danh sách: <strong>đang đăng ký</strong> và <strong>đang học</strong>.</p>
                        </div>
                        <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary btn-sm text-nowrap">
                            <i class="fas fa-arrow-left me-1"></i> Bảng điều khiển
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-semibold">Lớp đang trong thời gian đăng ký</span>
            <span class="small text-muted">{{ ($offeringsRegister ?? collect())->count() }} lớp</span>
        </div>
        <div class="card-body">
            @if(($offeringsRegister ?? collect())->isEmpty())
                <p class="text-muted mb-0">Không có lớp nào đang trong thời gian đăng ký.</p>
            @else
                <div class="table-responsive admin-table-wrap">
                    <table class="table table-striped table-hover border align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tên học phần</th>
                                <th>Môn học</th>
                                <th>Phòng</th>
                                <th class="text-center">Sĩ số</th>
                                <th>Thời gian đăng ký</th>
                                <th class="text-nowrap" style="min-width: 12.5rem">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offeringsRegister as $o)
                                @php
                                    $sub = $o->subject;
                                    $room = $o->classRoom;
                                    $enrolled = (int) ($o->enrolled_count ?? 0);
                                    $siSo = (int) ($o->si_so_lop ?? 0);
                                    $rosterUrl = route('teacher.grading.class', $o);
                                @endphp
                                <tr class="align-middle">
                                    <td class="fw-semibold">{{ $o->ten_hoc_phan }}</td>
                                    <td>{{ $sub ? ($sub->ma_mon_hoc.' — '.$sub->ten_mon_hoc) : '—' }}</td>
                                    <td>{{ $room ? ($room->ma_lop.' — '.$room->ten_lop) : '—' }}</td>
                                    <td class="text-center">{{ $enrolled }} / {{ $siSo }}</td>
                                    <td class="small">
                                        {{ optional($o->ngay_mo_dang_ky)->format('d/m/Y') ?? '—' }}
                                        →
                                        {{ optional($o->ngay_ket_thuc_dang_ky)->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="{{ $rosterUrl }}" class="btn btn-sm btn-primary d-inline-flex align-items-center text-nowrap">
                                            <i class="fas fa-pen me-1"></i> Nhập điểm
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

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-semibold">Lớp đang học</span>
            <span class="small text-muted">{{ ($offeringsStudy ?? collect())->count() }} lớp</span>
        </div>
        <div class="card-body">
            @if(($offeringsStudy ?? collect())->isEmpty())
                <p class="text-muted mb-0">Chưa có lớp nào đang học.</p>
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
                            @foreach($offeringsStudy as $o)
                                @php
                                    $sub = $o->subject;
                                    $room = $o->classRoom;
                                    $enrolled = (int) ($o->enrolled_count ?? 0);
                                    $siSo = (int) ($o->si_so_lop ?? 0);
                                    $rosterUrl = route('teacher.grading.class', $o);
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
                                            <i class="fas fa-pen me-1"></i> Nhập điểm
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
