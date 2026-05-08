@php
    $layout = auth()->check()
        ? (auth()->user()->role === 'student' ? 'layouts.student' : (auth()->user()->role === 'teacher' ? 'layouts.teacher' : 'layouts.admin'))
        : null;
    $pdfUrl = $item->attachment_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->attachment_path) : null;
@endphp

@extends($layout ?? 'layouts.guest')
@section('title', $item->title)
@if($layout)
    @section('page-title', $layout === 'layouts.admin' ? 'Thông Báo' : '')
@endif

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <div class="text-muted" style="font-size: 13px;">
                <span class="text-uppercase">{{ $item->audience }}</span>
            </div>
            <h5 class="mb-0 fw-bold">{{ $item->title }}</h5>
            @if($item->summary)
                <div class="text-muted mt-1">{{ $item->summary }}</div>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($item->content)
            <div class="mb-3" style="line-height: 1.65;">
                {!! $item->content !!}
            </div>
        @endif

        @if($pdfUrl)
            <div class="d-flex gap-2 mb-2">
                <a class="btn btn-primary btn-sm" href="{{ $pdfUrl }}" target="_blank" rel="noopener">Xem PDF</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ $pdfUrl }}" download>Tải về</a>
            </div>
            <div class="ratio ratio-16x9">
                <iframe src="{{ $pdfUrl }}" title="PDF" style="border: 1px solid rgba(0,0,0,.1); border-radius: 8px;"></iframe>
            </div>
        @endif

        @if(!$item->content && !$pdfUrl)
            <div class="text-muted">Thông báo này chưa có nội dung.</div>
        @endif
    </div>
</div>
@endsection

