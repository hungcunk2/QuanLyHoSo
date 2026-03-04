@extends('layouts.student')

@section('title', 'Chỉnh sửa thông tin cá nhân')
@section('page-title', 'Chỉnh sửa thông tin cá nhân')

@section('content')
<div class="dashboard-container">
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Chỉnh sửa thông tin</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    {{-- Ảnh đại diện --}}
                    <div class="col-12">
                        <label class="form-label">Ảnh đại diện</label>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            @if($student->avatar)
                                <div class="rounded-circle overflow-hidden bg-secondary flex-shrink-0" style="width: 80px; height: 80px;">
                                    <img src="{{ asset('storage/' . $student->avatar) }}" alt="Ảnh hiện tại" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <input type="file" name="avatar" class="form-control form-control-sm @error('avatar') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                <small class="text-muted">JPG, PNG, GIF, WebP. Tối đa 2MB.</small>
                                @error('avatar')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-2"><h6 class="border-bottom pb-1">Thông tin cá nhân</h6></div>
                    <div class="col-md-6">
                        <label class="form-label">Ngày sinh</label>
                        <input type="date" name="ngay_sinh" class="form-control @error('ngay_sinh') is-invalid @enderror" value="{{ old('ngay_sinh', $student->ngay_sinh ? $student->ngay_sinh->format('Y-m-d') : '') }}">
                        @error('ngay_sinh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Dân tộc</label>
                        <input type="text" name="dan_toc" class="form-control @error('dan_toc') is-invalid @enderror" value="{{ old('dan_toc', $student->dan_toc) }}">
                        @error('dan_toc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tôn giáo</label>
                        <input type="text" name="ton_giao" class="form-control @error('ton_giao') is-invalid @enderror" value="{{ old('ton_giao', $student->ton_giao) }}">
                        @error('ton_giao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Quốc tịch</label>
                        <input type="text" name="quoc_tich" class="form-control @error('quoc_tich') is-invalid @enderror" value="{{ old('quoc_tich', $student->quoc_tich) }}">
                        @error('quoc_tich')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Khu vực</label>
                        <input type="text" name="khu_vuc" class="form-control @error('khu_vuc') is-invalid @enderror" value="{{ old('khu_vuc', $student->khu_vuc) }}">
                        @error('khu_vuc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Số CCCD</label>
                        <input type="text" name="so_cccd" class="form-control @error('so_cccd') is-invalid @enderror" value="{{ old('so_cccd', $student->so_cccd) }}">
                        @error('so_cccd')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ngày cấp CCCD</label>
                        <input type="date" name="ngay_cap_cccd" class="form-control @error('ngay_cap_cccd') is-invalid @enderror" value="{{ old('ngay_cap_cccd', $student->ngay_cap_cccd ? $student->ngay_cap_cccd->format('Y-m-d') : '') }}">
                        @error('ngay_cap_cccd')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nơi cấp CCCD</label>
                        <input type="text" name="noi_cap_cccd" class="form-control @error('noi_cap_cccd') is-invalid @enderror" value="{{ old('noi_cap_cccd', $student->noi_cap_cccd) }}">
                        @error('noi_cap_cccd')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Đối tượng</label>
                        <input type="text" name="doi_tuong" class="form-control @error('doi_tuong') is-invalid @enderror" value="{{ old('doi_tuong', $student->doi_tuong) }}">
                        @error('doi_tuong')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Diện chính sách</label>
                        <input type="text" name="dien_chinh_sach" class="form-control @error('dien_chinh_sach') is-invalid @enderror" value="{{ old('dien_chinh_sach', $student->dien_chinh_sach) }}">
                        @error('dien_chinh_sach')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ngày vào Đoàn</label>
                        <input type="text" name="ngay_vao_doan" class="form-control @error('ngay_vao_doan') is-invalid @enderror" value="{{ old('ngay_vao_doan', $student->ngay_vao_doan) }}">
                        @error('ngay_vao_doan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ngày vào Đảng</label>
                        <input type="text" name="ngay_vao_dang" class="form-control @error('ngay_vao_dang') is-invalid @enderror" value="{{ old('ngay_vao_dang', $student->ngay_vao_dang) }}">
                        @error('ngay_vao_dang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Điện thoại</label>
                        <input type="text" name="so_dien_thoai" class="form-control @error('so_dien_thoai') is-invalid @enderror" value="{{ old('so_dien_thoai', $student->so_dien_thoai) }}">
                        @error('so_dien_thoai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $student->email ?? $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Địa chỉ liên hệ</label>
                        <textarea name="dia_chi_lien_he" class="form-control @error('dia_chi_lien_he') is-invalid @enderror" rows="2">{{ old('dia_chi_lien_he', $student->dia_chi_lien_he) }}</textarea>
                        @error('dia_chi_lien_he')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nơi sinh</label>
                        <input type="text" name="noi_sinh" class="form-control @error('noi_sinh') is-invalid @enderror" value="{{ old('noi_sinh', $student->noi_sinh) }}">
                        @error('noi_sinh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Hộ khẩu thường trú</label>
                        <textarea name="ho_khau_thuong_tru" class="form-control @error('ho_khau_thuong_tru') is-invalid @enderror" rows="2">{{ old('ho_khau_thuong_tru', $student->ho_khau_thuong_tru ?? $student->dia_chi) }}</textarea>
                        @error('ho_khau_thuong_tru')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Địa chỉ</label>
                        <textarea name="dia_chi" class="form-control @error('dia_chi') is-invalid @enderror" rows="2">{{ old('dia_chi', $student->dia_chi) }}</textarea>
                        @error('dia_chi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 mt-3"><h6 class="border-bottom pb-1">Quan hệ gia đình – Thông tin Cha</h6></div>
                    <div class="col-md-6">
                        <label class="form-label">Họ tên Cha</label>
                        <input type="text" name="ho_ten_cha" class="form-control @error('ho_ten_cha') is-invalid @enderror" value="{{ old('ho_ten_cha', $student->ho_ten_cha) }}">
                        @error('ho_ten_cha')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Số điện thoại Cha</label>
                        <input type="text" name="sdt_cha" class="form-control @error('sdt_cha') is-invalid @enderror" value="{{ old('sdt_cha', $student->sdt_cha) }}">
                        @error('sdt_cha')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 mt-3"><h6 class="border-bottom pb-1">Quan hệ gia đình – Thông tin Mẹ</h6></div>
                    <div class="col-md-6">
                        <label class="form-label">Họ tên Mẹ</label>
                        <input type="text" name="ho_ten_me" class="form-control @error('ho_ten_me') is-invalid @enderror" value="{{ old('ho_ten_me', $student->ho_ten_me) }}">
                        @error('ho_ten_me')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Số điện thoại Mẹ</label>
                        <input type="text" name="sdt_me" class="form-control @error('sdt_me') is-invalid @enderror" value="{{ old('sdt_me', $student->sdt_me) }}">
                        @error('sdt_me')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 pt-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Lưu thay đổi</button>
                        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">Hủy</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
