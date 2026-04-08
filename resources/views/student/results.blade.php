@extends('layouts.student')

@section('title', 'Kết Quả Học Tập')
@section('page-title', '')

@section('content')
@php
    $txCount = 5;
    $thCount = 5;
@endphp

<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold mb-0">Kết quả học tập</h5>
                    <p class="text-muted small mb-0">Hiển thị điểm theo các học phần đã đăng ký.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if(($offerings ?? collect())->isEmpty())
                <div class="p-3">
                    <div class="alert alert-info mb-0">Chưa có học phần nào để hiển thị điểm.</div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th rowspan="2" class="text-start ps-3" style="min-width: 260px">Tên môn / học phần</th>
                                <th rowspan="2" style="min-width: 90px">Số TC</th>
                                <th rowspan="2" style="min-width: 90px">Giữa kỳ</th>
                                <th colspan="{{ $txCount }}">Thường xuyên</th>
                                <th colspan="{{ $thCount }}">Thực hành</th>
                                <th rowspan="2" style="min-width: 90px">Cuối kỳ</th>
                                <th rowspan="2" style="min-width: 110px">Điểm tổng kết</th>
                                <th rowspan="2" style="min-width: 110px">Thang điểm 4</th>
                                <th rowspan="2" style="min-width: 90px">Điểm chữ</th>
                                <th rowspan="2" style="min-width: 120px">Xếp loại</th>
                            </tr>
                            <tr>
                                @for($i=1;$i<=$txCount;$i++)
                                    <th style="min-width:60px">{{ $i }}</th>
                                @endfor
                                @for($i=1;$i<=$thCount;$i++)
                                    <th style="min-width:60px">{{ $i }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offerings as $o)
                                @php
                                    $sub = $o->subject;
                                    $g = $gradesByOffering[$o->id] ?? null;
                                    $tx = is_array($g?->thuong_xuyen) ? $g->thuong_xuyen : [];
                                    $th = is_array($g?->thuc_hanh) ? $g->thuc_hanh : [];
                                    $name = $sub ? ($sub->ma_mon_hoc.' — '.$sub->ten_mon_hoc) : ($o->ten_hoc_phan ?? '—');
                                @endphp
                                <tr>
                                    <td class="text-start ps-3">
                                        <div class="fw-semibold">{{ $name }}</div>
                                        <div class="small text-muted">
                                            @if($o->ten_hoc_phan) {{ $o->ten_hoc_phan }} @endif
                                            @if($o->classRoom)
                                                &nbsp;·&nbsp; {{ $o->classRoom->ma_lop }} — {{ $o->classRoom->ten_lop }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $sub?->so_tin_chi ?? '' }}</td>
                                    <td>{{ $g?->giua_ky ?? '' }}</td>
                                    @for($i=1;$i<=$txCount;$i++)
                                        <td>{{ $tx[$i] ?? '' }}</td>
                                    @endfor
                                    @for($i=1;$i<=$thCount;$i++)
                                        <td>{{ $th[$i] ?? '' }}</td>
                                    @endfor
                                    <td>{{ $g?->cuoi_ky ?? '' }}</td>
                                    <td class="fw-semibold">{{ $g?->diem_tong_ket ?? '' }}</td>
                                    <td>{{ $g?->thang_diem_4 ?? '' }}</td>
                                    <td>{{ $g?->diem_chu ?? '' }}</td>
                                    <td>{{ $g?->xep_loai ?? '' }}</td>
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

