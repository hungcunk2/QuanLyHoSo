@extends('layouts.student')

@section('title', 'Lịch Học')
@section('page-title', 'Lịch Học')

@section('content')
<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3 flex-nowrap">
                <h5 class="mb-0 me-3">Lịch học, lịch thi theo tuần</h5>
                <div class="d-flex align-items-center gap-3 flex-nowrap">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="scheduleFilter" id="filterAll" checked>
                        <label class="form-check-label" for="filterAll">Tất cả</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="scheduleFilter" id="filterStudy">
                        <label class="form-check-label" for="filterStudy">Lịch học</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="scheduleFilter" id="filterExam">
                        <label class="form-check-label" for="filterExam">Lịch thi</label>
                    </div>
                    <form method="GET" action="{{ route('student.schedule') }}" class="mb-0">
                        <input
                            type="date"
                            name="date"
                            class="form-control form-control-sm"
                            value="{{ $currentDate->toDateString() }}"
                            style="width: auto; min-width: 140px;"
                            onchange="this.form.submit()"
                        >
                    </form>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 flex-nowrap">
                <a href="{{ route('student.schedule') }}" class="btn btn-primary btn-sm px-3" style="min-width: 90px; white-space: nowrap;">Hiện tại</a>
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" style="min-width: 90px; white-space: nowrap;">In lịch</button>
                <a href="{{ route('student.schedule', ['date' => $currentDate->copy()->subWeek()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm px-3" style="min-width: 90px; white-space: nowrap;">&lt; Trở về</a>
                <a href="{{ route('student.schedule', ['date' => $currentDate->copy()->addWeek()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm px-3" style="min-width: 90px; white-space: nowrap;">Tiếp &gt;</a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 text-center align-middle schedule-table">
                <thead>
                    <tr style="background-color: #F3F7F9;">
                        <th class="fw-bold" style="width: 80px; background-color: #F3F7F9;">Ca học</th>
                        @php
                            $startOfWeek = $currentDate->copy()->startOfWeek();
                        @endphp
                        @for ($i = 0; $i < 7; $i++)
                            @php
                                $day = $startOfWeek->copy()->addDays($i);
                            @endphp
                            <th style="background-color: #F3F7F9;">
                                <div class="fw-bold text-primary" style="font-size: 1.05rem;">
                                    @if ($i === 6)
                                        Chủ nhật
                                    @else
                                        Thứ {{ $i + 2 }}
                                    @endif
                                </div>
                                <div class="fw-bold text-primary" style="font-size: 1.05rem;">{{ $day->format('d/m/Y') }}</div>
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sessions = ['Sáng', 'Chiều', 'Tối'];
                    @endphp
                    @foreach ($sessions as $session)
                        <tr style="min-height: 200px; height: calc((100vh - 340px) / 3);">
                            <th class="text-start ps-3" style="background-color: rgb(255,255,206);" rowspan="1">{{ $session }}</th>
                            @for ($i = 0; $i < 7; $i++)
                                <td class="position-relative">
                                    {{-- Ô trống, sau này sẽ đổ dữ liệu tiết học vào đây --}}
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-3 py-2 border-top bg-white">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge" style="background-color:#3498db;color:#fff;min-width:30px;">&nbsp;</span>
                    <span>Lịch học lý thuyết</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge" style="background-color:#27ae60;color:#fff;min-width:30px;">&nbsp;</span>
                    <span>Lịch học thực hành</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge" style="background-color:#1abc9c;color:#fff;min-width:30px;">&nbsp;</span>
                    <span>Lịch học trực tuyến</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge" style="background-color:#f39c12;color:#fff;min-width:30px;">&nbsp;</span>
                    <span>Lịch thi</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge" style="background-color:#e74c3c;color:#fff;min-width:30px;">&nbsp;</span>
                    <span>Lịch tạm ngưng</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

