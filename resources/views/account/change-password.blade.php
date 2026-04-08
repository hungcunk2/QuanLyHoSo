@php
    $user = auth()->user();
    $layout = match ($user->role ?? 'admin') {
        'student' => 'layouts.student',
        'teacher' => 'layouts.teacher',
        default => 'layouts.admin',
    };
@endphp

@extends($layout)

@section('title', 'Đổi mật khẩu')
@section('page-title', '')

@section('content')
<div class="container-fluid" style="max-width: 720px;">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <div class="fw-semibold mb-1">Không thể đổi mật khẩu</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Đổi mật khẩu</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('account.password.update') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Mật khẩu hiện tại</label>
                    <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                </div>

                <div class="mb-3">
                    <label class="form-label">Mật khẩu mới</label>
                    <input type="password" name="password" class="form-control" required minlength="6" autocomplete="new-password">
                </div>

                <div class="mb-3">
                    <label class="form-label">Xác nhận mật khẩu mới</label>
                    <input type="password" name="password_confirmation" class="form-control" required minlength="6" autocomplete="new-password">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Lưu
                    </button>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Quay lại</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

