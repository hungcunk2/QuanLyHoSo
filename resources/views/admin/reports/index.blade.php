@extends('layouts.admin')

@section('title', 'Báo cáo / Thống kê')
@section('page-title', 'Báo cáo / Thống kê')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap gap-2 align-items-end mb-3">
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-end">
            <div>
                <label class="form-label mb-1">Khóa học</label>
                <select class="form-select form-control" name="khoa_hoc">
                    <option value="">Tất cả</option>
                    @foreach(($khoaHocOptions ?? collect()) as $opt)
                        <option value="{{ $opt }}" @selected((string) ($khoaHoc ?? '') === (string) $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label mb-1">Lớp</label>
                <select class="form-select form-control" name="lop">
                    <option value="">Tất cả</option>
                    @foreach(($lopOptions ?? collect()) as $opt)
                        <option value="{{ $opt }}" @selected((string) ($lop ?? '') === (string) $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pb-1">
                <button class="btn btn-primary">Lọc</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.reports.index') }}">Xóa lọc</a>
            </div>
        </form>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Tổng sinh viên</div>
                            <div class="fs-2 fw-semibold">{{ number_format((int) ($totalStudents ?? 0)) }}</div>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:#eef2ff;color:#4f46e5;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-muted small mb-1">Độ đầy đủ hồ sơ (tối thiểu)</div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:10px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ (int) ($completenessPct ?? 0) }}%; background:#22c55e;"></div>
                            </div>
                            <div class="fw-semibold">{{ (int) ($completenessPct ?? 0) }}%</div>
                        </div>
                        <div class="text-muted small mt-1">Tiêu chí: MSSV, Họ tên, Lớp, Khóa học</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Lớp học phần (không hủy)</div>
                            <div class="fs-2 fw-semibold">{{ number_format((int) ($totalOfferings ?? 0)) }}</div>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:#ecfeff;color:#0891b2;">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-muted small mb-1">Đã chốt điểm</div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:10px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ (int) ($finalizedPct ?? 0) }}%; background:#0ea5e9;"></div>
                            </div>
                            <div class="fw-semibold">{{ (int) ($finalizedPct ?? 0) }}%</div>
                        </div>
                        <div class="text-muted small mt-1">{{ number_format((int) ($finalizedOfferings ?? 0)) }} / {{ number_format((int) ($totalOfferings ?? 0)) }} lớp</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Cảnh báo dữ liệu</div>
                            <div class="fs-2 fw-semibold">{{ array_sum(array_map('intval', $missingCounts ?? [])) }}</div>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:#fff7ed;color:#ea580c;">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                    </div>
                    <div class="text-muted small mt-2">Tổng số trường thiếu (cộng dồn theo hồ sơ)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-semibold">Hồ sơ thiếu dữ liệu</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                            <tr>
                                <th>Trường</th>
                                <th class="text-end">Số hồ sơ thiếu</th>
                                <th class="text-end">Tỷ lệ</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach(($missingFields ?? []) as $field => $label)
                                @php
                                    $cnt = (int) (($missingCounts[$field] ?? 0));
                                    $pct = ($totalStudents ?? 0) ? (int) round($cnt * 100 / (int) $totalStudents) : 0;
                                @endphp
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($cnt) }}</td>
                                    <td class="text-end text-muted">{{ $pct }}%</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-muted small">Gợi ý: ưu tiên bắt buộc CCCD / SĐT / Email / Mã hồ sơ.</div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-semibold">Trùng dữ liệu (Top 10)</div>
                        <div class="text-muted small">Xuất CSV</div>
                    </div>

                    <div class="accordion" id="dupAccordion">
                        @php
                            $dupTitles = ['mssv' => 'MSSV', 'so_cccd' => 'CCCD', 'email' => 'Email', 'ma_ho_so' => 'Mã hồ sơ'];
                        @endphp
                        @foreach($dupTitles as $field => $label)
                            @php
                                $rows = $duplicates[$field] ?? collect();
                                $collapseId = 'dup_'.$field;
                            @endphp
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="h_{{ $collapseId }}">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}">
                                        {{ $label }} ({{ $rows->count() }})
                                    </button>
                                </h2>
                                <div id="{{ $collapseId }}" class="accordion-collapse collapse" data-bs-parent="#dupAccordion">
                                    <div class="accordion-body">
                                        <div class="d-flex justify-content-end mb-2">
                                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.reports.duplicates.csv', ['field' => $field]) }}">Tải CSV</a>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle">
                                                <thead>
                                                <tr>
                                                    <th>{{ $label }}</th>
                                                    <th class="text-end">Số bản ghi</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @forelse($rows as $r)
                                                    <tr>
                                                        <td class="text-break">{{ $r->{$field} }}</td>
                                                        <td class="text-end fw-semibold">{{ (int) $r->cnt }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="2" class="text-muted">Không có</td></tr>
                                                @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">Lớp học phần theo học kỳ (gần đây)</div>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                            <tr>
                                <th>Học kỳ</th>
                                <th>Khóa học</th>
                                <th class="text-end">Số lớp</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse(($offeringsByDot ?? collect()) as $r)
                                <tr>
                                    <td>{{ $r->hoc_ky ?? '—' }}</td>
                                    <td>{{ $r->khoa_hoc ?? '—' }}</td>
                                    <td class="text-end fw-semibold">{{ number_format((int) ($r->cnt ?? 0)) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">Chưa có dữ liệu</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">Tiến độ chốt điểm theo học kỳ (gần đây)</div>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                            <tr>
                                <th>Học kỳ</th>
                                <th>Khóa học</th>
                                <th class="text-end">Đã chốt</th>
                                <th class="text-end">Tổng</th>
                                <th class="text-end">%</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse(($finalizedByDot ?? collect()) as $r)
                                <tr>
                                    <td>{{ $r->hoc_ky ?? '—' }}</td>
                                    <td>{{ $r->khoa_hoc ?? '—' }}</td>
                                    <td class="text-end fw-semibold">{{ number_format((int) ($r->finalized ?? 0)) }}</td>
                                    <td class="text-end">{{ number_format((int) ($r->total ?? 0)) }}</td>
                                    <td class="text-end">
                                        <span class="badge" style="background: rgba(14,165,233,.12); color:#0369a1;">
                                            {{ (int) ($r->pct ?? 0) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted">Chưa có dữ liệu</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

