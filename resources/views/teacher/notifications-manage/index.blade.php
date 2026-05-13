@extends('layouts.teacher')

@section('title', 'Gửi Thông Báo')
@section('page-title', 'Gửi Thông Báo')

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Quản lý Thông Báo</h5>
        <a href="{{ route('teacher.notifications.manage.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Thêm mới
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 55%;">Tiêu đề</th>
                        <th>Đối tượng</th>
                        <th class="text-end" style="width: 160px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $it)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $it->title }}</div>
                                @if($it->summary)
                                    <div class="text-muted" style="font-size: 13px;">{{ $it->summary }}</div>
                                @endif
                                @if($it->attachment_path)
                                    <div class="mt-1" style="font-size: 13px;">
                                        <span class="badge bg-info text-dark">PDF</span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $audMap = ['teacher' => 'Giáo viên', 'student' => 'Sinh viên', 'all' => 'Tất cả'];
                                @endphp
                                <span>{{ $audMap[$it->audience] ?? 'Tất cả' }}</span>
                            </td>
                            <td class="text-end">
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('teacher.notifications.manage.edit', $it) }}">Sửa</a>
                                <form method="POST" action="{{ route('teacher.notifications.manage.destroy', $it) }}" class="d-inline" onsubmit="return confirm('Xóa thông báo này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" type="submit">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Chưa có thông báo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection

