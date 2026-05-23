@php
    $scheduleRouteName = $scheduleRouteName ?? 'student.schedule';
    $headingText = $headingText ?? 'Lịch học, lịch thi theo tuần';
    $grid = $scheduleGrid ?? ['morning' => array_fill(0, 7, []), 'afternoon' => array_fill(0, 7, []), 'evening' => array_fill(0, 7, [])];
@endphp

<div class="card">
    <div class="card-header bg-light schedule-toolbar">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 schedule-toolbar__row">
            <div class="d-flex align-items-center gap-3 flex-wrap schedule-toolbar__filters">
                <h5 class="mb-0 me-md-3">{{ $headingText }}</h5>
                <div class="d-flex align-items-center gap-2 gap-md-3 flex-wrap">
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
            <div class="d-flex align-items-center gap-2 gap-md-3 flex-wrap schedule-toolbar__actions">
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
        @include('partials.week-schedule-grid', [
            'currentDate' => $currentDate,
            'scheduleGrid' => $grid,
            'rescheduleMode' => false,
            'compact' => false,
        ])
    </div>
</div>

@push('modals')
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
@endpush

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
