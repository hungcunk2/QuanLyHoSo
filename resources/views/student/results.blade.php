@extends('layouts.student')

@section('title', 'Kết Quả Học Tập')
@section('page-title', '')

@section('content')
@php
    $txCount = 4;
    $thCount = 3;
    $colspan = 1 + 1 + 1 + $txCount + $thCount + 1 + 1 + 1 + 1 + 1;
@endphp

<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold mb-0">Kết quả học tập</h5>
                    <p class="text-muted small mb-0">Hiển thị điểm theo các học phần đã đăng ký.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('student.results.pdf') }}" class="btn btn-outline-danger btn-sm text-nowrap">
                        <i class="fas fa-file-pdf me-1"></i> Tải PDF để in
                    </a>
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
                            @php
                                $groups = ($offerings ?? collect())->groupBy(function ($o) {
                                    $khoa = (string) ($o->khoa_hoc ?? '');
                                    $hk = (string) ($o->hoc_ky ?? '');
                                    return $khoa.'|'.$hk;
                                });
                            @endphp
                            @foreach($groups as $key => $items)
                                @php
                                    [$khoa, $hk] = array_pad(explode('|', (string) $key, 2), 2, '');
                                    $label = trim(($hk !== '' ? ('HK'.$hk) : 'Học kỳ') . ($khoa !== '' ? (' ('.$khoa.')') : ''));
                                @endphp
                                <tr class="table-secondary">
                                    <td colspan="{{ $colspan }}" class="text-start ps-3 fw-bold">{{ $label }}</td>
                                </tr>
                                @php
                                    $sumTcRegistered = 0;
                                    $sumTcPassed = 0;
                                    $sumTcInProgress = 0;

                                    $wSum10 = 0.0;
                                    $wTc10 = 0;
                                    $wSum4 = 0.0;
                                    $wTc4 = 0;

                                    $toNum = function ($v): ?float {
                                        if ($v === null || $v === '') return null;
                                        $s = is_string($v) ? str_replace(',', '.', trim($v)) : $v;
                                        if (!is_numeric($s)) return null;
                                        return (float) $s;
                                    };
                                @endphp
                                @foreach($items as $o)
                                    @php
                                        $sub = $o->subject;
                                        $g = $gradesByOffering[$o->id] ?? null;
                                        $tx = is_array($g?->thuong_xuyen) ? $g->thuong_xuyen : [];
                                        $th = is_array($g?->thuc_hanh) ? $g->thuc_hanh : [];
                                        $name = $sub ? ($sub->ma_mon_hoc.' — '.$sub->ten_mon_hoc) : ($o->ten_hoc_phan ?? '—');

                                        $tc = (int) ($sub?->so_tin_chi ?? 0);
                                        $sumTcRegistered += $tc;

                                        $tk = $toNum($g?->diem_tong_ket);
                                        if ($tk !== null && $tc > 0) {
                                            $wSum10 += $tk * $tc;
                                            $wTc10 += $tc;
                                            if ($tk >= 5) {
                                                $sumTcPassed += $tc;
                                            }
                                        } else {
                                            if (!($o->grades_finalized_at ?? null) && $tc > 0) {
                                                $sumTcInProgress += $tc;
                                            }
                                        }

                                        $d4 = $toNum($g?->thang_diem_4);
                                        if ($d4 !== null && $tc > 0) {
                                            $wSum4 += $d4 * $tc;
                                            $wTc4 += $tc;
                                        }
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
                                @php
                                    $avg10 = $wTc10 > 0 ? round($wSum10 / $wTc10, 2) : null;
                                    $avg4 = $wTc4 > 0 ? round($wSum4 / $wTc4, 2) : null;

                                    $sumTcFailed = max(0, $sumTcRegistered - $sumTcPassed - $sumTcInProgress);
                                    $leftSpan = intdiv($colspan, 2);
                                    $rightSpan = $colspan - $leftSpan;

                                    $hocLuc = function (?float $avg10): string {
                                        if ($avg10 === null) return '—';
                                        if ($avg10 >= 8.0) return 'Giỏi';
                                        if ($avg10 >= 6.5) return 'Khá';
                                        if ($avg10 >= 5.0) return 'Trung bình';
                                        if ($avg10 >= 4.0) return 'Yếu';
                                        return 'Kém';
                                    };
                                @endphp
                                <tr class="table-light small">
                                    <td colspan="{{ $leftSpan }}" class="text-start ps-3">
                                        <span class="text-muted">Điểm trung bình học kỳ hệ 10:</span>
                                        <strong>{{ $avg10 !== null ? number_format($avg10, 2, '.', '') : '—' }}</strong>
                                    </td>
                                    <td colspan="{{ $rightSpan }}" class="text-start ps-3">
                                        <span class="text-muted">Điểm trung bình học kỳ hệ 4:</span>
                                        <strong>{{ $avg4 !== null ? number_format($avg4, 2, '.', '') : '—' }}</strong>
                                    </td>
                                </tr>
                                <tr class="table-light small">
                                    <td colspan="{{ $leftSpan }}" class="text-start ps-3">
                                        <span class="text-muted">Tổng số tín chỉ đã đăng ký:</span>
                                        <strong>{{ $sumTcRegistered }}</strong>
                                    </td>
                                    <td colspan="{{ $rightSpan }}" class="text-start ps-3">
                                        <span class="text-muted">Tổng số tín chỉ tích lũy:</span>
                                        <strong>{{ $sumTcPassed }}</strong>
                                    </td>
                                </tr>
                                <tr class="table-light small">
                                    <td colspan="{{ $leftSpan }}" class="text-start ps-3">
                                        <span class="text-muted">Tổng số tín chỉ nợ:</span>
                                        <strong>{{ $sumTcFailed }}</strong>
                                    </td>
                                    <td colspan="{{ $rightSpan }}" class="text-start ps-3">
                                        <span class="text-muted">Tổng số tín chỉ nợ đến hiện tại:</span>
                                        <strong>{{ $sumTcFailed }}</strong>
                                    </td>
                                </tr>
                                <tr class="table-light small">
                                    <td colspan="{{ $leftSpan }}" class="text-start ps-3">
                                        <span class="text-muted">Tổng số tín chỉ nợ học kỳ:</span>
                                        <strong>{{ $sumTcFailed }}</strong>
                                    </td>
                                    <td colspan="{{ $rightSpan }}" class="text-start ps-3">
                                        <span class="text-muted">Xếp loại học lực học kỳ:</span>
                                        <strong>{{ $hocLuc($avg10) }}</strong>
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
@endsection

