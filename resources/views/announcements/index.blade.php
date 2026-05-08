@php
    $layout = auth()->check()
        ? (auth()->user()->role === 'student' ? 'layouts.student' : (auth()->user()->role === 'teacher' ? 'layouts.teacher' : 'layouts.admin'))
        : null;
@endphp

@extends($layout ?? 'layouts.guest')
@section('title', 'Thông Báo')
@if($layout)
    @section('page-title', $layout === 'layouts.admin' ? 'Thông Báo' : '')
@endif

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Thông Báo</h5>
    </div>
    <div class="card-body">
        <div class="list-group">
            @forelse($items as $it)
                <a class="list-group-item list-group-item-action" href="{{ route('announcements.show', $it->slug) }}">
                    <div class="d-flex w-100 justify-content-between gap-3">
                        <h6 class="mb-1 fw-bold">{{ $it->title }}</h6>
                    </div>
                    @if($it->summary)
                        <p class="mb-1 text-muted">{{ $it->summary }}</p>
                    @endif
                    <small class="text-muted">
                        Đối tượng: <span class="text-uppercase">{{ $it->audience }}</span>
                        @if($it->attachment_path)
                            • <span class="fw-bold">PDF</span>
                        @endif
                    </small>
                </a>
            @empty
                <div class="text-muted">Chưa có thông báo.</div>
            @endforelse
        </div>

        <div class="mt-3">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection

