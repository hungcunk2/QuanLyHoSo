<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <title>@yield('title', 'Admin Panel') - IIUH</title>
    
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
                    <span class="logo-text">IIUH</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">QUẢN LÝ</div>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="{{ route('admin.students') }}" class="nav-link">
                                <i class="fas fa-user-graduate"></i>
                                <span>Quản Lý Học Sinh</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.teachers') }}" class="nav-link">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span>Quản Lý Giáo Viên</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.lops') }}" class="nav-link">
                                <i class="fas fa-users"></i>
                                <span>Quản Lý Lớp</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.subjects') }}" class="nav-link">
                                <i class="fas fa-book"></i>
                                <span>Quản Lý Môn Học</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.curriculum-terms.index') }}" class="nav-link">
                                <i class="fas fa-sitemap"></i>
                                <span>Quản Lý Chương Trình Khung</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.subject-registrations') }}" class="nav-link">
                                <i class="fas fa-clipboard-list"></i>
                                <span>Quản Lý Đăng Ký Học Phần</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.index') }}" class="nav-link">
                                <i class="fas fa-chart-pie"></i>
                                <span>Báo cáo / Thống kê</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.notifications.index') }}" class="nav-link">
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
                    <div class="user-profile d-flex align-items-center">
                        <span class="user-name">
                            {{ $authDisplayName ?? (Auth::user()->email ?? 'SUPER ADMIN') }}
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
