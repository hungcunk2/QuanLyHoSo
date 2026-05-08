@extends('layouts.student')

@section('title', 'Bảng điều khiển')
@section('page-title', '')

@section('content')
@php
    $s = $student ?? null;
    $emptyLabel = 'Chưa cập nhật';
    $v = function($key, $default = null) use ($s, $user, $emptyLabel) {
        $default = $default ?? $emptyLabel;
        if (!$s) {
            return $key === 'email' ? ($user->email ?? $emptyLabel) : $default;
        }
        $map = [
            'mssv' => $s->mssv,
            'ho_ten' => $s->ho_ten,
            'gioi_tinh' => $s->gioi_tinh ?? '',
            'trang_thai' => $s->trang_thai ?? '',
            'ma_ho_so' => $s->ma_ho_so ?? '',
            'ngay_vao_truong' => $s->ngay_vao_truong ? \Carbon\Carbon::parse($s->ngay_vao_truong)->format('d/m/Y') : '',
            'lop' => $s->lop,
            'co_so' => $s->co_so ?? '',
            'bac_dao_tao' => $s->bac_dao_tao ?? '',
            'loai_hinh_dao_tao' => $s->loai_hinh_dao_tao ?? '',
            'khoa' => $s->khoa ?? '',
            'nganh' => $s->nganh ?? '',
            'chuyen_nganh' => $s->chuyen_nganh ?? '',
            'khoa_hoc' => $s->khoa_hoc ?? '',
            'ngay_sinh' => $s->ngay_sinh ? $s->ngay_sinh->format('d/m/Y') : '',
            'dan_toc' => $s->dan_toc ?? '',
            'ton_giao' => $s->ton_giao ?? '',
            'quoc_tich' => $s->quoc_tich ?? '',
            'khu_vuc' => $s->khu_vuc ?? '',
            'so_cccd' => $s->so_cccd ?? '',
            'ngay_cap_cccd' => isset($s->ngay_cap_cccd) ? \Carbon\Carbon::parse($s->ngay_cap_cccd)->format('d/m/Y') : '',
            'noi_cap_cccd' => $s->noi_cap_cccd ?? '',
            'doi_tuong' => $s->doi_tuong ?? '',
            'dien_chinh_sach' => $s->dien_chinh_sach ?? '',
            'ngay_vao_doan' => $s->ngay_vao_doan ?? '',
            'ngay_vao_dang' => $s->ngay_vao_dang ?? '',
            'so_dien_thoai' => $s->so_dien_thoai,
            'email' => $s->email ?? $user->email ?? '',
            'dia_chi_lien_he' => $s->dia_chi_lien_he ?? '',
            'noi_sinh' => $s->noi_sinh ?? '',
            'ho_khau_thuong_tru' => $s->ho_khau_thuong_tru ?? $s->dia_chi ?? '',
            'ho_ten_cha' => $s->ho_ten_cha,
            'nam_sinh_cha' => $s->nam_sinh_cha ?? '',
            'nghe_nghiep_cha' => $s->nghe_nghiep_cha ?? '',
            'quoc_tich_cha' => $s->quoc_tich_cha ?? '',
            'dan_toc_cha' => $s->dan_toc_cha ?? '',
            'ton_giao_cha' => $s->ton_giao_cha ?? '',
            'co_quan_cha' => $s->co_quan_cha ?? '',
            'chuc_vu_cha' => $s->chuc_vu_cha ?? '',
            'sdt_cha' => $s->sdt_cha,
            'ho_ten_me' => $s->ho_ten_me,
            'sdt_me' => $s->sdt_me,
        ];
        $raw = $map[$key] ?? '';
        return (is_string($raw) && trim((string)$raw) !== '') ? $raw : $default;
    };
@endphp

