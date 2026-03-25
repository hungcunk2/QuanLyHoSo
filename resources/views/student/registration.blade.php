@extends('layouts.student')

@section('title', 'Đăng Ký Học Phần')
@section('page-title', 'Đăng Ký Học Phần')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Đăng Ký Học Phần</h5>
            <div class="small text-muted">
                Hôm nay: {{ ($today ?? \Carbon\Carbon::today())->format('d/m/Y') }}
                @if(!empty($classRoom))
                    · Lớp: <strong>{{ $classRoom->ma_lop }}</strong>
                @endif
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted mb-0">
                Danh sách dưới đây là các học phần có lớp đang trong giai đoạn <strong>từ khi mở đăng ký đến trước khi kết thúc học</strong>.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @php
                $todayX = ($today ?? \Carbon\Carbon::today());
                $grouped = collect($offerings ?? [])->groupBy(function($o){
                    return $o->subject_id ?? 0;
                });
            @endphp

            @if($grouped->isEmpty())
                <div class="text-center text-muted py-4">
                    Hiện chưa có môn nào phù hợp.
                </div>
            @else
                <div class="accordion" id="subjectsAccordion">
                    @foreach($grouped as $subjectId => $rows)
                        @php
                            $first = $rows->first();
                            $subjectTitle = $first?->subject ? ($first->subject->ma_mon_hoc . ' - ' . $first->subject->ten_mon_hoc) : 'Môn học';
                            $accId = 'sub_' . $subjectId;
                        @endphp
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading_{{ $accId }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $accId }}" aria-expanded="false" aria-controls="collapse_{{ $accId }}">
                                    <div class="d-flex justify-content-between align-items-center w-100 pe-3">
                                        <div class="fw-bold">{{ $subjectTitle }}</div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse_{{ $accId }}" class="accordion-collapse collapse" aria-labelledby="heading_{{ $accId }}" data-bs-parent="#subjectsAccordion">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped border align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Lớp / Học phần</th>
                                                    <th>Giáo viên</th>
                                                    <th>Thời gian đăng ký</th>
                                                    <th>Sĩ số</th>
                                                    <th>Trạng thái lớp</th>
                                                    <th>Trạng thái đăng ký</th>
                                                    <th width="180">Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($rows as $o)
                                                    @php
                                                        $reg = ($myRegs ?? collect())->get($o->id);
                                                        $status = $reg->status ?? null;
                                                        $daDangKy = $status && $status !== 'cancelled';
                                                        $count = (int) ($o->registrations_count ?? 0);
                                                        $siSo = (int) ($o->si_so_lop ?? 0);
                                                        $conLai = $siSo - $count;

                                                        $dangHoc = $o->ngay_bat_dau_hoc && $o->ngay_bat_dau_hoc->lte($todayX);
                                                        $dangMoDangKy = $o->ngay_mo_dang_ky && $o->ngay_ket_thuc_dang_ky
                                                            && $o->ngay_mo_dang_ky->lte($todayX) && $o->ngay_ket_thuc_dang_ky->gte($todayX);

                                                        $ltSessions = collect();
                                                        if ($o->thu_ly_thuyet && $o->tiet_ly_thuyet) {
                                                            $ltSessions->push(['thu' => (int) $o->thu_ly_thuyet, 'tiet' => (string) $o->tiet_ly_thuyet]);
                                                        }
                                                        $thSessions = collect();
                                                        if ($o->thu_thuc_hanh && $o->tiet_thuc_hanh) {
                                                            $thSessions->push(['thu' => (int) $o->thu_thuc_hanh, 'tiet' => (string) $o->tiet_thuc_hanh]);
                                                        }
                                                        foreach(($o->schedules ?? collect()) as $sc) {
                                                            if ($sc->loai === 'ly_thuyet') $ltSessions->push(['thu' => (int) $sc->thu, 'tiet' => (string) $sc->tiet]);
                                                            if ($sc->loai === 'thuc_hanh') $thSessions->push(['thu' => (int) $sc->thu, 'tiet' => (string) $sc->tiet]);
                                                        }
                                                        $schedulePayload = [
                                                            'ten_hoc_phan' => $o->ten_hoc_phan,
                                                            'subject' => ($o->subject?->ma_mon_hoc ? ($o->subject?->ma_mon_hoc . ' - ' . $o->subject?->ten_mon_hoc) : ''),
                                                            'class' => ($o->classRoom?->ma_lop ? ($o->classRoom?->ma_lop . ' - ' . $o->classRoom?->ten_lop) : ''),
                                                            'teacher' => ($o->teacher?->ho_ten ?? ''),
                                                            'date_range' => (optional($o->ngay_bat_dau_hoc)->format('d/m/Y') ?? '—') . ' → ' . (optional($o->ngay_ket_thuc_hoc)->format('d/m/Y') ?? '—'),
                                                            'lt' => $ltSessions->values(),
                                                            'th' => $thSessions->values(),
                                                        ];
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <div class="fw-bold">{{ $o->classRoom?->ma_lop ?? '—' }} <span class="text-muted">·</span> {{ $o->ten_hoc_phan }}</div>
                                                            <div class="small text-muted">
                                                                Học: {{ optional($o->ngay_bat_dau_hoc)->format('d/m/Y') ?? '—' }} → {{ optional($o->ngay_ket_thuc_hoc)->format('d/m/Y') ?? '—' }}
                                                            </div>
                                                        </td>
                                                        <td>{{ $o->teacher?->ho_ten ?? '—' }}</td>
                                                        <td>
                                                            {{ optional($o->ngay_mo_dang_ky)->format('d/m/Y') ?? '—' }}
                                                            →
                                                            {{ optional($o->ngay_ket_thuc_dang_ky)->format('d/m/Y') ?? '—' }}
                                                        </td>
                                                        <td>
                                                            <div>{{ $count }} / {{ $siSo }}</div>
                                                            <div class="small {{ $conLai > 0 ? 'text-success' : 'text-danger' }}">
                                                                Còn lại: {{ $conLai < 0 ? 0 : $conLai }}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if($dangHoc)
                                                                <span class="badge bg-success">Đang học</span>
                                                            @elseif($dangMoDangKy)
                                                                <span class="badge bg-warning text-dark">Đang chờ sinh viên đăng kí</span>
                                                            @else
                                                                <span class="badge bg-light text-dark">—</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($status === 'approved')
                                                                <span class="badge bg-success">Đã đăng ký</span>
                                                            @elseif($status === 'cancelled')
                                                                <span class="badge bg-secondary">Đã hủy</span>
                                                            @else
                                                                <span class="badge bg-light text-dark">Chưa đăng ký</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <button type="button"
                                                                    class="btn btn-outline-secondary btn-sm w-100 mb-2 btn-view-schedule"
                                                                    data-schedule='@json($schedulePayload)'>
                                                                Xem lịch học
                                                            </button>

                                                            @if($daDangKy)
                                                                <form method="POST" action="{{ route('student.registration.cancel', $o->id) }}">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">Hủy đăng ký</button>
                                                                </form>
                                                            @else
                                                               
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal xem lịch học -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleModalLabel">Lịch học</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2 fw-bold" id="schTen"></div>
                <div class="small text-muted mb-3" id="schMeta"></div>

                <div class="mb-3">
                    <div class="fw-bold mb-1">Lý thuyết</div>
                    <div id="schLT" class="small"></div>
                </div>
                <div>
                    <div class="fw-bold mb-1">Thực hành</div>
                    <div id="schTH" class="small"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const weekday = {2:'Thứ 2',3:'Thứ 3',4:'Thứ 4',5:'Thứ 5',6:'Thứ 6',7:'Thứ 7',8:'Chủ nhật'};
        function renderSessions(list) {
            if (!list || !list.length) return '<span class="text-muted">—</span>';
            return '<ul class="mb-0 ps-3">' + list.map(s => `<li>${weekday[s.thu] || ('Thứ ' + s.thu)} · tiết ${s.tiet}</li>`).join('') + '</ul>';
        }
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-view-schedule');
            if (!btn) return;
            const data = JSON.parse(btn.getAttribute('data-schedule') || '{}');
            document.getElementById('schTen').textContent = data.ten_hoc_phan || '—';
            const meta = [data.subject, data.class, data.teacher, ('Học: ' + (data.date_range || '—'))].filter(Boolean).join(' · ');
            document.getElementById('schMeta').textContent = meta;
            document.getElementById('schLT').innerHTML = renderSessions(data.lt || []);
            document.getElementById('schTH').innerHTML = renderSessions(data.th || []);
            const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
            modal.show();
        });
    })();
</script>
@endpush
@endsection

