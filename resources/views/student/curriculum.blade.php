@extends('layouts.student')

@section('title', 'Chương trình khung')
@section('page-title', '')

@section('content')
@php
    $subjectStatuses = collect($subjectStatuses ?? [])->mapWithKeys(fn ($status, $id) => [(int) $id => $status])->all();
    $rowNumber = 1;
    $totalCreditsAll = (int) $items->sum(fn ($term) => $term->sumCreditsForCurriculumTotal());
    $totalRequiredCreditsAll = (int) $items->sum(fn ($term) => $term->sumRequiredCredits());
    $totalElectiveCreditsAll = (int) $items->sum(fn ($term) => $term->sumElectiveCreditsCountOncePerGroup());
@endphp
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-0">Chương trình khung</h5>
            <div class="text-muted mt-1" style="font-size: 13px;">Mặc định chỉ hiện tổng từng học kỳ, bấm vào học kỳ để xem chi tiết môn học.</div>
        </div>
    </div>
    <div class="card-body p-0">
        @if($items->isEmpty())
            <div class="p-3 text-muted">Chưa có dữ liệu chương trình khung.</div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 curriculum-table">
                    <thead>
                        <tr>
                            <th style="width: 70px;">STT</th>
                            <th>Tên môn học/Học phần</th>
                            <th style="width: 150px;">Mã học phần</th>
                            <th style="width: 140px;">Học phần</th>
                            <th style="width: 90px;">Số TC</th>
                            <th style="width: 110px;">Số tiết LT</th>
                            <th style="width: 110px;">Số tiết TH</th>
                            <th style="width: 110px;">Nhóm tự chọn</th>
                            <th style="width: 160px;">Số TC bắt buộc của nhóm</th>
                            <th style="width: 90px;">Đạt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            @php
                                $termRowKey = 'student-term-' . $item->id;
                                $requiredSubjects = $item->subjects->filter(fn ($subject) => ($subject->pivot->loai_hoc_phan ?? 'bat_buoc') === 'bat_buoc')->values();
                                $electiveSubjects = $item->subjects->filter(fn ($subject) => ($subject->pivot->loai_hoc_phan ?? 'bat_buoc') === 'tu_chon')->values();
                                $totalCredits = (int) $item->sumCreditsForCurriculumTotal();
                                $requiredCredits = (int) $item->sumRequiredCredits();
                                $electiveCredits = (int) $item->sumElectiveCreditsCountOncePerGroup();
                            @endphp

                            <tr class="curriculum-table__term-row curriculum-term-toggle" data-term-target="{{ $termRowKey }}" aria-expanded="false">
                                <td colspan="4" class="fw-bold text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <span>{{ $item->ten_ky }}</span>
                                    </div>
                                </td>
                                <td class="fw-bold text-center">{{ $totalCredits }}</td>
                                <td colspan="5">
                                    @if($item->ghi_chu)
                                        <span class="text-muted">{{ $item->ghi_chu }}</span>
                                    @endif
                                </td>
                            </tr>

                            @if($requiredSubjects->isNotEmpty())
                                <tr class="curriculum-table__section-row curriculum-detail-row" data-term-row="{{ $termRowKey }}" style="display: none;">
                                    <td colspan="4" class="fw-bold">Học phần bắt buộc</td>
                                    <td class="fw-bold text-center">{{ $requiredCredits }}</td>
                                    <td colspan="5"></td>
                                </tr>

                                @foreach($requiredSubjects as $subject)
                                    @php
                                        $subjectStatus = $subjectStatuses[(int) $subject->id] ?? null;
                                    @endphp
                                    <tr class="curriculum-detail-row" data-term-row="{{ $termRowKey }}" style="display: none;">
                                        <td class="text-center">{{ $rowNumber++ }}</td>
                                        <td>{{ $subject->ten_mon_hoc }}</td>
                                        <td class="text-center">{{ $subject->ma_mon_hoc }}</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">{{ $subject->so_tin_chi }}</td>
                                        <td class="text-center">{{ $subject->so_tiet_ly_thuyet ?? 0 }}</td>
                                        <td class="text-center">{{ $subject->so_tiet_thuc_hanh ?? 0 }}</td>
                                        <td class="text-center">{{ (int) ($subject->pivot->nhom_tu_chon ?? 0) > 0 ? (int) $subject->pivot->nhom_tu_chon : '-' }}</td>
                                <tr class="curriculum-table__section-row curriculum-detail-row" data-term-row="{{ $termRowKey }}" style="display: none;">
                                    <td colspan="4" class="fw-bold">Học phần tự chọn</td>
                                    <td class="fw-bold text-center">{{ $electiveCredits }}</td>
                                    <td colspan="5"></td>
                                </tr>

                                @foreach($electiveSubjects as $subject)
                                    @php
                                        $subjectStatus = $subjectStatuses[(int) $subject->id] ?? null;
                                    @endphp
                                    <tr class="curriculum-detail-row" data-term-row="{{ $termRowKey }}" style="display: none;">
                                        <td class="text-center">{{ $rowNumber++ }}</td>
                                        <td>{{ $subject->ten_mon_hoc }}</td>
                                        <td class="text-center">{{ $subject->ma_mon_hoc }}</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">{{ $subject->so_tin_chi }}</td>
                                        <td class="text-center">{{ $subject->so_tiet_ly_thuyet ?? 0 }}</td>
                                        <td class="text-center">{{ $subject->so_tiet_thuc_hanh ?? 0 }}</td>
                                        <td class="text-center">{{ ($k = $subject->electiveCreditPoolKey($subject->pivot)) > 0 ? $k : '-' }}</td>
                                        <td class="text-center">{{ (int) ($subject->pivot->so_tc_bat_buoc_cua_nhom ?? 0) > 0 ? (int) $subject->pivot->so_tc_bat_buoc_cua_nhom : '-' }}</td>
                                        <td class="text-center">
                                            @if($subjectStatus === 'passed')
                                                <span class="curriculum-status curriculum-status--passed">
                                                    <i class="fas fa-check"></i>
                                                </span>
                                            @elseif($subjectStatus === 'failed')
                                                <span class="curriculum-status curriculum-status--failed">
                                                    <i class="fas fa-check"></i>
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                        <tr class="curriculum-table__summary-row">
                            <td colspan="4" class="fw-bold">Tổng TC yêu cầu</td>
                            <td class="fw-bold text-center text-danger">{{ $totalCreditsAll }}</td>
                            <td colspan="5"></td>
                        </tr>
                        <tr class="curriculum-table__summary-row">
                            <td colspan="4" class="fw-bold">Tổng TC bắt buộc</td>
                            <td class="fw-bold text-center text-danger">{{ $totalRequiredCreditsAll }}</td>
                            <td colspan="5"></td>
                        </tr>
                        <tr class="curriculum-table__summary-row">
                            <td colspan="4" class="fw-bold">Tổng TC tự chọn</td>
                            <td class="fw-bold text-center text-danger">{{ $totalElectiveCreditsAll }}</td>
                            <td colspan="5"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
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

    .curriculum-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 999px;
        font-size: 11px;
    }

    .curriculum-status--passed {
        background: #2ed05c;
        color: #fff;
    }

    .curriculum-status--failed {
        background: #ef4444;
        color: #fff;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.curriculum-term-toggle').forEach(function (row) {
            row.addEventListener('click', function () {
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
