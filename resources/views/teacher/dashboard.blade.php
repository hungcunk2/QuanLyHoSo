@extends('layouts.teacher')

@section('title', 'Bảng Điều Khiển')
@section('page-title', '')

@section('content')
@php
    $t = $teacher ?? null;
    $emptyLabel = 'Chưa cập nhật';
    $v = function($key, $default = null) use ($t, $user, $emptyLabel) {
        $default = $default ?? $emptyLabel;
        if (!$t) {
            return $key === 'email' ? ($user->email ?? $emptyLabel) : $default;
        }
        $map = [
            'msgv' => $t->msgv ?? ($user->username ?? ''),
            'ho_ten' => $t->ho_ten ?? '',
            'chuyen_mon' => $t->chuyen_mon ?? '',
            'ngay_sinh' => $t->ngay_sinh ? $t->ngay_sinh->format('d/m/Y') : '',
            'sdt' => $t->sdt ?? '',
            'email' => $t->email ?? ($user->email ?? ''),
            'dia_chi' => $t->dia_chi ?? '',
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
                            <span class="caption-subject bold">Thông tin giáo viên</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row g-3">
                            <div class="col-sm-3">
                                <div class="profile-userpic">
                                    @if($teacher && $teacher->avatar)
                                        <img src="{{ asset('storage/' . $teacher->avatar) }}" alt="Ảnh đại diện" class="img-fluid rounded-circle" style="width: 130px; height: 130px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle overflow-hidden bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 130px; height: 130px;">
                                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-center mt-2">
                                    <a href="{{ route('teacher.dashboard') }}" class="color-active">Xem chi tiết</a>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="row g-2 student-info-grid">
                                    <div class="col-6"><span class="text-muted">MSGV:</span> <span class="fw-bold">{{ $v('msgv') }}</span></div>
                                    <div class="col-6"><span class="text-muted">Chuyên môn:</span> <span class="fw-bold">{{ $v('chuyen_mon') }}</span></div>
                                    <div class="col-6"><span class="text-muted">Họ tên:</span> <span class="fw-bold">{{ $v('ho_ten') }}</span></div>
                                    <div class="col-6"><span class="text-muted">Email:</span> <span class="fw-bold">{{ $v('email') }}</span></div>
                                    <div class="col-6"><span class="text-muted">SĐT:</span> <span class="fw-bold">{{ $v('sdt') }}</span></div>
                                    <div class="col-6"><span class="text-muted">Ngày sinh:</span> <span class="fw-bold">{{ $v('ngay_sinh') }}</span></div>
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
                            <div class="item-box-menu box-df menu-card menu-card--reminder">
                                <div class="menu-card__row">
                                    <div class="menu-card__main">
                                        <h3 class="name nhacnho-custom mb-0">Nhắc nhở mới, chưa xem</h3>
                                        <div class="menu-card__number nhacnho-custom">{{ (int) ($remindersCount ?? 0) }}</div>
                                        <div class="text-start">
                                            <a href="{{ route('announcements.index') }}" class="color-active">Xem chi tiết</a>
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
                            <a href="{{ route('teacher.schedule') }}" class="color-active text-decoration-none" title="">
                                <div class="item-box-menu box-df menu-card menu-card--class lichhoc-custom">
                                    <div class="menu-card__row">
                                        <div class="menu-card__main">
                                            <h3 class="name mb-0">Lịch dạy trong tuần</h3>
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
                            <a href="{{ route('teacher.grading') }}" class="color-active text-decoration-none" title="">
                                <div class="item-box-menu box-df menu-card menu-card--exam lichthi-custom">
                                    <div class="menu-card__row">
                                        <div class="menu-card__main">
                                            <h3 class="name mb-0">Chấm điểm</h3>
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

    @php
        $shortcuts = [
            ['label' => 'Lịch dạy', 'icon' => 'fa-calendar', 'url' => route('teacher.schedule')],
            ['label' => 'Lớp học phần', 'icon' => 'fa-school', 'url' => route('teacher.my-classes')],
            ['label' => 'Chấm điểm', 'icon' => 'fa-clipboard-list', 'url' => route('teacher.grading')],
            ['label' => 'Thông báo', 'icon' => 'fa-bell', 'url' => route('teacher.notifications.manage.index')],
        ];
    @endphp
    <div class="featured mb-3">
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
    .chart-custom .box-df { border-color: #dfe6ee; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06); }
    .caption-subject.bold { font-weight: 700; }
    .actions .form-control { min-width: 180px; }
    .color-active { color: #0d6efd; }
    .profile-userpic { display:flex; justify-content:center; }
    .student-info-grid { font-size: 14px; line-height: 1.35; }
    @media (min-width: 1200px) { .student-info-grid { font-size: 15px; } }

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
    .nhacnho-custom { color:#667580; }

    .box-menu .item-box-menu { padding: 12px 14px; }
    .box-menu .name { font-size: 13px; font-weight: 700; margin-bottom: 8px; }
    .box-menu .number { font-size: 36px; font-weight: 800; line-height: 1; color:#003f65; }
    .box-menu .icon-menu .icon { font-size: 28px; color:#6c757d; }
    .nhacnho-border { border-left: 4px solid #eb2e51; }
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

    /* bỏ nền trắng phía sau các ô (gutter giống IUH) */
    .menu-right-portlet,
    .menu-right-body {
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    .featured {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
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
</style>
@endpush
