<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <title>@yield('title', 'Giáo Viên') - IIUH</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    
    @stack('styles')
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <img src="{{ asset('images/logo.png') }}" alt="IIUH" style="height: 52px; width: auto;">
                    <span class="logo-text">Giáo Viên</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">MAIN</div>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="{{ route('teacher.dashboard') }}" class="nav-link">
                                <i class="fas fa-home"></i>
                                <span>Bảng Điều Khiển</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">QUẢN LÝ</div>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="{{ route('teacher.profile') }}" class="nav-link">
                                <i class="fas fa-user"></i>
                                <span>Thông Tin Cá Nhân</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('teacher.schedule') }}" class="nav-link">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Lịch dạy</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('teacher.my-classes') }}" class="nav-link">
                                <i class="fas fa-school"></i>
                                <span>Lớp học của tôi</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('teacher.grading') }}" class="nav-link">
                                <i class="fas fa-clipboard-list"></i>
                                <span>Chấm điểm</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('teacher.notifications.manage.index') }}" class="nav-link">
                                <i class="fas fa-bell"></i>
                                <span>Thông Báo</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">SYSTEM</div>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="{{ route('account.password.edit') }}" class="nav-link">
                                <i class="fas fa-key"></i>
                                <span>Đổi mật khẩu</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Đăng Xuất</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <p class="copyright">© 2025 IIUH Connect – All rights reserved</p>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <div class="header-left">
                    <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="header-right">
                    <button class="header-icon-btn theme-toggle" id="themeToggle">
                        <i class="fas fa-sun"></i>
                    </button>
                    @php
                        $userId = (int) (Auth::id() ?? 0);
                        $topAnnouncements = \App\Models\Announcement::query()
                            ->whereIn('audience', ['all', 'teacher'])
                            ->orderByDesc('created_at')
                            ->limit(6)
                            ->get();

                        $readIds = $userId
                            ? \Illuminate\Support\Facades\DB::table('announcement_reads')
                                ->where('user_id', $userId)
                                ->pluck('announcement_id')
                                ->all()
                            : [];

                        $unreadCount = $userId
                            ? \App\Models\Announcement::query()
                                ->whereIn('audience', ['all', 'teacher'])
                                ->whereNotIn('id', $readIds)
                                ->count()
                            : 0;
                    @endphp
                    <div class="dropdown">
                        <button
                            class="header-icon-btn notification-btn"
                            id="notificationBtn"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <i class="fas fa-bell"></i>
                            @if($unreadCount > 0)
                                <span class="badge">{{ $unreadCount }}</span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="notificationBtn" style="width: 360px; max-width: 90vw;">
                            <div class="px-3 py-2 border-bottom fw-bold">Thông báo</div>
                            <div style="max-height: 360px; overflow: auto;">
                                @forelse($topAnnouncements as $a)
                                    @php($isUnread = $userId && !in_array($a->id, $readIds, true))
                                    <a class="dropdown-item py-2" href="{{ route('announcements.show', $a->slug) }}">
                                        <div class="fw-bold" style="white-space: normal;">{{ $a->title }}</div>
                                        @if($a->summary)
                                            <div class="text-muted" style="font-size: 12px; white-space: normal;">{{ $a->summary }}</div>
                                        @endif
                                        @if($isUnread)
                                            <div class="text-primary" style="font-size: 12px;">Chưa đọc</div>
                                        @endif
                                    </a>
                                @empty
                                    <div class="px-3 py-3 text-muted">Chưa có thông báo.</div>
                                @endforelse
                            </div>
                            <div class="border-top">
                                <a class="dropdown-item text-center py-2" href="{{ route('teacher.notifications.manage.index') }}">Quản lý thông báo</a>
                            </div>
                        </div>
                    </div>
                    <div class="user-profile dropdown">
                        <button type="button" class="btn btn-link p-0 text-decoration-none user-name dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ $authDisplayName ?? (Auth::user()->email ?? 'GIÁO VIÊN') }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Thông tin cá nhân</a></li>
                            <li><a class="dropdown-item" href="{{ route('account.password.edit') }}"><i class="fas fa-key me-2"></i>Đổi mật khẩu</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <main class="admin-content">
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/admin.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
