@extends('layouts.student')

@section('title', 'Đăng Ký Học Phần')
@section('page-title', '')

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
                @if(!empty($student) && !empty($student->lop))
                    · Lớp: <strong>{{ $studentLop ? ($studentLop->ma_lop.' — '.$studentLop->ten_lop) : $student->lop }}</strong>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3 registration-toolbar">
                <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="text-muted small">Năm học: <strong>{{ $currentKhoaHoc ?? '' }}</strong></div>
                    <select name="hoc_ky" class="form-select form-select-sm" style="width: 220px; max-width: 100%;" onchange="this.form.submit()">
                        @foreach(($hocKyOptions ?? []) as $opt)
                            <option value="{{ $opt['value'] }}" {{ ($selectedHocKy ?? '') === (string)$opt['value'] ? 'selected' : '' }}>
                                {{ $opt['label'] }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

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
                                                            $ltSessions->push([
                                                                'thu' => (int) $o->thu_ly_thuyet,
                                                                'tiet' => (string) $o->tiet_ly_thuyet,
                                                                'teacher' => $o->teacherLyThuyet?->ho_ten ?? '',
                                                                'room' => $o->classRoom?->ma_lop ? ($o->classRoom->ma_lop.' - '.$o->classRoom->ten_lop) : '',
                                                            ]);
                                                        }
                                                        foreach(($o->schedules ?? collect()) as $sc) {
                                                            if ($sc->loai === 'ly_thuyet') {
                                                                $ltSessions->push([
                                                                    'thu' => (int) $sc->thu,
                                                                    'tiet' => (string) $sc->tiet,
                                                                    'teacher' => $sc->teacher?->ho_ten ?? '',
                                                                    'room' => $o->classRoom?->ma_lop ? ($o->classRoom->ma_lop.' - '.$o->classRoom->ten_lop) : '',
                                                                ]);
                                                            }
                                                        }
                                                        $teacherLtName = $o->teacherLyThuyet?->ho_ten ?? '';
                                                        $teacherThName = $o->teacherThucHanh?->ho_ten ?? '';
                                                        $teacherLabel = '';
                                                        if ($teacherLtName && $teacherThName && $teacherLtName !== $teacherThName) {
                                                            $teacherLabel = 'LT: ' . $teacherLtName . ' / TH: ' . $teacherThName;
                                                        } elseif ($teacherLtName) {
                                                            $teacherLabel = $teacherLtName;
                                                        } elseif ($teacherThName) {
                                                            $teacherLabel = $teacherThName;
                                                        }

                                                        $thGroupsForView = [];
                                                        if ($o->thu_thuc_hanh && $o->tiet_thuc_hanh) {
                                                            $thGroupsForView[] = [
                                                                'index' => 1,
                                                                'thu' => (int) $o->thu_thuc_hanh,
                                                                'tiet' => (string) $o->tiet_thuc_hanh,
                                                                'teacher' => $o->teacherThucHanh?->ho_ten ?? '',
                                                                'room' => $o->classRoomThucHanh?->ma_lop ? ($o->classRoomThucHanh->ma_lop.' - '.$o->classRoomThucHanh->ten_lop) : '',
                                                            ];
                                                        }
                                                        $thSchedulesForView = collect($o->schedules ?? collect())
                                                            ->where('loai', 'thuc_hanh')
                                                            ->sortBy('id')
                                                            ->values();
                                                        foreach ($thSchedulesForView as $sc) {
                                                            if (! $sc->thu || ($sc->tiet ?? '') === '') {
                                                                continue;
                                                            }
                                                            $thGroupsForView[] = [
                                                                'index' => count($thGroupsForView) + 1,
                                                                'thu' => (int) $sc->thu,
                                                                'tiet' => (string) $sc->tiet,
                                                                'teacher' => $sc->teacher?->ho_ten ?? '',
                                                                'room' => $sc->classRoom?->ma_lop ? ($sc->classRoom->ma_lop.' - '.$sc->classRoom->ten_lop) : '',
                                                            ];
                                                        }

                                                        $schedulePayload = [
                                                            'ten_hoc_phan' => $o->ten_hoc_phan,
                                                            'subject' => ($o->subject?->ma_mon_hoc ? ($o->subject?->ma_mon_hoc . ' - ' . $o->subject?->ten_mon_hoc) : ''),
                                                            'class' => ($o->classRoom?->ma_lop ? ($o->classRoom?->ma_lop . ' - ' . $o->classRoom?->ten_lop) : ''),
                                                            'teacher' => $teacherLabel,
                                                            'date_range' => (optional($o->ngay_bat_dau_hoc)->format('d/m/Y') ?? '—') . ' → ' . (optional($o->ngay_ket_thuc_hoc)->format('d/m/Y') ?? '—'),
                                                            'lt' => $ltSessions->values(),
                                                            'th_groups' => $thGroupsForView,
                                                        ];
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <div class="fw-bold">{{ $o->classRoom?->ma_lop ?? '—' }} <span class="text-muted">·</span> {{ $o->ten_hoc_phan }}</div>
                                                            <div class="small text-muted">
                                                                Học: {{ optional($o->ngay_bat_dau_hoc)->format('d/m/Y') ?? '—' }} → {{ optional($o->ngay_ket_thuc_hoc)->format('d/m/Y') ?? '—' }}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if($teacherLtName && $teacherThName && $teacherLtName !== $teacherThName)
                                                                <div class="small">LT: {{ $teacherLtName }}</div>
                                                                <div class="small">TH: {{ $teacherThName }}</div>
                                                            @else
                                                                {{ $teacherLtName ?: ($teacherThName ?: '—') }}
                                                            @endif
                                                        </td>
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
                                                                @if($student)
                                                                    @php
                                                                        $coTheDangKy = $dangMoDangKy && $conLai > 0;
                                                                    @endphp
                                                                    @php
                                                                        $thGroupsPayload = [];
                                                                        // Nhóm TH 1 (từ cột chính của course_offerings)
                                                                        if ($o->thu_thuc_hanh && $o->tiet_thuc_hanh) {
                                                                            $cap = (int) ($o->si_so_thuc_hanh_nhom_1 ?? 0);
                                                                            $regCnt = (int) (($thCountsByOffering[$o->id][1] ?? 0) ?? 0);
                                                                            $thGroupsPayload[] = [
                                                                                'index' => 1,
                                                                                'thu' => (int) $o->thu_thuc_hanh,
                                                                                'tiet' => (string) $o->tiet_thuc_hanh,
                                                                                'teacher' => $o->teacherThucHanh?->ho_ten ?? '',
                                                                                'room' => $o->classRoomThucHanh?->ma_lop ? ($o->classRoomThucHanh->ma_lop.' - '.$o->classRoomThucHanh->ten_lop) : '',
                                                                                'capacity' => $cap,
                                                                                'registered' => $regCnt,
                                                                            ];
                                                                        }
                                                                        // Các nhóm TH tiếp theo (từ schedules loai=thuc_hanh)
                                                                        $thSchedules = collect($o->schedules ?? collect())
                                                                            ->where('loai', 'thuc_hanh')
                                                                            ->sortBy('id')
                                                                            ->values();
                                                                        foreach ($thSchedules as $idx => $sc) {
                                                                            if (! $sc->thu || ($sc->tiet ?? '') === '') {
                                                                                continue;
                                                                            }
                                                                            $groupIndex = count($thGroupsPayload) + 1;
                                                                            $cap = $groupIndex === 2 ? (int) ($o->si_so_thuc_hanh_nhom_2 ?? 0) : 0;
                                                                            $regCnt = (int) (($thCountsByOffering[$o->id][$groupIndex] ?? 0) ?? 0);
                                                                            $thGroupsPayload[] = [
                                                                                'index' => $groupIndex,
                                                                                'thu' => (int) $sc->thu,
                                                                                'tiet' => (string) $sc->tiet,
                                                                                'teacher' => $sc->teacher?->ho_ten ?? '',
                                                                                'room' => $sc->classRoom?->ma_lop ? ($sc->classRoom->ma_lop.' - '.$sc->classRoom->ten_lop) : '',
                                                                                'capacity' => $cap,
                                                                                'registered' => $regCnt,
                                                                            ];
                                                                        }
                                                                    @endphp
                                                                    <form method="POST" action="{{ route('student.registration.register', $o->id) }}" class="mb-0 form-register-offering" data-th-groups='@json($thGroupsPayload)' data-offering-name="{{ $o->ten_hoc_phan }}">
                                                                        @csrf
                                                                        <input type="hidden" name="th_group_index" value="">
                                                                        <button type="submit" class="btn btn-primary btn-sm w-100" @if(!$coTheDangKy) disabled @endif>
                                                                            Đăng ký
                                                                        </button>
                                                                    </form>
                                                                    @if($conLai <= 0)
                                                                        <div class="small text-danger mt-1">Lớp đã đủ sĩ số.</div>
                                                                    @endif
                                                                @else
                                                                    <span class="small text-muted">Cần hồ sơ học sinh trùng tài khoản.</span>
                                                                @endif
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

    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0 fw-bold">Lớp học phần đã đăng ký ({{ ($myRegisteredOfferings ?? collect())->count() }})</h6>
            <div class="small text-muted">
                Kỳ: <strong>HK{{ $selectedHocKy ?? '' }} ({{ $currentKhoaHoc ?? '' }})</strong>
            </div>
        </div>
        <div class="card-body p-0">
            @if(($myRegisteredOfferings ?? collect())->isEmpty())
                <div class="p-3 text-muted">Bạn chưa đăng ký lớp học phần nào trong kỳ này.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 320px;">Học phần</th>
                                <th style="min-width: 180px;">Lớp / Phòng</th>
                                <th style="min-width: 220px;">Giáo viên</th>
                                <th style="min-width: 120px;">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($myRegisteredOfferings as $o)
                                @php
                                    $sub = $o->subject;
                                    $name = $sub ? ($sub->ma_mon_hoc.' — '.$sub->ten_mon_hoc) : ($o->ten_hoc_phan ?? '—');
                                    $room = $o->classRoom?->ma_lop ? ($o->classRoom->ma_lop.' — '.$o->classRoom->ten_lop) : '—';
                                    $lt = $o->teacherLyThuyet?->ho_ten ?? '';
                                    $th = $o->teacherThucHanh?->ho_ten ?? '';
                                    $teacherLabel = $lt && $th && $lt !== $th ? ('LT: '.$lt.' / TH: '.$th) : ($lt ?: ($th ?: '—'));
                                    $reg = ($myRegs ?? collect())->get($o->id);
                                    $status = $reg->status ?? null;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $name }}</div>
                                        @if($o->ten_hoc_phan)
                                            <div class="small text-muted">{{ $o->ten_hoc_phan }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $room }}</td>
                                    <td>{{ $teacherLabel }}</td>
                                    <td>
                                        @if($status === 'approved')
                                            <span class="badge bg-success">Đã đăng ký</span>
                                        @elseif($status === 'cancelled')
                                            <span class="badge bg-secondary">Đã hủy</span>
                                        @else
                                            <span class="badge bg-light text-dark">—</span>
                                        @endif
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

