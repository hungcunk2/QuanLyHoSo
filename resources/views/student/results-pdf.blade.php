<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bảng điểm</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        .row { width: 100%; }
        .muted { color: #666; }
        .title { text-align: center; font-size: 18px; font-weight: 700; margin: 4px 0 10px; }
        .sub { text-align: center; font-size: 12px; margin-bottom: 12px; }
        .info { margin-bottom: 10px; }
        .info table { width: 100%; border-collapse: collapse; }
        .info td { padding: 2px 0; vertical-align: top; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #bbb; padding: 3px 4px; text-align: center; word-break: break-word; }
        th { background: #f1f3f5; font-weight: 700; }
        .left { text-align: left; }
        .group { background: #e9ecef; font-weight: 700; text-align: left; }
        .sign { margin-top: 22px; width: 100%; }
        .sign td { border: 0; padding: 0; }
        .sign .box { width: 33.33%; text-align: center; }
        .sign .line { margin-top: 60px; display: inline-block; border-top: 1px dotted #444; padding-top: 4px; min-width: 220px; }
        .stamp { margin-top: 6px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="title">BẢNG ĐIỂM KẾT QUẢ HỌC TẬP</div>
    <div class="sub muted">&nbsp;</div>

    <div class="info">
        <table>
            <tr>
                <td style="width: 50%">
                    <div><strong>Họ tên:</strong> {{ $student->ho_ten ?? '—' }}</div>
                    <div><strong>MSSV:</strong> {{ $student->mssv ?? '—' }}</div>
                    <div><strong>Lớp:</strong> {{ $lopTen ?? ($student->lop ?? '—') }}</div>
                </td>
                <td style="width: 50%">
                    <div><strong>Email:</strong> {{ $student->email ?? '—' }}</div>
                    <div><strong>Ngày in:</strong> {{ now()->format('d/m/Y H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    @php
        $txCount = 4;
        $thCount = 3;
        $groups = ($offerings ?? collect())->groupBy(function ($o) {
            $khoa = (string) ($o->khoa_hoc ?? '');
            $hk = (string) ($o->hoc_ky ?? '');
            return $khoa.'|'.$hk;
        });
    @endphp

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="left" style="width: 32%">Tên môn / học phần</th>
                <th rowspan="2" style="width: 5%">Số TC</th>
                <th rowspan="2" style="width: 5%">GK</th>
                <th colspan="{{ $txCount }}">TX</th>
                <th colspan="{{ $thCount }}">TH</th>
                <th rowspan="2" style="width: 5%">CK</th>
                <th rowspan="2" style="width: 6%">TK(10)</th>
                <th rowspan="2" style="width: 5%">Hệ 4</th>
                <th rowspan="2" style="width: 5%">Chữ</th>
                <th rowspan="2" style="width: 15%">Xếp loại</th>
            </tr>
            <tr>
                @for($i=1;$i<=$txCount;$i++) <th style="width: 4%">{{ $i }}</th> @endfor
                @for($i=1;$i<=$thCount;$i++) <th style="width: 4%">{{ $i }}</th> @endfor
            </tr>
        </thead>
        <tbody>
            @forelse($groups as $key => $items)
                @php
                    [$khoa, $hk] = array_pad(explode('|', (string) $key, 2), 2, '');
                    $label = trim(($hk !== '' ? ('HK'.$hk) : 'Học kỳ') . ($khoa !== '' ? (' ('.$khoa.')') : ''));
                @endphp
                <tr>
                    <td colspan="{{ 1 + 1 + 1 + $txCount + $thCount + 1 + 1 + 1 + 1 + 1 }}" class="group">{{ $label }}</td>
                </tr>
                @foreach($items as $o)
                    @php
                        $sub = $o->subject;
                        $g = $gradesByOffering[$o->id] ?? null;
                        $tx = is_array($g?->thuong_xuyen) ? $g->thuong_xuyen : [];
                        $th = is_array($g?->thuc_hanh) ? $g->thuc_hanh : [];
                        $name = $sub ? ($sub->ma_mon_hoc.' — '.$sub->ten_mon_hoc) : ($o->ten_hoc_phan ?? '—');
                    @endphp
                    <tr>
                        <td class="left">
                            <div><strong>{{ $name }}</strong></div>
                            <div class="muted" style="font-size: 10px">
                                @if($o->ten_hoc_phan) {{ $o->ten_hoc_phan }} @endif
                                @if($o->classRoom)
                                    &nbsp;·&nbsp; {{ $o->classRoom->ma_lop }} — {{ $o->classRoom->ten_lop }}
                                @endif
                            </div>
                        </td>
                        <td>{{ $sub?->so_tin_chi ?? '' }}</td>
                        <td>{{ $g?->giua_ky ?? '' }}</td>
                        @for($i=1;$i<=$txCount;$i++) <td>{{ $tx[$i] ?? '' }}</td> @endfor
                        @for($i=1;$i<=$thCount;$i++) <td>{{ $th[$i] ?? '' }}</td> @endfor
                        <td>{{ $g?->cuoi_ky ?? '' }}</td>
                        <td><strong>{{ $g?->diem_tong_ket ?? '' }}</strong></td>
                        <td>{{ $g?->thang_diem_4 ?? '' }}</td>
                        <td>{{ $g?->diem_chu ?? '' }}</td>
                        <td>{{ $g?->xep_loai ?? '' }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="{{ 1 + 1 + 1 + $txCount + $thCount + 1 + 1 + 1 + 1 + 1 }}">Chưa có dữ liệu điểm.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="sign">
        <tr>
            <td class="box">
                <div><strong>Sinh viên</strong></div>
                <div class="muted">(Ký, ghi rõ họ tên)</div>
                <div class="line">{{ $student->ho_ten ?? '' }}</div>
            </td>
            <td class="box">
                <div><strong>Phòng/Đơn vị xác nhận</strong></div>
                <div class="muted">(Ký tên)</div>
                <div class="line">&nbsp;</div>
            </td>
            <td class="box">
                <div><strong>Xác nhận của Nhà trường</strong></div>
                <div class="muted">(Ký tên, đóng mộc)</div>
                <div class="stamp muted">Vị trí đóng mộc</div>
                <div class="line">&nbsp;</div>
            </td>
        </tr>
    </table>
</body>
</html>

