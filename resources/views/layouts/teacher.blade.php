<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Giáo Viên') - HDU</title>
    
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
                    <i class="fas fa-chalkboard-teacher text-success"></i>
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
                            <a href="{{ route('teacher.dashboard') }}" class="nav-link">
                                <i class="fas fa-user"></i>
                                <span>Thông Tin Cá Nhân</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('teacher.dashboard') }}" class="nav-link">
                                <i class="fas fa-school"></i>
                                <span>Lớp Học Của Tôi</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('teacher.dashboard') }}" class="nav-link">
                                <i class="fas fa-user-graduate"></i>
                                <span>Học Sinh</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('teacher.dashboard') }}" class="nav-link">
                                <i class="fas fa-clipboard-list"></i>
                                <span>Chấm Điểm</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('teacher.dashboard') }}" class="nav-link">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Lịch Dạy</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">SYSTEM</div>
                    <ul class="nav-menu">
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
                <p class="copyright">© 2025 HDU Connect – All rights reserved</p>
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
                    <button class="header-icon-btn notification-btn" id="notificationBtn">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </button>
                    <div class="user-profile dropdown">
                        <div class="user-avatar dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <span class="user-name">{{ Auth::user()->email ?? 'GIÁO VIÊN' }}</span>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Thông tin cá nhân</a></li>
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
