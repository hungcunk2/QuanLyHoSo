@extends('layouts.teacher')

@section('title', 'Danh sách sinh viên — '.$courseOffering->ten_hoc_phan)
@section('page-title', 'Danh sách lớp')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
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

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-semibold">Danh sách học sinh/sinh viên ({{ $registrations->count() }})</span>
            <a href="{{ $rosterListUrl ?? route('teacher.my-classes') }}" class="btn btn-outline-secondary btn-sm">Quay lại</a>
        </div>
        <div class="card-body p-0">
            @if($registrations->isEmpty())
                <p class="text-muted p-3 mb-0">Chưa có sinh viên nào đăng ký học phần này.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped border mb-0 align-middle">
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
                                    <td>{{ $s?->lop ?? '—' }}</td>
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
