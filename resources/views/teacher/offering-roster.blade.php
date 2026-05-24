@extends('layouts.teacher')

@section('title', 'Danh sách sinh viên — '.$courseOffering->ten_hoc_phan)
@section('page-title', 'Danh sách lớp')

@push('styles')
<style>
@media print {
    .admin-sidebar,
    .admin-header,
    .no-print,
    .breadcrumb,
    #aiChatbox,
    .ai-chatbox {
        display: none !important;
    }
    .admin-wrapper {
        display: block !important;
    }
    .admin-main {
        margin: 0 !important;
        width: 100% !important;
    }
    .admin-content {
        padding: 0 !important;
        margin: 0 !important;
    }
    .roster-print-page .card {
        border: none !important;
        box-shadow: none !important;
    }
    .roster-print-page .card-header {
        display: none !important;
    }
    .roster-print-page .table {
        font-size: 11pt;
    }
    .roster-print-page .table th,
    .roster-print-page .table td {
        padding: 0.35rem 0.5rem !important;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid roster-print-page">
    <div class="row mb-3 no-print">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ $rosterListUrl ?? route('teacher.my-classes') }}">{{ $rosterListLabel ?? 'Lớp học của tôi' }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Danh sách sinh viên</li>
                </ol>
            </nav>
            <div class="card">
                <div class="card-body">
                    <h5 class="fw-bold mb-2">{{ $courseOffering->ten_hoc_phan }}</h5>
                    <p class="text-muted small mb-0">
                        @if($courseOffering->subject)
                            <strong>Môn:</strong> {{ $courseOffering->subject->ma_mon_hoc }} — {{ $courseOffering->subject->ten_mon_hoc }}
                        @endif
                        @if($courseOffering->classRoom)
                            &nbsp;·&nbsp; <strong>Phòng:</strong> {{ $courseOffering->classRoom->ma_lop }} — {{ $courseOffering->classRoom->ten_lop }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="d-none d-print-block mb-3 text-center roster-print-banner">
        <div class="fw-bold text-uppercase" style="font-size: 1.1rem;">Danh sách sinh viên học phần</div>
        <div class="fw-bold mt-1">{{ $courseOffering->ten_hoc_phan }}</div>
        <div class="small mt-2">
            @if($courseOffering->subject)
                Môn: {{ $courseOffering->subject->ma_mon_hoc }} — {{ $courseOffering->subject->ten_mon_hoc }}
            @endif
            @if($courseOffering->classRoom)
                · Lớp: {{ $courseOffering->classRoom->ma_lop }} — {{ $courseOffering->classRoom->ten_lop }}
            @endif
            @if($courseOffering->hoc_ky || $courseOffering->khoa_hoc)
                · {{ trim(($courseOffering->hoc_ky ? 'HK '.$courseOffering->hoc_ky : '').($courseOffering->khoa_hoc ? ' · KH '.$courseOffering->khoa_hoc : '')) }}
            @endif
        </div>
        <div class="small mt-1">
            Giảng viên: {{ $teacher->ho_ten ?? '—' }}@if($teacher->msgv) ({{ $teacher->msgv }})@endif
            · In ngày: {{ now()->format('d/m/Y H:i') }}
            · Tổng: {{ $registrations->count() }} sinh viên
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 no-print">
            <span class="fw-semibold">Danh sách học sinh/sinh viên ({{ $registrations->count() }})</span>
            <div class="d-flex flex-wrap gap-2">
                @if($registrations->isNotEmpty())
                    <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> In danh sách
                    </button>
                @endif
                <a href="{{ $rosterListUrl ?? route('teacher.my-classes') }}" class="btn btn-outline-secondary btn-sm">Quay lại</a>
            </div>
        </div>
        <div class="card-body p-0">
            @if($registrations->isEmpty())
                <p class="text-muted p-3 mb-0">Chưa có sinh viên nào đăng ký học phần này.</p>
            @else
                <div class="table-responsive admin-table-wrap">
                    <table class="table table-striped border mb-0 align-middle roster-print-table">
                        <thead>
                            <tr>
                                <th style="width:70px">STT</th>
                                <th>Tên học sinh</th>
                                <th style="width:140px">MSSV</th>
                                <th>Email</th>
                                <th style="width:140px">Lớp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($registrations as $i => $reg)
                                @php $s = $reg->student; @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="fw-semibold">{{ $s?->ho_ten ?? '—' }}</td>
                                    <td>{{ $s?->mssv ?? '—' }}</td>
                                    <td>{{ $s?->email ?? '—' }}</td>
                                    <td>{{ $lopNameByCode[$s?->lop ?? ''] ?? ($s?->lop ?? '—') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
