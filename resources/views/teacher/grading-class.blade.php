@extends('layouts.teacher')

@section('title', 'Nhập điểm')
@section('page-title', '')

@section('content')
@php
    $saveUrl = route('teacher.grading.save', $courseOffering);
    $finalizeUrl = route('teacher.grading.finalize', $courseOffering);
    $backUrl = route('teacher.grading');
    $exportXlsxUrl = route('teacher.grading.export.xlsx', $courseOffering);
    $isFinalized = (bool) $courseOffering->grades_finalized_at;
    $canEdit = (optional($courseOffering->ngay_bat_dau_hoc)->startOfDay()?->lte(now()->startOfDay()) ?? false) && ! $isFinalized;
@endphp

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                        <div>
                            <h5 class="fw-bold mb-1">{{ $courseOffering->ten_hoc_phan }}</h5>
                            <div class="small text-muted">
                                @if($courseOffering->subject)
                                    <strong>Môn:</strong> {{ $courseOffering->subject->ma_mon_hoc }} — {{ $courseOffering->subject->ten_mon_hoc }}
                                @endif
                                @if($courseOffering->classRoom)
                                    &nbsp;·&nbsp; <strong>Phòng:</strong> {{ $courseOffering->classRoom->ma_lop }} — {{ $courseOffering->classRoom->ten_lop }}
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm text-nowrap">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                            <a href="{{ $exportXlsxUrl }}" class="btn btn-outline-success btn-sm text-nowrap">
                                <i class="fas fa-file-excel me-1"></i> Tải Excel
                            </a>
                            <button type="button" class="btn btn-warning btn-sm text-nowrap" id="btnFinalizeGrades" @disabled($isFinalized) title="{{ $isFinalized ? 'Đã chốt điểm' : '' }}">
                                <i class="fas fa-lock me-1"></i> Chốt điểm
                            </button>
                            <button type="button" class="btn btn-primary btn-sm text-nowrap" id="btnSaveGrades" @disabled(!$canEdit) title="{{ $canEdit ? '' : 'Lớp chưa bắt đầu học nên chưa thể lưu điểm' }}">
                                <i class="fas fa-save me-1"></i> Lưu điểm
                            </button>
                        </div>
                    </div>
                    <div id="gradeAlert" class="alert d-none mt-3 mb-0"></div>
                    @if(!$canEdit)
                        <div class="alert alert-warning mt-3 mb-0">
                            @if($isFinalized)
                                Học phần đã <strong>chốt điểm</strong> lúc <strong>{{ optional($courseOffering->grades_finalized_at)->format('d/m/Y H:i') }}</strong> nên không thể chỉnh sửa.
                            @else
                                Lớp <strong>chưa bắt đầu học</strong>, bạn vẫn xem được danh sách và nhập thử nhưng hệ thống sẽ <strong>không cho lưu</strong> cho đến khi bắt đầu học.
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 align-middle text-center" id="gradesTable">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2" style="min-width:260px" class="text-start ps-3">Tên học sinh</th>
                            <th rowspan="2" style="min-width:110px">MSSV</th>
                            <th rowspan="2" style="min-width:80px">Số TC</th>
                            <th rowspan="2" style="min-width:90px">Giữa kỳ</th>
                            <th colspan="4">Thường xuyên</th>
                            <th colspan="3">Thực hành</th>
                            <th rowspan="2" style="min-width:90px">Cuối kỳ</th>
                            <th rowspan="2" style="min-width:110px">Điểm tổng kết</th>
                            <th rowspan="2" style="min-width:110px">Thang điểm 4</th>
                            <th rowspan="2" style="min-width:90px">Điểm chữ</th>
                            <th rowspan="2" style="min-width:120px">Xếp loại</th>
                        </tr>
                        <tr>
                            @for($i=1;$i<=4;$i++)
                                <th style="min-width:60px">{{ $i }}</th>
                            @endfor
                            @for($i=1;$i<=3;$i++)
                                <th style="min-width:60px">{{ $i }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @foreach(($registrations ?? collect()) as $reg)
                            @php
                                $s = $reg->student;
                                $g = $grades[$s->id] ?? null;
                                $tx = is_array($g?->thuong_xuyen) ? $g->thuong_xuyen : [];
                                $th = is_array($g?->thuc_hanh) ? $g->thuc_hanh : [];
                            @endphp
                            <tr data-student-id="{{ $s->id }}">
                                <td class="text-start ps-3 fw-semibold">{{ $s->ho_ten ?? '—' }}</td>
                                <td>{{ $s->mssv ?? '—' }}</td>
                                <td>{{ $courseOffering->subject?->so_tin_chi ?? '' }}</td>
                                <td><input class="form-control form-control-sm grade-input" type="text" inputmode="decimal" value="{{ $g?->giua_ky }}" data-field="giua_ky" data-min="0" data-max="10" @disabled(!$canEdit)></td>
                                @for($i=1;$i<=4;$i++)
                                    <td><input class="form-control form-control-sm grade-input" type="text" inputmode="decimal" value="{{ $tx[$i] ?? '' }}" data-field="tx_{{ $i }}" data-min="0" data-max="10" @disabled(!$canEdit)></td>
                                @endfor
                                @for($i=1;$i<=3;$i++)
                                    <td><input class="form-control form-control-sm grade-input" type="text" inputmode="decimal" value="{{ $th[$i] ?? '' }}" data-field="th_{{ $i }}" data-min="0" data-max="10" @disabled(!$canEdit)></td>
                                @endfor
                                <td><input class="form-control form-control-sm grade-input" type="text" inputmode="decimal" value="{{ $g?->cuoi_ky }}" data-field="cuoi_ky" data-min="0" data-max="10" @disabled(!$canEdit)></td>
                                <td><input class="form-control form-control-sm grade-input" type="text" inputmode="decimal" value="{{ $g?->diem_tong_ket }}" data-field="diem_tong_ket" data-min="0" data-max="10" @disabled(!$canEdit)></td>
                                <td><input class="form-control form-control-sm grade-input" type="text" inputmode="decimal" value="{{ $g?->thang_diem_4 }}" data-field="thang_diem_4" data-min="0" data-max="4" @disabled(!$canEdit)></td>
                                <td><input class="form-control form-control-sm grade-input" type="text" maxlength="5" value="{{ $g?->diem_chu }}" data-field="diem_chu" @disabled(!$canEdit)></td>
                                <td><input class="form-control form-control-sm grade-input" type="text" maxlength="30" value="{{ $g?->xep_loai }}" data-field="xep_loai" @disabled(!$canEdit)></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #gradesTable .grade-low {
        background: #fff5f5 !important;
        border-color: #dc3545 !important;
        color: #842029;
        font-weight: 700;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const saveUrl = @json($saveUrl);
        const finalizeUrl = @json($finalizeUrl);
        const canEdit = @json($canEdit);
        const isFinalized = @json($isFinalized);
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const btn = document.getElementById('btnSaveGrades');
        const btnFinalize = document.getElementById('btnFinalizeGrades');
        const alertBox = document.getElementById('gradeAlert');
        const table = document.getElementById('gradesTable');

        function showAlert(type, text) {
            alertBox.className = 'alert mt-3 mb-0 alert-' + type;
            alertBox.textContent = text;
            alertBox.classList.remove('d-none');
        }

        function parseClipboardColumn(text) {
            const raw = (text || '').replace(/\r/g, '').trimEnd();
            if (!raw) return [];
            return raw
                .split('\n')
                .map(line => {
                    const cell = line.split('\t')[0];
                    return (cell ?? '').trim();
                })
                .filter(v => v !== '');
        }

        function getColumnInputs(field) {
            return Array.from(table.querySelectorAll('tbody tr[data-student-id] .grade-input'))
                .filter(inp => inp.getAttribute('data-field') === field);
        }

        function getRowIndexForInput(inputEl) {
            const tr = inputEl.closest('tr[data-student-id]');
            if (!tr) return -1;
            const rows = Array.from(table.querySelectorAll('tbody tr[data-student-id]'));
            return rows.indexOf(tr);
        }

        function applyColumnPaste(startInput, values) {
            const field = startInput.getAttribute('data-field');
            if (!field) return;
            const colInputs = getColumnInputs(field);
            const startIdx = getRowIndexForInput(startInput);
            if (startIdx < 0) return;

            const remaining = colInputs.length - startIdx;
            if (values.length > remaining) {
                showAlert('warning', `Bạn đang dán ${values.length} dòng nhưng từ vị trí hiện tại chỉ còn ${remaining} học sinh. Vui lòng kiểm tra lại.`);
                return;
            }
            if (values.length !== remaining) {
                const ok = window.confirm(
                    `Bạn đang dán ${values.length} dòng, trong khi từ vị trí hiện tại còn ${remaining} học sinh.\n\n` +
                    `OK = vẫn dán ${values.length} dòng đầu.\nCancel = hủy để kiểm tra lại danh sách.`
                );
                if (!ok) return;
            }

            for (let i = 0; i < values.length; i++) {
                const inp = colInputs[startIdx + i];
                if (!inp || inp.disabled) continue;
                inp.value = values[i];
                inp.dispatchEvent(new Event('input', { bubbles: true }));
                inp.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        function collectRows() {
            const rows = [];
            document.querySelectorAll('#gradesTable tbody tr[data-student-id]').forEach(tr => {
                const studentId = parseInt(tr.getAttribute('data-student-id'), 10);
                const row = {
                    student_id: studentId,
                    thuong_xuyen: {},
                    thuc_hanh: {},
                };
                tr.querySelectorAll('.grade-input').forEach(inp => {
                    const field = inp.getAttribute('data-field');
                    const val = inp.value === '' ? null : inp.value;
                    if (field.startsWith('tx_')) {
                        const k = parseInt(field.replace('tx_', ''), 10);
                        row.thuong_xuyen[k] = val;
                    } else if (field.startsWith('th_')) {
                        const k = parseInt(field.replace('th_', ''), 10);
                        row.thuc_hanh[k] = val;
                    } else {
                        row[field] = val;
                    }
                });
                rows.push(row);
            });
            return rows;
        }

        function toNumberOrNull(v) {
            if (v === null || v === undefined) return null;
            const s = String(v).trim();
            if (!s) return null;
            const n = Number(s.replace(',', '.'));
            return Number.isFinite(n) ? n : null;
        }

        function clampNumber(n, min, max) {
            if (n === null) return null;
            if (min !== null && n < min) return min;
            if (max !== null && n > max) return max;
            return n;
        }

        function normalizeInputValue(inp) {
            const minAttr = inp.getAttribute('data-min');
            const maxAttr = inp.getAttribute('data-max');
            const min = minAttr === null ? null : toNumberOrNull(minAttr);
            const max = maxAttr === null ? null : toNumberOrNull(maxAttr);
            const n = toNumberOrNull(inp.value);
            if (n === null) return;
            const clamped = clampNumber(n, min, max);
            if (clamped === null) return;
            const fixed = Number.isInteger(clamped) ? String(clamped) : String(Math.round(clamped * 100) / 100);
            inp.value = fixed.replace('.', ',');
        }

        function updateLowGradeHighlight(tr) {
            const inp = tr.querySelector('.grade-input[data-field="diem_tong_ket"]');
            if (!inp) return;
            const n = toNumberOrNull(inp.value);
            if (n !== null && n < 5) {
                inp.classList.add('grade-low');
            } else {
                inp.classList.remove('grade-low');
            }
        }

        function refreshLowGradeHighlights() {
            table.querySelectorAll('tbody tr[data-student-id]').forEach(tr => updateLowGradeHighlight(tr));
        }

        function avgNumbers(values) {
            const nums = values.map(toNumberOrNull).filter(n => n !== null);
            if (nums.length === 0) return null;
            return nums.reduce((a, b) => a + b, 0) / nums.length;
        }

        function recomputeRowTotal(tr) {
            const gk = toNumberOrNull(tr.querySelector('.grade-input[data-field="giua_ky"]')?.value);
            const ck = toNumberOrNull(tr.querySelector('.grade-input[data-field="cuoi_ky"]')?.value);
            const txVals = [];
            for (let i = 1; i <= 4; i++) {
                txVals.push(tr.querySelector(`.grade-input[data-field="tx_${i}"]`)?.value ?? null);
            }
            const thVals = [];
            for (let i = 1; i <= 3; i++) {
                thVals.push(tr.querySelector(`.grade-input[data-field="th_${i}"]`)?.value ?? null);
            }

            const txAvg = avgNumbers(txVals);
            const thAvg = avgNumbers(thVals);
            const out = tr.querySelector('.grade-input[data-field="diem_tong_ket"]');
            if (!out || out.disabled) return;

            if (txAvg === null || thAvg === null || gk === null || ck === null) {
                return;
            }

            const total = 0.2 * txAvg + 0.2 * gk + 0.2 * thAvg + 0.4 * ck;
            out.value = (Math.round(total * 100) / 100).toFixed(2);
            normalizeInputValue(out);
            out.dispatchEvent(new Event('input', { bubbles: true }));
            out.dispatchEvent(new Event('change', { bubbles: true }));
            updateLowGradeHighlight(tr);
        }

        async function saveGrades() {
            if (!canEdit) {
                showAlert('warning', 'Lớp chưa bắt đầu học nên chưa thể lưu điểm.');
                return;
            }
            alertBox.classList.add('d-none');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang lưu...';
            try {
                const res = await fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ rows: collectRows() })
                });
                const data = await res.json();
                if (res.ok) {
                    showAlert('success', data.message || 'Đã lưu điểm.');
                    refreshLowGradeHighlights();
                } else {
                    showAlert('danger', data.message || 'Không lưu được điểm.');
                }
            } catch (e) {
                showAlert('danger', 'Có lỗi xảy ra khi lưu.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i> Lưu điểm';
            }
        }

        async function finalizeGrades() {
            if (isFinalized) {
                showAlert('info', 'Học phần đã chốt điểm.');
                return;
            }
            const ok = window.confirm('Chốt điểm sẽ tính Điểm tổng kết/Thang 4/Điểm chữ/Xếp loại và khóa không cho chỉnh sửa nữa. Bạn chắc chắn muốn chốt?');
            if (!ok) return;

            alertBox.classList.add('d-none');
            btnFinalize.disabled = true;
            btnFinalize.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang chốt...';
            try {
                const res = await fetch(finalizeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                });
                const data = await res.json();
                if (res.ok) {
                    showAlert('success', data.message || 'Đã chốt điểm.');
                    window.location.reload();
                } else {
                    showAlert('danger', data.message || 'Không chốt được điểm.');
                }
            } catch (e) {
                showAlert('danger', 'Có lỗi xảy ra khi chốt điểm.');
            } finally {
                btnFinalize.disabled = false;
                btnFinalize.innerHTML = '<i class="fas fa-lock me-1"></i> Chốt điểm';
            }
        }

        table.addEventListener('paste', (e) => {
            const target = e.target;
            if (!canEdit) return;
            if (!(target instanceof HTMLInputElement)) return;
            if (!target.classList.contains('grade-input')) return;
            if (target.disabled) return;

            const clip = e.clipboardData?.getData('text/plain') ?? '';
            const values = parseClipboardColumn(clip);
            if (values.length <= 1) {
                return;
            }

            e.preventDefault();
            alertBox.classList.add('d-none');
            applyColumnPaste(target, values);
            const fieldInputs = [];
            const startTr = target.closest('tr[data-student-id]');
            if (startTr) {
                const allRows = Array.from(table.querySelectorAll('tbody tr[data-student-id]'));
                const startIdx = allRows.indexOf(startTr);
                const field = target.getAttribute('data-field') || '';
                if (field) {
                    const colInputs = getColumnInputs(field);
                    for (let i = 0; i < values.length; i++) {
                        const inp = colInputs[startIdx + i];
                        if (inp) fieldInputs.push(inp);
                    }
                }
            }
            fieldInputs.forEach(inp => normalizeInputValue(inp));

            const field = target.getAttribute('data-field') || '';
            if (field === 'giua_ky' || field === 'cuoi_ky' || field.startsWith('tx_') || field.startsWith('th_')) {
                const startTr = target.closest('tr[data-student-id]');
                if (startTr) {
                    const allRows = Array.from(table.querySelectorAll('tbody tr[data-student-id]'));
                    const startIdx = allRows.indexOf(startTr);
                    for (let i = 0; i < values.length; i++) {
                        const tr = allRows[startIdx + i];
                        if (!tr) break;
                        recomputeRowTotal(tr);
                    }
                }
            }
        });

        table.addEventListener('input', (e) => {
            const target = e.target;
            if (!canEdit) return;
            if (!(target instanceof HTMLInputElement)) return;
            if (!target.classList.contains('grade-input')) return;
            normalizeInputValue(target);
            const field = target.getAttribute('data-field') || '';
            if (!(field === 'giua_ky' || field === 'cuoi_ky' || field.startsWith('tx_') || field.startsWith('th_'))) return;
            const tr = target.closest('tr[data-student-id]');
            if (!tr) return;
            recomputeRowTotal(tr);
        });

        btn.addEventListener('click', saveGrades);
        btnFinalize.addEventListener('click', finalizeGrades);

        refreshLowGradeHighlights();
    })();
</script>
@endpush

