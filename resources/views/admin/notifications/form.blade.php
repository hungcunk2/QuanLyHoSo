@extends('layouts.admin')

@section('title', 'Thông Báo')
@section('page-title', 'Thông Báo')

@section('content')
@php
    /** @var \App\Models\Announcement $item */
    $isEdit = (bool) ($item->id ?? false);
@endphp

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">{{ $isEdit ? 'Cập nhật thông báo' : 'Thêm thông báo' }}</h5>
        <a href="{{ route('admin.notifications.index') }}" class="btn btn-light btn-sm">Quay lại</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">Vui lòng kiểm tra lại dữ liệu.</div>
        @endif

        <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('admin.notifications.update', $item) : route('admin.notifications.store') }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label class="form-label fw-bold">Tiêu đề</label>
                <input class="form-control" name="title" value="{{ old('title', $item->title) }}" required>
                @error('title')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Mô tả ngắn</label>
                <input class="form-control" name="summary" value="{{ old('summary', $item->summary) }}" placeholder="Tóm tắt ngắn (tuỳ chọn)">
                @error('summary')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Đối tượng</label>
                    <select class="form-select" name="audience" required>
                        @php($aud = old('audience', $item->audience ?: 'all'))
                        <option value="teacher" {{ $aud === 'teacher' ? 'selected' : '' }}>Giáo viên</option>
                        <option value="student" {{ $aud === 'student' ? 'selected' : '' }}>Sinh viên</option>
                        <option value="all" {{ $aud === 'all' ? 'selected' : '' }}>Tất cả</option>
                    </select>
                    @error('audience')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mt-3 mb-3">
                <label class="form-label fw-bold">Nội dung (có thể để trống nếu chỉ đăng PDF)</label>
                <textarea id="announcementContent" class="form-control" rows="10" name="content" placeholder="Nhập nội dung...">{{ old('content', $item->content) }}</textarea>
                @error('content')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">File PDF (tuỳ chọn)</label>
                <input type="file" class="form-control" name="attachment" accept="application/pdf">
                @error('attachment')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror

                @if($isEdit && $item->attachment_path)
                    <div class="mt-2 d-flex align-items-center gap-3">
                        <div class="text-muted" style="font-size: 13px;">
                            Đang có file: <code>{{ $item->attachment_path }}</code>
                        </div>
                        <label class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="remove_attachment" value="1">
                            <span class="form-check-label">Gỡ file hiện tại</span>
                        </label>
                    </div>
                @endif
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-light">Hủy</a>
                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo thông báo' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>
    <script>
        (function () {
            var el = document.getElementById('announcementContent');
            if (!el || !window.ClassicEditor) return;
            if (el.dataset.enhanced === '1') return;
            el.dataset.enhanced = '1';

            window.ClassicEditor.create(el, {
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'underline', 'link', '|',
                    'bulletedList', 'numberedList', '|',
                    'alignment', '|',
                    'blockQuote', 'insertTable', '|',
                    'undo', 'redo'
                ],
            }).catch(function () {
                // keep textarea as-is if editor fails
            });
        })();
    </script>
@endpush

