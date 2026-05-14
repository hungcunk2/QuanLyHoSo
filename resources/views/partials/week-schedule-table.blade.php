@php
    $scheduleRouteName = $scheduleRouteName ?? 'student.schedule';
    $headingText = $headingText ?? 'Lịch học, lịch thi theo tuần';
    $grid = $scheduleGrid ?? ['morning' => array_fill(0, 7, []), 'afternoon' => array_fill(0, 7, []), 'evening' => array_fill(0, 7, [])];
    $sessionRows = ['morning' => 'Sáng', 'afternoon' => 'Chiều', 'evening' => 'Tối'];
    $weekStart = $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
@endphp

<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3 flex-nowrap">
                <h5 class="mb-0 me-3">{{ $headingText }}</h5>
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
                    <form method="GET" action="{{ route($scheduleRouteName) }}" class="mb-0">
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
                <a href="{{ route($scheduleRouteName) }}" class="btn btn-primary btn-sm px-3" style="min-width: 90px; white-space: nowrap;">Hiện tại</a>
                <button type="button"
                        class="btn btn-outline-secondary btn-sm px-3 btn-print-schedule"
                        style="min-width: 90px; white-space: nowrap;"
                        data-date="{{ $currentDate->toDateString() }}">
                    In lịch
                </button>
                <a href="{{ route($scheduleRouteName, ['date' => $currentDate->copy()->subWeek()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm px-3" style="min-width: 90px; white-space: nowrap;">&lt; Trở về</a>
                <a href="{{ route($scheduleRouteName, ['date' => $currentDate->copy()->addWeek()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm px-3" style="min-width: 90px; white-space: nowrap;">Tiếp &gt;</a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 text-center align-middle schedule-table">
                <thead>
                    <tr style="background-color: #F3F7F9;">
                        <th class="fw-bold" style="width: 80px; background-color: #F3F7F9;">Ca học</th>
                        @for ($i = 0; $i < 7; $i++)
                            @php $day = $weekStart->copy()->addDays($i); @endphp
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
                    @foreach ($sessionRows as $sessionKey => $sessionLabel)
                        <tr class="schedule-week-row" style="min-height: 280px; height: calc((100vh - 260px) / 3);">
                            <th class="text-start ps-3 align-top pt-3" style="background-color: rgb(255,255,206);">{{ $sessionLabel }}</th>
                            @for ($i = 0; $i < 7; $i++)
                                <td class="position-relative align-top p-2">
                                    @foreach ($grid[$sessionKey][$i] ?? [] as $slot)
                                        <div
                                            class="schedule-slot mb-2 px-3 py-3 rounded-2 text-white text-start shadow-sm"
                                            style="background-color: {{ $slot['badge'] ?? '#3498db' }}; min-height: 5.25rem;"
                                            data-kind="{{ $slot['kind'] ?? 'study' }}"
                                        >
                                            <div class="fw-semibold" style="font-size: 0.95rem; line-height: 1.35;">{{ $slot['title'] }}</div>
                                            <div class="opacity-90 mt-1" style="font-size: 0.875rem; line-height: 1.4;">{{ $slot['meta'] ?? '' }}</div>
                                        </div>
                                    @endforeach
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

<!-- Modal in lịch -->
<div class="modal fade" id="printScheduleModal" tabindex="-1" aria-labelledby="printScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printScheduleModalLabel">In lịch (PDF)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="small text-muted mb-2" id="printScheduleHint"></div>
                <div class="d-grid gap-2">
                    <a class="btn btn-primary" id="btnPrintWeek" href="#">Tuần này</a>
                    <a class="btn btn-outline-primary" id="btnPrintMonth" href="#">Tháng này</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function applyScheduleFilter() {
            var checked = document.querySelector('input[name="scheduleFilter"]:checked');
            var id = checked ? checked.id : 'filterAll';
            document.querySelectorAll('.schedule-slot').forEach(function(el) {
                var kind = el.getAttribute('data-kind') || 'study';
                var show = true;
                if (id === 'filterStudy') {
                    show = (kind === 'study' || kind === 'pause');
                }
                if (id === 'filterExam') {
                    show = kind === 'exam';
                }
                el.style.display = show ? '' : 'none';
            });
        }
        document.querySelectorAll('input[name="scheduleFilter"]').forEach(function(r) {
            r.addEventListener('change', applyScheduleFilter);
        });

        var printBtn = document.querySelector('.btn-print-schedule');
        if (printBtn) {
            printBtn.addEventListener('click', function () {
                var date = this.getAttribute('data-date') || '';
                var hint = document.getElementById('printScheduleHint');
                if (hint && date) {
                    hint.textContent = 'Ngày đang xem: ' + date.split('-').reverse().join('/');
                }

                var weekLink = document.getElementById('btnPrintWeek');
                var monthLink = document.getElementById('btnPrintMonth');

                var isTeacher = '{{ $scheduleRouteName }}' === 'teacher.schedule';
                var pdfBaseUrl = isTeacher ? '{{ route('teacher.schedule.pdf') }}' : '{{ route('student.schedule.pdf') }}';
                if (weekLink) {
                    weekLink.onclick = null;
                    weekLink.href = pdfBaseUrl + '?range=week&date=' + encodeURIComponent(date);
                }
                if (monthLink) {
                    monthLink.onclick = null;
                    monthLink.href = pdfBaseUrl + '?range=month&date=' + encodeURIComponent(date);
                }

                var modal = new bootstrap.Modal(document.getElementById('printScheduleModal'));
                modal.show();
            });
        }
    });
</script>
@endpush
