@extends('layouts.admin')

@section('title', 'Quản lý chương trình khung')
@section('page-title', '')

@section('content')
@php
    $rowNumber = 1;
    $totalCreditsAll = (int) $items->sum(fn ($term) => $term->sumCreditsForCurriculumTotal());
    $totalRequiredCreditsAll = (int) $items->sum(fn ($term) => $term->sumRequiredCredits());
    $totalElectiveCreditsAll = (int) $items->sum(fn ($term) => $term->sumElectiveCreditsCountOncePerGroup());
@endphp
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-0">Quản lý chương trình khung</h5>
            <div class="text-muted mt-1" style="font-size: 13px;">Mặc định chỉ hiện tổng từng học kỳ, bấm vào học kỳ để xem chi tiết môn học.</div>
        </div>
        <a href="{{ route('admin.curriculum-terms.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Tạo kỳ mới
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0 curriculum-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">STT</th>
                        <th>Tên môn học</th>
                        <th style="width: 150px;">Mã học phần</th>
                        <th style="width: 90px;">Số TC</th>
                        <th style="width: 110px;">Số tiết LT</th>
                        <th style="width: 110px;">Số tiết TH</th>
                        <th style="width: 110px;">Nhóm tự chọn</th>
                        <th style="width: 160px;">Số TC bắt buộc của nhóm</th>
                        <th style="width: 180px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $it)
                        @php
                            $termRowKey = 'admin-term-' . $it->id;
                            $totalCredits = (int) $it->sumCreditsForCurriculumTotal();
                            $requiredSubjects = $it->subjects
                                ->filter(fn ($subject) => ($subject->pivot->loai_hoc_phan ?? 'bat_buoc') === 'bat_buoc')
                                ->values();
                            $electiveSubjects = $it->subjects
                                ->filter(fn ($subject) => ($subject->pivot->loai_hoc_phan ?? 'bat_buoc') === 'tu_chon')
                                ->values();
                            $requiredCredits = (int) $it->sumRequiredCredits();
                            $electiveCredits = (int) $it->sumElectiveCreditsCountOncePerGroup();
                        @endphp
                        <tr class="curriculum-table__term-row curriculum-term-toggle" data-term-target="{{ $termRowKey }}" aria-expanded="false">
                            <td colspan="3" class="fw-bold text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span>{{ $it->ten_ky }}</span>
                                </div>
                                @if($it->ghi_chu)
                                    <div class="text-muted fw-normal mt-1" style="font-size: 12px;">{{ $it->ghi_chu }}</div>
                                @endif
                            </td>
                            <td class="fw-bold text-center">{{ $totalCredits }}</td>
                            <td colspan="4"></td>
                            <td class="text-end curriculum-table__actions">
                                <a href="{{ route('admin.curriculum-terms.edit', $it) }}" class="btn btn-outline-primary btn-sm">Sửa</a>
                                <form method="POST" action="{{ route('admin.curriculum-terms.destroy', $it) }}" class="d-inline" onsubmit="return confirm('Xóa kỳ chương trình khung này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Xóa</button>
                                </form>
                            </td>
                        </tr>

                        @if($requiredSubjects->isNotEmpty())
                            <tr class="curriculum-table__section-row curriculum-detail-row" data-term-row="{{ $termRowKey }}" style="display: none;">
                                <td colspan="3" class="fw-bold">Học phần bắt buộc</td>
                                <td class="fw-bold text-center">{{ $requiredCredits }}</td>
                                <td colspan="5"></td>
                            </tr>

                            @foreach($requiredSubjects as $subject)
                                <tr class="curriculum-detail-row" data-term-row="{{ $termRowKey }}" style="display: none;">
                                    <td class="text-center">{{ $rowNumber++ }}</td>
                                    <td>{{ $subject->ten_mon_hoc }}</td>
                                    <td class="text-center">{{ $subject->ma_mon_hoc }}</td>
                                    <td class="text-center">{{ $subject->so_tin_chi }}</td>
                                    <td class="text-center">{{ $subject->so_tiet_ly_thuyet ?? 0 }}</td>
                                    <td class="text-center">{{ $subject->so_tiet_thuc_hanh ?? 0 }}</td>
                                    <td class="text-center">@if((int) ($subject->nhom_thuc_hanh ?? 0) > 0){{ (int) $subject->nhom_thuc_hanh }}@endif</td>
                                    <td class="text-center">@if((int) ($subject->so_tc_bat_buoc_cua_nhom ?? 0) > 0){{ (int) $subject->so_tc_bat_buoc_cua_nhom }}@endif</td>
                                    <td></td>
                                </tr>
                            @endforeach
                        @endif

                        @if($electiveSubjects->isNotEmpty())
                            <tr class="curriculum-table__section-row curriculum-detail-row" data-term-row="{{ $termRowKey }}" style="display: none;">
                                <td colspan="3" class="fw-bold">Học phần tự chọn</td>
                                <td class="fw-bold text-center">{{ $electiveCredits }}</td>
                                <td colspan="5"></td>
                            </tr>

                            @foreach($electiveSubjects as $subject)
                                <tr class="curriculum-detail-row" data-term-row="{{ $termRowKey }}" style="display: none;">
                                    <td class="text-center">{{ $rowNumber++ }}</td>
                                    <td>{{ $subject->ten_mon_hoc }}</td>
                                    <td class="text-center">{{ $subject->ma_mon_hoc }}</td>
                                    <td class="text-center">{{ $subject->so_tin_chi }}</td>
                                    <td class="text-center">{{ $subject->so_tiet_ly_thuyet ?? 0 }}</td>
                                    <td class="text-center">{{ $subject->so_tiet_thuc_hanh ?? 0 }}</td>
                                    <td class="text-center">@if((int) ($subject->nhom_thuc_hanh ?? 0) > 0){{ (int) $subject->nhom_thuc_hanh }}@endif</td>
                                    <td class="text-center">@if((int) ($subject->so_tc_bat_buoc_cua_nhom ?? 0) > 0){{ (int) $subject->so_tc_bat_buoc_cua_nhom }}@endif</td>
                                    <td></td>
                                </tr>
                            @endforeach
                        @endif
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Chưa có kỳ chương trình khung nào.</td>
                        </tr>
                    @endforelse
                    @if($items->isNotEmpty())
                        <tr class="curriculum-table__summary-row">
                            <td colspan="3" class="fw-bold">Tổng TC yêu cầu</td>
                            <td class="fw-bold text-center text-danger">{{ $totalCreditsAll }}</td>
                            <td colspan="5"></td>
                        </tr>
                        <tr class="curriculum-table__summary-row">
                            <td colspan="3" class="fw-bold">Tổng TC bắt buộc</td>
                            <td class="fw-bold text-center text-danger">{{ $totalRequiredCreditsAll }}</td>
                            <td colspan="5"></td>
                        </tr>
                        <tr class="curriculum-table__summary-row">
                            <td colspan="3" class="fw-bold">Tổng TC tự chọn</td>
                            <td class="fw-bold text-center text-danger">{{ $totalElectiveCreditsAll }}</td>
                            <td colspan="5"></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .curriculum-table thead th {
        background: #f7fbff;
        color: #4f9aca;
        font-weight: 700;
        text-align: center;
        vertical-align: middle;
    }

    .curriculum-table__term-row td,
    .curriculum-table__section-row td {
        background: #f9fbfd;
        color: #4f78a8;
    }

    .curriculum-table__summary-row td {
        background: #fcfcfc;
        color: #4f78a8;
    }

    .curriculum-table__section-row td:first-child,
    .curriculum-table__term-row td:first-child {
        border-left-color: #dbe8f1;
    }

    .curriculum-table__term-row {
        cursor: pointer;
    }

    .curriculum-table__actions {
        cursor: default;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.curriculum-term-toggle').forEach(function (row) {
            row.addEventListener('click', function (event) {
                if (event.target.closest('.curriculum-table__actions')) {
                    return;
                }

                var key = this.getAttribute('data-term-target');
                var expanded = this.getAttribute('aria-expanded') === 'true';

                document.querySelectorAll('[data-term-row="' + key + '"]').forEach(function (detailRow) {
                    detailRow.style.display = expanded ? 'none' : 'table-row';
                });

                this.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            });
        });
    });
</script>
@endpush
