@extends('layouts.admin')

@section('title', 'Báo cáo / Thống kê')
@section('page-title', 'Báo cáo / Thống kê')

@push('styles')
<style>
    .reports-term-picker__row {
        display: flex;
        flex-wrap: nowrap;
        align-items: stretch;
        gap: 0.5rem;
    }
    .reports-term-picker__row .form-select {
        min-width: 240px;
        flex: 1 1 auto;
    }
    .reports-term-picker__btn {
        flex: 0 0 auto;
        align-self: stretch;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding-left: 1.25rem;
        padding-right: 1.25rem;
        white-space: nowrap;
    }
</style>
@endpush

@section('content')
@php
    $s = $stat;
    $fmt = fn ($n) => number_format((int) $n);
@endphp

<div class="container-fluid">
    <div class="d-flex flex-wrap gap-3 align-items-end justify-content-between mb-3 admin-toolbar">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="reports-term-picker">
            <label for="hoc_ky" class="form-label mb-1">Chọn học kỳ</label>
            <div class="reports-term-picker__row">
                <select name="hoc_ky" id="hoc_ky" class="form-select">
                    @foreach($hocKyOptions as $value => $label)
                        <option value="{{ $value }}" @selected($selectedHocKy === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary reports-term-picker__btn">Xem</button>
            </div>
        </form>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100 border-primary border-opacity-25">
                <div class="card-body">
                    <div class="text-muted small">Tổng sinh viên khoa</div>
                    <div class="fs-2 fw-semibold text-primary">{{ $fmt($s->totalStudentsByYear()) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100 border-warning border-opacity-25">
                <div class="card-body">
                    <div class="text-muted small">Cảnh báo học vụ (kỳ này)</div>
                    <div class="fs-2 fw-semibold text-warning">{{ $fmt($s->totalAcademicWarnings()) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100 border-info border-opacity-25">
                <div class="card-body">
                    <div class="text-muted small">Lớp học phần mở (kỳ này)</div>
                    <div class="fs-2 fw-semibold text-info">{{ $fmt($s->lop_hp_mo_ky_nay) }}</div>
                    <div class="text-muted small mt-2">Đã chốt điểm: <strong>{{ $fmt($s->lop_hp_da_chot_diem) }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-semibold bg-white">Sinh viên khoa theo năm học</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 align-middle">
                            <thead>
                            <tr>
                                <th>Nhóm</th>
                                <th class="text-end">Số lượng</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Năm 1</td>
                                <td class="text-end fw-semibold">{{ $fmt($s->sv_nam_1) }}</td>
                            </tr>
                            <tr>
                                <td>Năm 2</td>
                                <td class="text-end fw-semibold">{{ $fmt($s->sv_nam_2) }}</td>
                            </tr>
                            <tr>
                                <td>Năm 3</td>
                                <td class="text-end fw-semibold">{{ $fmt($s->sv_nam_3) }}</td>
                            </tr>
                            <tr>
                                <td>Năm 4</td>
                                <td class="text-end fw-semibold">{{ $fmt($s->sv_nam_4) }}</td>
                            </tr>
                            <tr>
                                <td>Sau năm 4</td>
                                <td class="text-end fw-semibold">{{ $fmt($s->sv_sau_nam_4) }}</td>
                            </tr>
                            <tr class="table-light">
                                <td class="fw-semibold">Tổng</td>
                                <td class="text-end fw-bold">{{ $fmt($s->totalStudentsByYear()) }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-semibold bg-white">Cảnh báo học vụ kỳ này</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 align-middle">
                            <thead>
                            <tr>
                                <th>Loại</th>
                                <th class="text-end">Số lượng</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Cảnh báo lần 1</td>
                                <td class="text-end fw-semibold">{{ $fmt($s->canh_bao_lan_1) }}</td>
                            </tr>
                            <tr>
                                <td>Cảnh báo lần 2</td>
                                <td class="text-end fw-semibold">{{ $fmt($s->canh_bao_lan_2) }}</td>
                            </tr>
                            <tr>
                                <td>Nghỉ học</td>
                                <td class="text-end fw-semibold">{{ $fmt($s->nghi_hoc) }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-semibold bg-white">Lớp học phần &amp; chốt điểm</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 align-middle">
                            <thead>
                            <tr>
                                <th scope="col"></th>
                                <th scope="col" class="text-end">Số lượng</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Lớp học phần mở kỳ này</td>
                                <td class="text-end fw-semibold">{{ $fmt($s->lop_hp_mo_ky_nay) }}</td>
                            </tr>
                            <tr>
                                <td>Lớp học phần đã chốt điểm</td>
                                <td class="text-end fw-semibold">{{ $fmt($s->lop_hp_da_chot_diem) }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-semibold bg-white">Lớp đã chốt điểm — xếp loại điểm trung bình của lớp</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 align-middle">
                            <thead>
                            <tr>
                                <th>Xếp loại ĐTB lớp</th>
                                <th class="text-end">Số lớp</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Giỏi</td>
                                <td class="text-end fw-semibold">{{ $fmt($s->lop_ca_lop_gioi) }}</td>
                            </tr>
                            <tr>
                                <td>Khá</td>
                                <td class="text-end fw-semibold">{{ $fmt($s->lop_ca_lop_kha) }}</td>
                            </tr>
                            <tr>
                                <td>Trung bình</td>
                                <td class="text-end fw-semibold">{{ $fmt($s->lop_ca_lop_trung_binh) }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-semibold bg-white">Kết quả học tập khác</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 align-middle">
                            <thead>
                            <tr>
                                <th scope="col"></th>
                                <th scope="col" class="text-end">Số lượng</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Sinh viên rớt môn</td>
                                <td class="text-end fw-semibold">{{ $fmt($s->sv_rot_mon) }}</td>
                            </tr>
                            <tr>
                                <td>Sinh viên bảo lưu</td>
                                <td class="text-end fw-semibold">{{ $fmt($s->sv_bao_luu) }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
