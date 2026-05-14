<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lịch dạy</title>
    <style>
        @page { margin: 14mm 10mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #111; font-size: 12px; }
        .muted { color: #555; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .title { font-size: 16px; font-weight: 700; margin: 0; }
        .meta { font-size: 11px; margin-top: 3px; }
        .week-title { font-weight: 700; margin: 10px 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #b9c3cc; padding: 6px; vertical-align: top; }
        th { background: #eef3f6; text-align: center; font-weight: 700; }
        .session { background: #fff6ce; font-weight: 700; width: 72px; }
        .slot { margin: 0 0 6px; padding: 6px; border-radius: 4px; color: #fff; }
        .slot-title { font-weight: 700; font-size: 12px; line-height: 1.25; }
        .slot-meta { font-size: 11px; opacity: .95; margin-top: 2px; line-height: 1.25; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
@php
    $heading = $range === 'month' ? 'Lịch dạy theo tháng' : 'Lịch dạy theo tuần';
@endphp
<div class="header">
    <div>
        <div class="title">{{ $heading }}</div>
        <div class="meta muted">
            Giảng viên: <strong>{{ $teacher->ho_ten ?? $teacher->msgv ?? '—' }}</strong>
            @if(!empty($teacher->msgv))
                · MSGV: <strong>{{ $teacher->msgv }}</strong>
            @endif
        </div>
        <div class="meta muted">
            @if($range === 'month')
                Tháng: <strong>{{ $baseDate->format('m/Y') }}</strong>
            @else
                Tuần: <strong>{{ $from->format('d/m/Y') }} → {{ $to->format('d/m/Y') }}</strong>
            @endif
        </div>
    </div>
    <div class="meta muted" style="text-align:right;">
        Ngày in: <strong>{{ now()->format('d/m/Y H:i') }}</strong>
    </div>
</div>

@php
    $sessionRows = ['morning' => 'Sáng', 'afternoon' => 'Chiều', 'evening' => 'Tối'];
@endphp

@foreach(($weeks ?? []) as $wIndex => $w)
    @php
        $currentDate = $w['currentDate'];
        $grid = $w['scheduleGrid'] ?? ['morning' => array_fill(0, 7, []), 'afternoon' => array_fill(0, 7, []), 'evening' => array_fill(0, 7, [])];
        $weekStart = $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $weekEnd = $currentDate->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
    @endphp

    @if($range === 'month')
        <div class="week-title">Tuần: {{ $weekStart->format('d/m/Y') }} → {{ $weekEnd->format('d/m/Y') }}</div>
    @endif

    <table>
        <thead>
        <tr>
            <th style="width:72px;">Ca dạy</th>
            @for ($i = 0; $i < 7; $i++)
                @php $day = $weekStart->copy()->addDays($i); @endphp
                <th>
                    <div>
                        @if ($i === 6)
                            Chủ nhật
                        @else
                            Thứ {{ $i + 2 }}
                        @endif
                    </div>
                    <div>{{ $day->format('d/m/Y') }}</div>
                </th>
            @endfor
        </tr>
        </thead>
        <tbody>
        @foreach ($sessionRows as $sessionKey => $sessionLabel)
            <tr>
                <td class="session">{{ $sessionLabel }}</td>
                @for ($i = 0; $i < 7; $i++)
                    <td>
                        @foreach ($grid[$sessionKey][$i] ?? [] as $slot)
                            <div class="slot" style="background: {{ $slot['badge'] ?? '#3498db' }};">
                                <div class="slot-title">{{ $slot['title'] }}</div>
                                <div class="slot-meta">{{ $slot['meta'] ?? '' }}</div>
                            </div>
                        @endforeach
                    </td>
                @endfor
            </tr>
        @endforeach
        </tbody>
    </table>

    @if($range === 'month' && $wIndex < count($weeks) - 1)
        <div class="page-break"></div>
    @endif
@endforeach
</body>
</html>
