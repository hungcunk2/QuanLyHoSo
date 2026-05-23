@php
    $grid = $scheduleGrid ?? ['morning' => array_fill(0, 7, []), 'afternoon' => array_fill(0, 7, []), 'evening' => array_fill(0, 7, [])];
    $sessionRows = ['morning' => 'Sáng', 'afternoon' => 'Chiều', 'evening' => 'Tối'];
    $currentDate = $currentDate ?? now();
    $weekStart = $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
    $rescheduleMode = (bool) ($rescheduleMode ?? false);
    $compact = (bool) ($compact ?? false);
    $rowMinHeight = $compact ? '200px' : 'calc((100vh - 260px) / 3)';
@endphp

<p class="schedule-scroll-hint mb-0 d-md-none px-3 pt-2">
    <i class="fas fa-arrows-left-right me-1"></i> Vuốt ngang để xem đủ 7 ngày trong tuần (Thứ 2 – Chủ nhật).
</p>
<div class="table-responsive schedule-table-scroll">
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
                <tr class="schedule-week-row" style="min-height: {{ $rowMinHeight }};">
                    <th class="text-start ps-3 align-top pt-3" style="background-color: rgb(255,255,206);">{{ $sessionLabel }}</th>
                    @for ($i = 0; $i < 7; $i++)
                        <td class="position-relative align-top p-2">
                            @foreach ($grid[$sessionKey][$i] ?? [] as $slot)
                                @php
                                    $selectable = $rescheduleMode && ($slot['selectable'] ?? false);
                                    $cursor = $selectable ? 'pointer' : 'default';
                                @endphp
                                <div
                                    class="schedule-slot mb-2 px-3 py-3 rounded-2 text-white text-start shadow-sm{{ $selectable ? ' schedule-slot--pickable' : '' }}"
                                    style="background-color: {{ $slot['badge'] ?? '#3498db' }}; min-height: 5.25rem; cursor: {{ $cursor }};"
                                    data-kind="{{ $slot['kind'] ?? 'study' }}"
                                    @if ($rescheduleMode)
                                        data-session-key="{{ $slot['session_key'] ?? '' }}"
                                        data-date="{{ $slot['date'] ?? '' }}"
                                        data-thu="{{ $slot['thu'] ?? '' }}"
                                        data-tiet="{{ $slot['tiet'] ?? '' }}"
                                        data-pick-label="{{ $slot['pick_label'] ?? ($slot['title'] ?? '') }}"
                                        data-selectable="{{ $selectable ? '1' : '0' }}"
                                    @endif
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
            <span class="badge" style="background-color:#f39c12;color:#fff;min-width:30px;">&nbsp;</span>
            <span>Lịch thi</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge" style="background-color:#e74c3c;color:#fff;min-width:30px;">&nbsp;</span>
            <span>Lịch tạm ngưng</span>
        </div>
        @if ($rescheduleMode)
            <div class="d-flex align-items-center gap-2 text-muted small">
                <i class="fas fa-hand-pointer"></i>
                <span>Bấm ô LT/TH để chọn buổi cần dời (cùng lịch SV/GV).</span>
            </div>
        @endif
    </div>
</div>
