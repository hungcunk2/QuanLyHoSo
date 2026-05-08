@extends('layouts.teacher')

@section('title', 'Thông Báo')
@section('page-title', '')

@section('content')
@php
    $items = \App\Models\Announcement::query()
        ->whereIn('audience', ['all', 'teacher'])
        ->orderByDesc('created_at')
        ->paginate(15);
@endphp

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Thông Báo</h5>
        <a href="{{ route('announcements.index') }}" class="btn btn-light btn-sm">Xem tất cả</a>
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
                        @if($it->attachment_path)
                            PDF •
                        @endif
                        Xem chi tiết
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

