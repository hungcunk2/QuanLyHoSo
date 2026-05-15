<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <title>@yield('title', 'Học Sinh') - IIUH</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/ai-chatbox.css') }}">
    
    @stack('styles')
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <img src="{{ asset('images/logo.png') }}" alt="IIUH" style="height: 52px; width: auto;">
                    <span class="logo-text">Học Sinh</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">MAIN</div>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="{{ route('student.dashboard') }}" class="nav-link">
                                <i class="fas fa-gauge"></i>
                                <span>Bảng điều khiển</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('student.profile') }}" class="nav-link">
                                <i class="fas fa-user"></i>
                                <span>Thông tin cá nhân</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">CHỨC NĂNG</div>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="{{ route('student.schedule') }}" class="nav-link">
                                <i class="fas fa-book"></i>
                                <span>Lịch Học</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('student.results') }}" class="nav-link">
                                <i class="fas fa-chart-line"></i>
                                <span>Kết Quả Học Tập</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('student.registration') }}" class="nav-link">
                                <i class="fas fa-clipboard-list"></i>
                                <span>Đăng Ký Học Phần</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('student.curriculum') }}" class="nav-link">
                                <i class="fas fa-sitemap"></i>
                                <span>Chương Trình Khung</span>
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
                <div class="header-left d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="mobile-menu-toggle btn btn-sm btn-outline-secondary" id="mobileMenuToggle" aria-label="Mở menu" aria-expanded="false">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title mb-0">@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="header-right">
                    <button class="header-icon-btn theme-toggle" id="themeToggle">
                        <i class="fas fa-sun"></i>
                    </button>
                    @php
                        $userId = (int) (Auth::id() ?? 0);
                        $student = \App\Models\Student::where('email', Auth::user()->email)->first();
                        $offeringIds = $student
                            ? \App\Models\SubjectRegistration::query()
                                ->where('student_id', $student->id)
                                ->where('status', '!=', 'cancelled')
                                ->whereNotNull('course_offering_id')
                                ->pluck('course_offering_id')
                                ->unique()
                                ->values()
                                ->all()
                            : [];

                        $targetedIds = !empty($offeringIds)
                            ? \Illuminate\Support\Facades\DB::table('announcement_offering_targets')
                                ->whereIn('course_offering_id', $offeringIds)
                                ->pluck('announcement_id')
                                ->unique()
                                ->values()
                                ->all()
                            : [];

                        $topAnnouncements = \App\Models\Announcement::query()
                            ->where(function ($q) use ($targetedIds) {
                                $q->where('audience', 'all')
                                    ->orWhere(function ($sq) use ($targetedIds) {
                                        $sq->where('audience', 'student');
                                        if (!empty($targetedIds)) {
                                            $sq->whereIn('id', $targetedIds);
                                        } else {
                                            // nếu không có lớp, chỉ lấy thông báo "all"
                                            $sq->whereRaw('1=0');
                                        }
                                    });
                            })
                            ->orderByDesc('created_at')
                            ->limit(6)
                            ->get();

                        $readIds = $userId
                            ? \Illuminate\Support\Facades\DB::table('announcement_reads')
                                ->where('user_id', $userId)
                                ->pluck('announcement_id')
                                ->all()
                            : [];

                        $unreadCount = 0;
                        if ($userId) {
                            $visibleIds = \App\Models\Announcement::query()
                                ->where(function ($q) use ($targetedIds) {
                                    $q->where('audience', 'all')
                                        ->orWhere(function ($sq) use ($targetedIds) {
                                            $sq->where('audience', 'student');
                                            if (!empty($targetedIds)) {
                                                $sq->whereIn('id', $targetedIds);
                                            } else {
                                                $sq->whereRaw('1=0');
                                            }
                                        });
                                })
                                ->pluck('id')
                                ->all();
                            $unreadCount = count(array_diff($visibleIds, $readIds));
                        }
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
                                <a class="dropdown-item text-center py-2" href="{{ route('student.notifications') }}">Xem tất cả</a>
                            </div>
                        </div>
                    </div>
                    <div class="user-profile d-flex align-items-center">
                        <span class="user-name">
                            {{ $authDisplayName ?? (Auth::user()->email ?? 'HỌC SINH') }}
                        </span>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <main class="admin-content">
                @yield('content')
            </main>
        </div>
    </div>
    @include('partials.ai-chatbox')

    @stack('modals')

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/admin.js') }}?v={{ @filemtime(public_path('js/admin.js')) }}"></script>
    <script src="{{ asset('js/ai-chatbox.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
