@extends('layouts.admin')

@php
    $isEdit = (bool) $item->exists;
    $selectedRequiredIds = collect(old(
        'required_subject_ids',
        $isEdit
            ? $item->subjects->where('pivot.loai_hoc_phan', 'bat_buoc')->pluck('id')->all()
            : []
    ))
        ->map(fn ($id) => (int) $id)
        ->all();
    $selectedElectiveIds = collect(old(
        'elective_subject_ids',
        $isEdit
            ? $item->subjects->where('pivot.loai_hoc_phan', 'tu_chon')->pluck('id')->all()
            : []
    ))
        ->map(fn ($id) => (int) $id)
        ->all();
    $electiveGroupNumbers = collect(old(
        'elective_group_numbers',
        $isEdit
            ? $item->subjects
                ->where('pivot.loai_hoc_phan', 'tu_chon')
                ->mapWithKeys(fn ($subject) => [(int) $subject->id => (int) ($subject->pivot->nhom_tu_chon ?? 0)])
                ->all()
            : []
    ))->all();
    $electiveRequiredCredits = collect(old(
        'elective_required_credits',
        $isEdit
            ? $item->subjects
                ->where('pivot.loai_hoc_phan', 'tu_chon')
                ->mapWithKeys(fn ($subject) => [(int) $subject->id => (int) ($subject->pivot->so_tc_bat_buoc_cua_nhom ?? 0)])
                ->all()
            : []
    ))->all();
@endphp

@section('title', $isEdit ? 'Sửa kỳ chương trình khung' : 'Tạo kỳ chương trình khung')
@section('page-title', '')

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">{{ $isEdit ? 'Sửa kỳ chương trình khung' : 'Tạo kỳ chương trình khung' }}</h5>
        <a href="{{ route('admin.curriculum-terms.index') }}" class="btn btn-light btn-sm">Quay lại</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ $isEdit ? route('admin.curriculum-terms.update', $item) : route('admin.curriculum-terms.store') }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tên kỳ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="ten_ky" value="{{ old('ten_ky', $item->ten_ky) }}" placeholder="Ví dụ: Học kỳ 1" required>
                    @error('ten_ky')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Thứ tự kỳ <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="thu_tu" min="1" max="50" value="{{ old('thu_tu', $item->thu_tu) }}" required>
                    @error('thu_tu')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Ghi chú</label>
                    <textarea class="form-control" name="ghi_chu" rows="3" placeholder="Ghi chú thêm cho kỳ học này...">{{ old('ghi_chu', $item->ghi_chu) }}</textarea>
                    @error('ghi_chu')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <div class="row g-3">
                        <div class="col-xl-6">
                            <label class="form-label fw-bold">Học phần bắt buộc</label>
                            <input
                                type="text"
                                class="form-control mb-2 curriculum-subject-search"
                                placeholder="Tìm theo tên hoặc mã môn học..."
                                data-target="#required-subject-list"
                            >
                            <div class="border rounded p-3" style="max-height: 420px; overflow: auto;">
                                <div class="row g-2" id="required-subject-list">
                                    @forelse($subjects as $subject)
                                        <div class="col-12 curriculum-subject-item">
                                            <label class="border rounded d-flex align-items-start gap-2 p-2 h-100 w-100">
                                                <input
                                                    class="form-check-input mt-1"
                                                    type="checkbox"
                                                    name="required_subject_ids[]"
                                                    value="{{ $subject->id }}"
                                                    {{ in_array((int) $subject->id, $selectedRequiredIds, true) ? 'checked' : '' }}
                                                >
                                                <span>
                                                    <span class="fw-bold d-block">{{ $subject->ten_mon_hoc }}</span>
                                                    <span class="text-muted" style="font-size: 13px;">
                                                        {{ $subject->ma_mon_hoc }} • {{ $subject->so_tin_chi }} tín chỉ
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    @empty
                                        <div class="col-12 text-muted">Chưa có môn học nào để chọn.</div>
                                    @endforelse
                                </div>
                            </div>
                            @error('required_subject_ids')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                            @error('required_subject_ids.*')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-xl-6">
                            <label class="form-label fw-bold">Học phần tự chọn</label>
                            <input
                                type="text"
                                class="form-control mb-2 curriculum-subject-search"
                                placeholder="Tìm theo tên hoặc mã môn học..."
                                data-target="#elective-subject-list"
                            >
                            <div class="border rounded p-3" style="max-height: 420px; overflow: auto;">
                                <div class="row g-2" id="elective-subject-list">
                                    @forelse($subjects as $subject)
                                        <div class="col-12 curriculum-subject-item">
                                            <div class="border rounded p-2 h-100 w-100">
                                                <label class="d-flex align-items-start gap-2 mb-2 w-100">
                                                    <input
                                                        class="form-check-input mt-1 curriculum-elective-checkbox"
                                                        type="checkbox"
                                                        name="elective_subject_ids[]"
                                                        value="{{ $subject->id }}"
                                                        {{ in_array((int) $subject->id, $selectedElectiveIds, true) ? 'checked' : '' }}
                                                    >
                                                    <span>
                                                        <span class="fw-bold d-block">{{ $subject->ten_mon_hoc }}</span>
                                                        <span class="text-muted" style="font-size: 13px;">
                                                            {{ $subject->ma_mon_hoc }} • {{ $subject->so_tin_chi }} tín chỉ
                                                        </span>
                                                    </span>
                                                </label>
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <label class="form-label mb-1" style="font-size: 12px;">Nhóm tự chọn</label>
                                                        <input
                                                            type="number"
                                                            class="form-control form-control-sm"
                                                            name="elective_group_numbers[{{ $subject->id }}]"
                                                            min="0"
                                                            max="100"
                                                            value="{{ $electiveGroupNumbers[$subject->id] ?? 0 }}"
                                                        >
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label mb-1" style="font-size: 12px;">Số TC bắt buộc của nhóm</label>
                                                        <input
                                                            type="number"
                                                            class="form-control form-control-sm"
                                                            name="elective_required_credits[{{ $subject->id }}]"
                                                            min="0"
                                                            max="100"
                                                            value="{{ $electiveRequiredCredits[$subject->id] ?? 0 }}"
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-muted">Chưa có môn học nào để chọn.</div>
                                    @endforelse
                                </div>
                            </div>
                            @error('elective_subject_ids')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                            @error('elective_subject_ids.*')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo kỳ' }}</button>
                <a href="{{ route('admin.curriculum-terms.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.curriculum-subject-search').forEach(function (input) {
            input.addEventListener('input', function () {
                var query = (this.value || '').toLowerCase().trim();
                var targetSelector = this.getAttribute('data-target');
                var container = targetSelector ? document.querySelector(targetSelector) : null;

                if (!container) {
                    return;
                }

                container.querySelectorAll('.curriculum-subject-item').forEach(function (item) {
                    var text = (item.textContent || '').toLowerCase();
                    item.style.display = text.includes(query) ? '' : 'none';
                });
            });
        });
    });
</script>
@endpush