<!-- Modal chọn nhóm thực hành -->
<div class="modal fade" id="thGroupModal" tabindex="-1" aria-labelledby="thGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="thGroupModalLabel">Chọn nhóm thực hành</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2 fw-bold" id="thGroupOfferingName"></div>
                <div class="small text-muted mb-3" id="thGroupHint"></div>
                <div id="thGroupList"></div>
                <div class="text-danger small mt-2 d-none" id="thGroupError">Vui lòng chọn nhóm thực hành.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="btnConfirmThGroup">Xác nhận đăng ký</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const weekday = {2:'Thứ 2',3:'Thứ 3',4:'Thứ 4',5:'Thứ 5',6:'Thứ 6',7:'Thứ 7',8:'Chủ nhật'};
        function esc(s) {
            return String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        }
        function renderLtTable(list) {
            if (!list || !list.length) return '<span class="text-muted">—</span>';
            const rows = list.map(s => {
                const thu = weekday[s.thu] || ('Thứ ' + s.thu);
                const tiet = esc(s.tiet || '—');
                const room = esc(s.room || '—');
                const teacher = esc(s.teacher || '—');
                return `
                    <tr>
                        <td class="text-nowrap">${esc(thu)}</td>
                        <td class="text-nowrap">Tiết ${tiet}</td>
                        <td>${room}</td>
                        <td>${teacher}</td>
                    </tr>
                `;
            }).join('');
            return `
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="90">Thứ</th>
                                <th width="120">Tiết</th>
                                <th>Phòng</th>
                                <th>Giáo viên</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;
        }
        function renderThGroupsTable(groups) {
            if (!groups || !groups.length) return '<span class="text-muted">—</span>';
            const rows = groups.map(g => {
                const thu = weekday[g.thu] || ('Thứ ' + g.thu);
                const tiet = esc(g.tiet || '—');
                const room = esc(g.room || '—');
                const teacher = esc(g.teacher || '—');
                const idx = esc(g.index || '—');
                return `
                    <tr>
                        <td class="text-nowrap"><span class="badge bg-info text-dark">Nhóm TH ${idx}</span></td>
                        <td class="text-nowrap">${esc(thu)}</td>
                        <td class="text-nowrap">Tiết ${tiet}</td>
                        <td>${room}</td>
                        <td>${teacher}</td>
                    </tr>
                `;
            }).join('');
            return `
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="120">Nhóm</th>
                                <th width="90">Thứ</th>
                                <th width="120">Tiết</th>
                                <th>Phòng</th>
                                <th>Giáo viên</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;
        }
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-view-schedule');
            if (!btn) return;
            const data = JSON.parse(btn.getAttribute('data-schedule') || '{}');
            document.getElementById('schTen').textContent = data.ten_hoc_phan || '—';
            const meta = [data.subject, data.class, data.teacher, ('Học: ' + (data.date_range || '—'))].filter(Boolean).join(' · ');
            document.getElementById('schMeta').textContent = meta;
            document.getElementById('schLT').innerHTML = renderLtTable(data.lt || []);
            document.getElementById('schTH').innerHTML = renderThGroupsTable(data.th_groups || []);
            const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
            modal.show();
        });

        let pendingRegisterForm = null;
        const thModalEl = document.getElementById('thGroupModal');
        const thModal = thModalEl ? new bootstrap.Modal(thModalEl) : null;
        const thListEl = document.getElementById('thGroupList');
        const thOfferingNameEl = document.getElementById('thGroupOfferingName');
        const thHintEl = document.getElementById('thGroupHint');
        const thErrEl = document.getElementById('thGroupError');

        function openThGroupModal(form, groups, offeringName) {
            if (!thModal) return;
            pendingRegisterForm = form;
            if (thOfferingNameEl) thOfferingNameEl.textContent = offeringName || '—';
            if (thHintEl) thHintEl.textContent = 'Học phần có thực hành, bạn cần chọn 1 nhóm để đăng ký.';
            if (thErrEl) thErrEl.classList.add('d-none');
            if (thListEl) {
                thListEl.innerHTML = '';
                const wrap = document.createElement('div');
                wrap.className = 'vstack gap-2';
                groups.forEach((g) => {
                    const id = 'th_group_' + String(g.index);
                    const row = document.createElement('div');
                    row.className = 'border rounded p-2 d-flex align-items-start gap-2 th-group-row';
                    row.setAttribute('role', 'button');
                    row.setAttribute('tabindex', '0');
                    row.setAttribute('data-index', String(g.index));
                    row.innerHTML = `
                        <input class="form-check-input mt-1" type="radio" name="th_group_index_pick" id="${id}" value="${g.index}" tabindex="-1">
                        <div class="flex-grow-1">
                            <div class="fw-bold">Nhóm TH ${g.index}</div>
                            <div class="small text-muted">
                                ${weekday[g.thu] || ('Thứ ' + g.thu)} · tiết ${g.tiet}
                                ${g.room ? (' · ' + g.room) : ''}
                                ${g.teacher ? (' · GV: ' + g.teacher) : ''}
                                ${(typeof g.capacity === 'number' && g.capacity > 0)
                                    ? (` · ${g.registered || 0}/${g.capacity} (còn ${Math.max(0, g.capacity - (g.registered || 0))})`)
                                    : ''}
                            </div>
                        </div>
                    `;
                    const cap = (typeof g.capacity === 'number' ? g.capacity : 0);
                    const reg = (typeof g.registered === 'number' ? g.registered : (g.registered ? parseInt(g.registered, 10) : 0));
                    const full = cap > 0 && reg >= cap;
                    if (full) {
                        row.classList.add('opacity-50');
                        row.setAttribute('aria-disabled', 'true');
                        const radio = row.querySelector('input[name="th_group_index_pick"]');
                        if (radio) radio.disabled = true;
                    }
                    wrap.appendChild(row);
                });
                thListEl.appendChild(wrap);

                // Click anywhere on a row to pick its group
                thListEl.querySelectorAll('.th-group-row').forEach((row) => {
                    row.addEventListener('click', function () {
                        if (this.getAttribute('aria-disabled') === 'true') return;
                        const idx = this.getAttribute('data-index');
                        const radio = this.querySelector('input[name="th_group_index_pick"]');
                        if (radio) radio.checked = true;
                        if (thErrEl) thErrEl.classList.add('d-none');
                        thListEl.querySelectorAll('.th-group-row').forEach((r) => r.classList.remove('border-primary', 'bg-light'));
                        this.classList.add('border-primary', 'bg-light');
                    });
                    row.addEventListener('keydown', function (ev) {
                        if (ev.key === 'Enter' || ev.key === ' ') {
                            ev.preventDefault();
                            this.click();
                        }
                    });
                });
            }
            thModal.show();
        }

        document.addEventListener('submit', function (e) {
            const form = e.target.closest('.form-register-offering');
            if (!form) return;

            const btn = form.querySelector('button[type="submit"]');
            if (btn && btn.disabled) return;

            const raw = form.getAttribute('data-th-groups') || '[]';
            let groups = [];
            try { groups = JSON.parse(raw) || []; } catch (_) { groups = []; }

            if (Array.isArray(groups) && groups.length > 0) {
                e.preventDefault();
                const offeringName = form.getAttribute('data-offering-name') || '';
                openThGroupModal(form, groups, offeringName);
            }
        });

        const btnConfirm = document.getElementById('btnConfirmThGroup');
        if (btnConfirm) {
            btnConfirm.addEventListener('click', function () {
                if (!pendingRegisterForm) return;
                const picked = thModalEl.querySelector('input[name="th_group_index_pick"]:checked');
                if (!picked || !picked.value) {
                    if (thErrEl) thErrEl.classList.remove('d-none');
                    return;
                }
                const hidden = pendingRegisterForm.querySelector('input[name="th_group_index"]');
                if (hidden) hidden.value = picked.value;
                thModal.hide();
                pendingRegisterForm.submit();
            });
        }
    })();
</script>
@endpush
@endsection

