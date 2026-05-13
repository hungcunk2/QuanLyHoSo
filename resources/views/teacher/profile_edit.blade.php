@extends('layouts.teacher')

@section('title', 'Chỉnh sửa thông tin cá nhân')
@section('page-title', 'Chỉnh sửa thông tin cá nhân')

@section('content')
<div class="dashboard-container">
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Chỉnh sửa thông tin</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('teacher.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    {{-- Ảnh đại diện --}}
                    <div class="col-12">
                        <label class="form-label">Ảnh đại diện</label>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            @if($teacher->avatar)
                                <div class="rounded-circle overflow-hidden bg-secondary flex-shrink-0" style="width: 80px; height: 80px;">
                                    <img src="{{ asset('storage/' . $teacher->avatar) }}" alt="Ảnh hiện tại" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <input type="file" name="avatar" class="form-control form-control-sm @error('avatar') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                <small class="text-muted">JPG, PNG, GIF, WebP. Tối đa 2MB.</small>
                                @error('avatar')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-2"><h6 class="border-bottom pb-1">Thông tin cơ bản</h6></div>

                    <div class="col-md-6">
                        <label class="form-label">MSGV</label>
                        <input type="text" class="form-control" value="{{ $teacher->msgv ?? ($user->username ?? '') }}" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Họ tên</label>
                        <input type="text" name="ho_ten" class="form-control @error('ho_ten') is-invalid @enderror" value="{{ old('ho_ten', $teacher->ho_ten) }}">
                        @error('ho_ten')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $teacher->email ?? $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SĐT</label>
                        <input type="text" name="sdt" class="form-control @error('sdt') is-invalid @enderror" value="{{ old('sdt', $teacher->sdt) }}">
                        @error('sdt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Ngày sinh</label>
                        <input type="date" name="ngay_sinh" class="form-control @error('ngay_sinh') is-invalid @enderror" value="{{ old('ngay_sinh', $teacher->ngay_sinh ? $teacher->ngay_sinh->format('Y-m-d') : '') }}">
                        @error('ngay_sinh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Chuyên môn</label>
                        <input type="text" name="chuyen_mon" class="form-control @error('chuyen_mon') is-invalid @enderror" value="{{ old('chuyen_mon', $teacher->chuyen_mon) }}">
                        @error('chuyen_mon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Địa chỉ</label>
                        <textarea name="dia_chi" class="form-control @error('dia_chi') is-invalid @enderror" rows="2">{{ old('dia_chi', $teacher->dia_chi) }}</textarea>
                        @error('dia_chi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 pt-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Lưu thay đổi</button>
                        <a href="{{ route('teacher.profile') }}" class="btn btn-outline-secondary">Hủy</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

