@extends('layouts.teacher')

@section('title', 'Nhập điểm')
@section('page-title', 'Nhập điểm')

@section('content')
@php
    $saveUrl = route('teacher.grading.save', $courseOffering);
    $backUrl = route('teacher.grading');
    $canEdit = optional($courseOffering->ngay_bat_dau_hoc)->startOfDay()?->lte(now()->startOfDay()) ?? false;
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
                            <button type="button" class="btn btn-primary btn-sm text-nowrap" id="btnSaveGrades" @disabled(!$canEdit) title="{{ $canEdit ? '' : 'Lớp chưa bắt đầu học nên chưa thể lưu điểm' }}">
                                <i class="fas fa-save me-1"></i> Lưu điểm
                            </button>
                        </div>
                    </div>
                    <div id="gradeAlert" class="alert d-none mt-3 mb-0"></div>
                    @if(!$canEdit)
                        <div class="alert alert-warning mt-3 mb-0">
                            Lớp <strong>chưa bắt đầu học</strong>, bạn vẫn xem được danh sách và nhập thử nhưng hệ thống sẽ <strong>không cho lưu</strong> cho đến khi bắt đầu học.
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
                            <th colspan="5">Thường xuyên</th>
                            <th colspan="5">Thực hành</th>
                            <th rowspan="2" style="min-width:90px">Cuối kỳ</th>
                            <th rowspan="2" style="min-width:110px">Điểm tổng kết</th>
                            <th rowspan="2" style="min-width:110px">Thang điểm 4</th>
                            <th rowspan="2" style="min-width:90px">Điểm chữ</th>
                            <th rowspan="2" style="min-width:120px">Xếp loại</th>
                        </tr>
                        <tr>
                            @for($i=1;$i<=5;$i++)
                                <th style="min-width:60px">{{ $i }}</th>
                            @endfor
                            @for($i=1;$i<=5;$i++)
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
                                <td><input class="form-control form-control-sm grade-input" type="number" step="0.01" min="0" max="10" value="{{ $g?->giua_ky }}" data-field="giua_ky" @disabled(!$canEdit)></td>
                                @for($i=1;$i<=5;$i++)
                                    <td><input class="form-control form-control-sm grade-input" type="number" step="0.01" min="0" max="10" value="{{ $tx[$i] ?? '' }}" data-field="tx_{{ $i }}" @disabled(!$canEdit)></td>
                                @endfor
                                @for($i=1;$i<=5;$i++)
                                    <td><input class="form-control form-control-sm grade-input" type="number" step="0.01" min="0" max="10" value="{{ $th[$i] ?? '' }}" data-field="th_{{ $i }}" @disabled(!$canEdit)></td>
                                @endfor
                                <td><input class="form-control form-control-sm grade-input" type="number" step="0.01" min="0" max="10" value="{{ $g?->cuoi_ky }}" data-field="cuoi_ky" @disabled(!$canEdit)></td>
                                <td><input class="form-control form-control-sm grade-input" type="number" step="0.01" min="0" max="10" value="{{ $g?->diem_tong_ket }}" data-field="diem_tong_ket" @disabled(!$canEdit)></td>
                                <td><input class="form-control form-control-sm grade-input" type="number" step="0.01" min="0" max="4" value="{{ $g?->thang_diem_4 }}" data-field="thang_diem_4" @disabled(!$canEdit)></td>
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

@push('scripts')
<script>
    (function () {
        const saveUrl = @json($saveUrl);
        const canEdit = @json($canEdit);
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const btn = document.getElementById('btnSaveGrades');
        const alertBox = document.getElementById('gradeAlert');

        function showAlert(type, text) {
            alertBox.className = 'alert mt-3 mb-0 alert-' + type;
            alertBox.textContent = text;
            alertBox.classList.remove('d-none');
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

        btn.addEventListener('click', saveGrades);
    })();
</script>
@endpush

