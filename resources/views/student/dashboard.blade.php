@extends('layouts.student')

@section('title', 'Thông tin cá nhân')
@section('page-title', 'Thông tin cá nhân')

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

<div class="dashboard-container">
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
    <div class="mb-3 text-end">
        <a href="{{ route('student.profile.edit') }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i>Chỉnh sửa
        </a>
    </div>
        {{-- I. Thông tin học vấn --}}
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Thông tin học vấn</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-auto mb-3 mb-md-0">
                        <div class="rounded-circle overflow-hidden bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 120px; height: 120px;">
                            @if($student && $student->avatar)
                                <img src="{{ asset('storage/' . $student->avatar) }}" alt="Ảnh đại diện" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <i class="fas fa-user-graduate fa-3x"></i>
                            @endif
                        </div>
                    </div>
                    <div class="col">
                        <div class="row g-2">
                            <div class="col-md-6 col-lg-4"><strong>MSSV:</strong> {{ $v('mssv') }}</div>
                            <div class="col-md-6 col-lg-4"><strong>Họ tên:</strong> {{ $v('ho_ten') }}</div>
                            <div class="col-md-6 col-lg-4"><strong>Giới tính:</strong> {{ $v('gioi_tinh') }}</div>
                            <div class="col-md-6 col-lg-4"><strong>Trạng thái:</strong> {{ $v('trang_thai') }}</div>
                            <div class="col-md-6 col-lg-4"><strong>Mã hồ sơ:</strong> {{ $v('ma_ho_so') }}</div>
                            <div class="col-md-6 col-lg-4"><strong>Ngày vào trường:</strong> {{ $v('ngay_vao_truong') }}</div>
                            <div class="col-md-6 col-lg-4"><strong>Lớp học:</strong> {{ $v('lop') }}</div>
                            <div class="col-md-6 col-lg-4"><strong>Cơ sở:</strong> {{ $v('co_so') }}</div>
                            <div class="col-md-6 col-lg-4"><strong>Bậc đào tạo:</strong> {{ $v('bac_dao_tao') }}</div>
                            <div class="col-md-6 col-lg-4"><strong>Loại hình đào tạo:</strong> {{ $v('loai_hinh_dao_tao') }}</div>
                            <div class="col-md-6 col-lg-4"><strong>Khoa:</strong> {{ $v('khoa') }}</div>
                            <div class="col-md-6 col-lg-4"><strong>Ngành:</strong> {{ $v('nganh') }}</div>
                            <div class="col-md-6 col-lg-4"><strong>Chuyên ngành:</strong> {{ $v('chuyen_nganh') }}</div>
                            <div class="col-md-6 col-lg-4"><strong>Khóa học:</strong> {{ $v('khoa_hoc') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- II. Thông tin cá nhân --}}
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Thông tin cá nhân</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6 col-lg-4"><strong>Ngày sinh:</strong> {{ $v('ngay_sinh') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Dân tộc:</strong> {{ $v('dan_toc') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Tôn giáo:</strong> {{ $v('ton_giao') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Quốc tịch:</strong> {{ $v('quoc_tich') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Khu vực:</strong> {{ $v('khu_vuc') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Số CCCD:</strong> {{ $v('so_cccd') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Ngày cấp:</strong> {{ $v('ngay_cap_cccd') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Nơi cấp:</strong> {{ $v('noi_cap_cccd') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Đối tượng:</strong> {{ $v('doi_tuong') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Diện chính sách:</strong> {{ $v('dien_chinh_sach') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Ngày vào Đoàn:</strong> {{ $v('ngay_vao_doan') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Ngày vào Đảng:</strong> {{ $v('ngay_vao_dang') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Điện thoại:</strong> {{ $v('so_dien_thoai') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Email:</strong> {{ $v('email') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Địa chỉ liên hệ:</strong> {{ $v('dia_chi_lien_he') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Nơi sinh:</strong> {{ $v('noi_sinh') }}</div>
                    <div class="col-12"><strong>Hộ khẩu thường trú:</strong> {{ $v('ho_khau_thuong_tru') }}</div>
                </div>
            </div>
        </div>

        {{-- III. Quan hệ gia đình --}}
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Quan hệ gia đình</h5>
            </div>
            <div class="card-body">
                <h6 class="text-muted mb-2">Thông tin Cha</h6>
                <div class="row g-2 mb-3">
                    <div class="col-md-6 col-lg-4"><strong>Họ tên Cha:</strong> {{ $v('ho_ten_cha') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Số điện thoại:</strong> {{ $v('sdt_cha') }}</div>
                </div>
                <h6 class="text-muted mb-2">Thông tin Mẹ</h6>
                <div class="row g-2">
                    <div class="col-md-6 col-lg-4"><strong>Họ tên Mẹ:</strong> {{ $v('ho_ten_me') }}</div>
                    <div class="col-md-6 col-lg-4"><strong>Số điện thoại:</strong> {{ $v('sdt_me') }}</div>
                </div>
            </div>
        </div>
</div>
@endsection