<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('message'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row g-3 mb-3">
        <div class="col-md-7">
            <div class="box-df profile-ds-info h-100">
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject bold">Thông tin sinh viên</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row g-3">
                            <div class="col-sm-3">
                                <div class="profile-userpic">
                                    @if($student && $student->avatar)
                                        <img src="{{ asset('storage/' . $student->avatar) }}" alt="Ảnh đại diện" class="img-fluid rounded-circle" style="width: 130px; height: 130px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle overflow-hidden bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 130px; height: 130px;">
                                            <i class="fas fa-user-graduate fa-2x"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-center mt-2">
                                    <a href="{{ route('student.profile') }}" class="color-active">Xem chi tiết</a>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="row g-2 student-info-grid">
                                    <div class="col-6"><span class="text-muted">MSSV:</span> <span class="fw-bold">{{ $v('mssv') }}</span></div>
                                    <div class="col-6"><span class="text-muted">Lớp học:</span> <span class="fw-bold">{{ $lopTen ?: $v('lop') }}</span></div>
                                    <div class="col-6"><span class="text-muted">Họ tên:</span> <span class="fw-bold">{{ $v('ho_ten') }}</span></div>
                                    <div class="col-6"><span class="text-muted">Khóa học:</span> <span class="fw-bold">{{ $v('khoa_hoc') }}</span></div>
                                    <div class="col-6"><span class="text-muted">Giới tính:</span> <span class="fw-bold">{{ $v('gioi_tinh') }}</span></div>
                                    <div class="col-6"><span class="text-muted">Bậc đào tạo:</span> <span class="fw-bold">{{ $v('bac_dao_tao') }}</span></div>
                                    <div class="col-6"><span class="text-muted">Ngày sinh:</span> <span class="fw-bold">{{ $v('ngay_sinh') }}</span></div>
                                    <div class="col-6"><span class="text-muted">Ngành:</span> <span class="fw-bold">{{ $v('nganh') }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="portlet h-100 menu-right-portlet">
                <div class="portlet-body menu-right-body">
                    <div class="box-menu row g-3">
                        <div class="col-12">
                            <div class="item-box-menu box-df menu-card menu-card--reminder nhacnho-border">
                                <div class="menu-card__row">
                                    <div class="menu-card__main">
                                        <h3 class="name nhacnho-custom mb-0">Nhắc nhở mới, chưa xem</h3>
                                        <div class="menu-card__number nhacnho-custom">{{ (int) ($remindersCount ?? 0) }}</div>
                                        <div class="text-start">
                                            <a href="{{ route('student.notifications') }}" class="color-active">Xem chi tiết</a>
                                        </div>
                                    </div>
                                    <div class="menu-card__icon">
                                        <div class="menu-card__icon-circle menu-card__icon-circle--reminder">
                                            <i class="fa fa-bell-o" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('student.schedule') }}" class="color-active text-decoration-none" title="">
                                <div class="item-box-menu box-df menu-card menu-card--class lichhoc-custom">
                                    <div class="menu-card__row">
                                        <div class="menu-card__main">
                                            <h3 class="name mb-0">Lịch học trong tuần</h3>
                                            <div class="menu-card__number">{{ (int) ($weekClassCount ?? 0) }}</div>
                                            <div class="text-start">Xem chi tiết</div>
                                        </div>
                                        <div class="menu-card__icon">
                                            <div class="menu-card__icon-circle menu-card__icon-circle--class">
                                                <i class="fa fa-calendar" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('student.schedule', ['type' => 'exam']) }}" class="color-active text-decoration-none" title="">
                                <div class="item-box-menu box-df menu-card menu-card--exam lichthi-custom">
                                    <div class="menu-card__row">
                                        <div class="menu-card__main">
                                            <h3 class="name mb-0">Lịch thi trong tuần</h3>
                                            <div class="menu-card__number">{{ (int) ($weekExamCount ?? 0) }}</div>
                                            <div class="text-start">Xem chi tiết</div>
                                        </div>
                                        <div class="menu-card__icon">
                                            <div class="menu-card__icon-circle menu-card__icon-circle--exam">
                                                <i class="fa fa-calendar-check-o" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="featured mb-3">
        @php
            $shortcuts = [
                ['label' => 'Lịch theo tuần', 'icon' => 'fa-calendar', 'url' => route('student.schedule')],
                ['label' => 'Lịch thi trong tuần', 'icon' => 'fa-calendar-check', 'url' => route('student.schedule', ['type' => 'exam'])],
                ['label' => 'Kết quả học tập', 'icon' => 'fa-chart-column', 'url' => route('student.results')],
                ['label' => 'Đăng ký học phần', 'icon' => 'fa-layer-group', 'url' => route('student.registration')],
                ['label' => 'Thông báo', 'icon' => 'fa-bell', 'url' => route('student.notifications')],
            ];
        @endphp
        @foreach($shortcuts as $sc)
            <div class="featured-item">
                <a href="{{ $sc['url'] }}" class="text-decoration-none">
                    <div class="box-df auto-height">
                        <div class="icon">
                            <i class="fas {{ $sc['icon'] }}"></i>
                        </div>
                        <span>{{ $sc['label'] }}</span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    @php
        $programTc = (int) ($programTotalCredits ?? 167);
        if ($programTc < 1) {
            $programTc = 167;
        }
        $passedTc = (int) ($passedCredits ?? 0);
        $registeredTc = (int) ($totalCredits ?? 0);
        $innerPct = min(100, (int) round($passedTc * 100 / $programTc));
        $outerPct = min(100, (int) round($registeredTc * 100 / $programTc));

        $outerR = 44;
        $innerR = 32;
        $pi = 3.1415926;
        $outerC = 2 * $pi * $outerR;
        $innerC = 2 * $pi * $innerR;
        $outerOffset = $outerC - ($outerC * ($outerPct / 100));
        $innerOffset = $innerC - ($innerC * ($innerPct / 100));
    @endphp

    @php
        $dotOptions = ($hocKyOptions ?? collect())->map(function ($x) {
            $kh = (string) ($x['khoa_hoc'] ?? '');
            $hk = (string) ($x['hoc_ky'] ?? '');
            $label = trim(($hk !== '' ? ('HK'.$hk) : 'HK') . ($kh !== '' ? (' ('.$kh.')') : ''));
            $value = $hk . '|' . $kh;
            return ['khoa_hoc' => $kh, 'hoc_ky' => $hk, 'label' => $label, 'value' => $value];
        })->values();
    @endphp
    <div class="row chart-custom g-3">
        <div class="col-md-4">
            <div class="box-df">
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject bold"><a href="{{ route('student.results') }}" class="text-decoration-none">Kết quả học tập</a></span>
                        </div>
                        <div class="actions">
                            <form method="GET">
                                <input type="hidden" name="dot_lhp" value="{{ $selectedDotLhp ?? '' }}">
                                <select class="form-select form-control form-select-sm" name="dot_kq" onchange="this.form.submit()">
                                    <option value="">Chọn học kỳ</option>
                                    @foreach($dotOptions as $opt)
                                        <option value="{{ $opt['value'] }}" @selected((string) ($selectedDotKq ?? '') === (string) $opt['value'])>{{ $opt['label'] }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                    @php
                        $kqLabels = $resultsChartLabels ?? [];
                        $kqValues = $resultsChartValues ?? [];
                        $hasKq = is_array($kqLabels) && is_array($kqValues) && count($kqLabels) > 0 && count($kqValues) > 0;
                    @endphp
                    <div class="portlet-body results-bg {{ $hasKq ? 'results-bg--has-data' : '' }}">
                        @if($hasKq)
                            <div class="results-bg__content">
                                <canvas id="resultsChart"></canvas>
                            </div>
                        @else
                            <div class="results-bg__content text-center text-muted small">Chưa có dữ liệu hiển thị</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="box-df">
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject bold">Tiến độ học tập</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div id="study-progress-chart" class="position-relative d-flex flex-column align-items-center justify-content-center study-progress-chart-inner">
                            <div id="pd-tooltip" class="pd-tooltip" role="tooltip">Số tín chỉ tích lũy: {{ $passedTc }}</div>
                            <svg id="study-progress-svg" width="270" height="270" viewBox="0 0 120 120" role="img" aria-label="Tiến độ học tập">
                                <circle cx="60" cy="60" r="{{ $outerR }}" stroke="#e9ecef" stroke-width="11" fill="none"></circle>
                                <circle id="pd-outer-stroke" cx="60" cy="60" r="{{ $outerR }}" stroke="#2caffe" stroke-width="11" fill="none"
                                        stroke-linecap="round"
                                        stroke-dasharray="{{ $outerC }}"
                                        stroke-dashoffset="{{ $outerOffset }}"
                                        transform="rotate(-90 60 60)">
                                    <title>Đăng ký: {{ $registeredTc }}/{{ $programTc }} tín chỉ ({{ $outerPct }}%)</title>
                                </circle>

                                <circle cx="60" cy="60" r="{{ $innerR }}" stroke="#e9ecef" stroke-width="11" fill="none"></circle>
                                <circle id="pd-inner-stroke" cx="60" cy="60" r="{{ $innerR }}" stroke="#22c55e" stroke-width="11" fill="none"
                                        stroke-linecap="round"
                                        stroke-dasharray="{{ $innerC }}"
                                        stroke-dashoffset="{{ $innerOffset }}"
                                        transform="rotate(-90 60 60)">
                                    <title>Số tín chỉ tích lũy: {{ $passedTc }}</title>
                                </circle>

                            </svg>
                            <div class="study-progress-center" aria-hidden="true">
                                <div class="study-progress-center__label">Đã học: {{ $passedTc }} tín chỉ</div>
                                <div class="study-progress-center__pct">{{ $innerPct }}%</div>
                            </div>
                            <p class="study-progress-ratio mb-0 mt-1 text-center fw-bold" style="color:#003f65;">{{ $passedTc }}/{{ $programTc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box-df">
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject bold">Lớp học phần</span>
                        </div>
                        <div class="actions">
                            <form method="GET">
                                <input type="hidden" name="dot_kq" value="{{ $selectedDotKq ?? '' }}">
                                <select class="form-select form-control form-select-sm" name="dot_lhp" onchange="this.form.submit()">
                                    <option value="">Chọn học kỳ</option>
                                    @foreach($dotOptions as $opt)
                                        <option value="{{ $opt['value'] }}" @selected((string) ($selectedDotLhp ?? '') === (string) $opt['value'])>{{ $opt['label'] }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="panel panel-admin">
                            <div class="panel-heading clearfix d-flex justify-content-between">
                                <span>Môn học/học phần</span>
                                <span class="text-center">Số tín chỉ</span>
                            </div>
                            <div class="panel-scroll border-scroll">
                                <table class="table table-striped mb-0">
                                    <tbody>
                                        @forelse(($offeringsLhp ?? $offerings ?? collect()) as $o)
                                            <tr>
                                                <td width="80%">
                                                    <div>
                                                        <a href="#" class="color-active">{{ $o->subject?->ma_mon_hoc ?? '' }}</a>
                                                    </div>
                                                    <div class="name">{{ $o->subject?->ten_mon_hoc ?? ($o->ten_hoc_phan ?? '—') }}</div>
                                                </td>
                                                <td width="20%">
                                                    <div class="text-center">{{ $o->subject?->so_tin_chi ?? '' }}</div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center text-muted p-3">Chưa có dữ liệu</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .box-df { background:#fff; border-radius:8px; border:1px solid #e9ecef; }
    .portlet { border: 0; }
    .portlet-title { padding: 12px 14px; border-bottom: 1px solid #eef1f5; display:flex; align-items:center; justify-content: space-between; gap: 12px; }
    .chart-custom > [class^="col-"] { display: flex; }
    .chart-custom .box-df { flex: 1; }
    .chart-custom .portlet { height: 100%; display: flex; flex-direction: column; }
    .chart-custom .portlet-body { padding: 14px; flex: 1; }
    /* tách 3 ô (KQHT / Tiến độ / LHP) rõ hơn */
    .chart-custom .box-df {
        border-color: #dfe6ee;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }
    .caption-subject.bold { font-weight: 700; }
    .actions .form-control { min-width: 180px; }
    .color-active { color: #0d6efd; }
    .profile-userpic { display:flex; justify-content:center; }
    .student-info-grid { font-size: 14px; line-height: 1.35; }
    @media (min-width: 1200px) {
        .student-info-grid { font-size: 15px; }
    }
    .box-menu .item-box-menu { padding: 12px 14px; }
    .box-menu .name { font-size: 13px; font-weight: 700; margin-bottom: 8px; }
    .box-menu .number { font-size: 36px; font-weight: 800; line-height: 1; color:#003f65; }
    .box-menu .icon-menu .icon { font-size: 28px; color:#6c757d; }
    .nhacnho-border { border-left: 4px solid #eb2e51; }
    .nhacnho-custom { color:#667580; }
    .nhacnho-border .desc .number { flex: 1; }
    .nhacnho-border .icon-menu .icon { color:#667580; }
    .nhacnho-border .text-start,
    .nhacnho-border a.color-active { color:#667580; }
    .lichhoc-custom .name,
    .lichhoc-custom .number,
    .lichhoc-custom .icon-menu .icon,
    .lichhoc-custom .text-start { color:#4DA1E8; }
    .lichthi-custom .name,
    .lichthi-custom .number,
    .lichthi-custom .icon-menu .icon,
    .lichthi-custom .text-start { color:#FF9205; }

    /* IUH-like menu cards (3 ô bên phải) */
    .menu-card { border: 1px solid #e9ecef; border-radius: 8px; }
    .menu-card__row { display:flex; align-items:center; justify-content:space-between; gap: 12px; }
    .menu-card__main { flex: 1; min-width: 0; }
    .menu-card__number {
        font-size: 36px;
        font-weight: 600;
        line-height: 1;
        margin: 6px 0 6px;
        letter-spacing: 0.01em;
        font-variant-numeric: tabular-nums;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;
    }
    .menu-card__icon { display:flex; align-items:center; justify-content:center; }
    .menu-card__icon-circle { width: 40px; height: 40px; border-radius: 999px; display:flex; align-items:center; justify-content:center; }
    .menu-card__icon-circle i { font-size: 18px; }

    .menu-card--class { background: #E0FBFF; border-color: rgba(77, 161, 232, 0.18); }
    .menu-card__icon-circle--class { background: rgba(77, 161, 232, 0.18); color:#4DA1E8; }
    .menu-card--exam { background: #FFF2D4; border-color: rgba(255, 146, 5, 0.22); }
    .menu-card__icon-circle--exam { background: rgba(255, 146, 5, 0.20); color:#FF9205; }

    .menu-card--reminder { background: #ffffff; }
    .menu-card__icon-circle--reminder { background: #f1f5f9; color:#667580; }

    /* bỏ nền trắng phía sau các ô (gutter giống IUH) */
    .menu-right-portlet,
    .menu-right-body {
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    .results-bg { position: relative; overflow: hidden; min-height: 280px; }
    .results-bg:not(.results-bg--has-data)::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: url("{{ asset('images/results-bg.png') }}");
        background-size: cover;
        background-position: center;
        opacity: 0.35;
        filter: blur(2px);
        transform: scale(1.05);
    }
    .results-bg:not(.results-bg--has-data)::after {
        content: "";
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,0.35);
    }
    .results-bg__content {
        position: relative;
        z-index: 1;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .results-bg__content:has(#resultsChart) { padding: 6px 6px 0; }
    #resultsChart { width: 100% !important; height: 100% !important; }

    .featured {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
    }
    @media (max-width: 992px) {
        .featured { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 576px) {
        .featured { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    .featured-item .box-df {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 14px 10px;
        min-height: 92px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
    }
    .featured-item .box-df:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(0,0,0,.06);
        border-color: #cfe2ff;
    }
    .featured-item .icon {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0d6efd;
        font-size: 18px;
        margin-bottom: 8px;
    }
    .featured-item span {
        font-size: 12px;
        color: #6c757d;
        line-height: 1.2;
        max-width: 100%;
        display: block;
        padding: 0 6px;
    }

    .panel-admin { border: 1px solid #eef1f5; border-radius: 8px; overflow: hidden; }
    .panel-admin .panel-heading { background: #f8f9fa; padding: 10px 12px; font-weight: 700; }
    .panel-admin .name { color:#6c757d; font-size: 12px; }
    .border-scroll { max-height: 280px; overflow: auto; }

    .study-progress-chart-inner { padding-top: 16px; padding-bottom: 12px; min-height: 320px; }

    /* Match IUH-looking numerals (smooth + tabular) */
    #study-progress-chart {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        font-variant-numeric: tabular-nums;
    }

    .study-progress-center {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        pointer-events: none;
        z-index: 2;
        transform: translateY(-6px);
    }
    .study-progress-center__label {
        font-size: 15px;
        font-weight: 600;
        color: #003f65;
        line-height: 1.15;
    }
    .study-progress-center__pct {
        margin-top: 2px;
        font-size: 34px;
        font-weight: 600;
        color: #22c55e;
        letter-spacing: 0.01em;
        line-height: 1;
    }

    .study-progress-ratio {
        font-size: 16px;
        letter-spacing: 0.02em;
        font-variant-numeric: tabular-nums;
    }

    #study-progress-chart .pd-tooltip {
        position: absolute;
        left: 50%;
        top: 2px;
        transform: translateX(-50%);
        background: #fff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.12);
        padding: 7px 14px;
        font-size: 13px;
        color: #334155;
        border-radius: 6px;
        white-space: nowrap;
        z-index: 3;
        pointer-events: none;
        opacity: 0;
        visibility: hidden;
        transition: opacity .12s ease, visibility .12s ease;
    }
    #study-progress-chart.is-tip-visible .pd-tooltip {
        opacity: 1;
        visibility: visible;
    }

    #study-progress-svg { user-select: none; }
    #pd-outer-stroke,
    #pd-inner-stroke { transition: stroke .15s ease, filter .15s ease, stroke-width .15s ease; }
    #study-progress-svg.is-outer-hot #pd-outer-stroke {
        stroke: #1ab0ff;
        filter: drop-shadow(0 0 6px rgba(44, 175, 254, 0.65));
        stroke-width: 11;
    }
    #study-progress-svg.is-inner-hot #pd-inner-stroke {
        stroke: #15803d;
        filter: drop-shadow(0 0 5px rgba(34, 197, 94, 0.45));
        stroke-width: 11;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    var chart = document.getElementById('study-progress-chart');
    var svg = document.getElementById('study-progress-svg');
    if (!svg || !chart) return;

    function svgDist(ev) {
        var pt = svg.createSVGPoint();
        pt.x = ev.clientX;
        pt.y = ev.clientY;
        var m = svg.getScreenCTM();
        if (!m) return null;
        var p = pt.matrixTransform(m.inverse());
        return Math.hypot(p.x - 60, p.y - 60);
    }

    function zone(d) {
        if (d == null || d > 52) return 'out';
        if (d < 22) return 'center';
        if (d <= 37) return 'inner';
        if (d <= 50) return 'outer';
        return 'out';
    }

    svg.addEventListener('mousemove', function (ev) {
        var d = svgDist(ev);
        var z = zone(d);
        svg.classList.remove('is-outer-hot', 'is-inner-hot');
        chart.classList.remove('is-tip-visible');
        if (z === 'inner') {
            svg.classList.add('is-inner-hot');
            chart.classList.add('is-tip-visible');
            svg.style.cursor = 'pointer';
        } else if (z === 'outer') {
            svg.classList.add('is-outer-hot');
            chart.classList.add('is-tip-visible');
            svg.style.cursor = 'pointer';
        } else {
            svg.style.cursor = 'default';
        }
    });
    svg.addEventListener('mouseleave', function () {
        svg.classList.remove('is-outer-hot', 'is-inner-hot');
        chart.classList.remove('is-tip-visible');
        svg.style.cursor = 'default';
    });
})();
</script>

@php
    $kqLabels = $resultsChartLabels ?? [];
    $kqValues = $resultsChartValues ?? [];
    $kqAvgs = $resultsChartAverages ?? [];
    $hasKq = is_array($kqLabels) && is_array($kqValues) && count($kqLabels) > 0 && count($kqValues) > 0;
@endphp
@if($hasKq)
<script>
(function () {
    var el = document.getElementById('resultsChart');
    if (!el || !window.Chart) return;

    var labels = @json(array_values($kqLabels));
    var values = @json(array_values($kqValues));
    var avgs = @json(array_values($kqAvgs));

    // giới hạn cho đẹp như IUH (đỡ rối)
    var maxItems = 8;
    if (labels.length > maxItems) {
        labels = labels.slice(0, maxItems);
        values = values.slice(0, maxItems);
        avgs = avgs.slice(0, maxItems);
    }

    var ctx = el.getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                type: 'bar',
                label: 'Điểm của bạn:',
                data: values,
                yAxisID: 'y1',
                order: 2,
                backgroundColor: '#fa6c51',
                borderColor: '#fa6c51',
                borderWidth: 1,
                borderRadius: 6,
                maxBarThickness: 54
            }, {
                type: 'line',
                label: 'Điểm TB lớp học phần:',
                data: avgs,
                yAxisID: 'y',
                order: 1,
                borderColor: '#fdcd56',
                backgroundColor: 'transparent',
                borderWidth: 2,
                pointBackgroundColor: '#fdcd56',
                pointBorderColor: '#fdcd56',
                pointRadius: 4,
                pointHoverRadius: 5,
                spanGaps: true,
                tension: 0.35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 10,
                        boxHeight: 10,
                        color: '#111'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function (c) {
                            return (c.dataset.label || '') + ' ' + (c.raw ?? '');
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#475569',
                        maxRotation: 0,
                        autoSkip: false,
                        callback: function (v) {
                            var s = (this.getLabelForValue(v) || '');
                            // wrap giống IUH
                            if (s.length <= 22) return s;
                            var out = [];
                            var w = 0;
                            var parts = s.split(/\s+/);
                            var line = '';
                            for (var i = 0; i < parts.length; i++) {
                                var next = (line ? (line + ' ' + parts[i]) : parts[i]);
                                if (next.length > 22 && line) {
                                    out.push(line);
                                    line = parts[i];
                                    w++;
                                    if (w >= 2) break;
                                } else {
                                    line = next;
                                }
                            }
                            if (line) out.push(line);
                            if (out.join(' ').length < s.length) {
                                out[out.length - 1] = out[out.length - 1].slice(0, 22) + '…';
                            }
                            return out;
                        }
                    }
                },
                y: {
                    position: 'left',
                    min: 0,
                    max: 10,
                    ticks: { stepSize: 2, color: '#64748b' },
                    title: { display: true, text: 'Điểm TB lớp học phần', color: '#544fc5' },
                    grid: { color: 'rgba(148, 163, 184, 0.35)' }
                },
                y1: {
                    position: 'right',
                    min: 0,
                    max: 10,
                    ticks: { stepSize: 2, color: '#2caffe' },
                    title: { display: true, text: 'Điểm của bạn', color: '#2caffe' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });
})();
</script>
@endif
@endpush
