<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đại học Công nghiệp Thành phố Hồ Chí Minh - HDU</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <div class="logo-wrapper">
                        <i class="fas fa-graduation-cap"></i>
                        <span class="logo-text">HDU</span>
                    </div>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="#about">Giới thiệu</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#news">Tin tức</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#admission">Tuyển sinh</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#training">Đào tạo</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#contact">Liên hệ</a>
                        </li>
                    </ul>
                    <div class="header-actions ms-3">
                        <a href="#" class="btn btn-outline-primary btn-sm me-2">
                            <i class="fas fa-user"></i> Giảng viên
                        </a>
                        <a href="#" class="btn btn-outline-primary btn-sm me-2">
                            <i class="fas fa-envelope"></i> Email
                        </a>
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user-graduate"></i> Sinh viên
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-8" data-aos="fade-right">
                    <h1 class="hero-title">Chào mừng bạn đến với<br>Đại học Công nghiệp Thành phố Hồ Chí Minh</h1>
                    <p class="hero-subtitle">Với mục tiêu và tầm nhìn trở thành một trong những trường đại học hàng đầu Việt Nam, tiên phong trong giáo dục, nghiên cứu ứng dụng, chuyển giao công nghệ và đổi mới sáng tạo.</p>
                    <div class="hero-buttons">
                        <a href="#about" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-info-circle"></i> Tìm hiểu thêm
                        </a>
                        <a href="#admission" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-graduation-cap"></i> Tuyển sinh 2026
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 text-center" data-aos="fade-left">
                    <div class="hero-badges">
                        <div class="badge-item">
                            <i class="fas fa-trophy"></i>
                            <span>HDU 2026</span>
                        </div>
                        <div class="badge-item">
                            <i class="fas fa-star"></i>
                            <span>QS 2026</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-scroll">
            <a href="#about" class="scroll-down">
                <i class="fas fa-chevron-down"></i>
            </a>
        </div>
    </section>

    <!-- Core Values Section -->
    <section id="about" class="core-values-section py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                    <h2 class="section-title">Giá trị cốt lõi</h2>
                    <p class="section-subtitle">Trường Đại học Công nghiệp Thành phố Hồ Chí Minh xác định và đúc kết hệ giá trị cốt lõi: "Đổi mới – Đoàn kết – Nhân văn"</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="value-card">
                        <div class="value-icon innovation">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h3 class="value-title">Đổi mới</h3>
                        <p class="value-description">Liên tục tìm kiếm những cách làm mới và tốt hơn, sáng tạo, đổi mới và dám nghĩ, dám làm để thử những điều mới và tạo cơ hội để cải tiến và đạt đến tầm cao mới.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="value-card">
                        <div class="value-icon unity">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="value-title">Đoàn kết</h3>
                        <p class="value-description">Chịu trách nhiệm và làm chủ, đồng thời phối hợp làm việc với tất cả các đơn vị và các bên liên quan để khai thác triệt để các nguồn lực và năng lực.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="value-card">
                        <div class="value-icon humanity">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3 class="value-title">Nhân văn</h3>
                        <p class="value-description">Đối xử với từng cá nhân và tập thể bằng lòng nhân ái, vị tha, công bằng, công tâm và hướng đến phát triển tiềm năng và hiện thực hóa nguyện vọng.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="section-title">Về chúng tôi</h2>
                    <p class="about-text">Tiền thân của Đại học Công nghiệp Thành phố Hồ Chí Minh là Trường Huấn nghiệp Gò Vấp do các tu sĩ dòng Don Bosco thành lập 11/11/1956 tại xã Hạnh Thông.</p>
                    <p class="about-text">Bằng việc không ngừng đổi mới và nâng cao chất lượng giáo dục, cũng như cải thiện cơ sở vật chất phục vụ học tập, nghiên cứu và các hoạt động, HDU đã và đang khẳng định vị thế của mình trong hệ thống giáo dục đại học Việt Nam.</p>
                    <div class="stats-row mt-4">
                        <div class="stat-item">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Giảng đường</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">350+</div>
                            <div class="stat-label">Phòng thí nghiệm</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">50+</div>
                            <div class="stat-label">Năm kinh nghiệm</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="about-image">
                        <div class="image-placeholder">
                            <i class="fas fa-university"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section id="news" class="news-section py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                    <h2 class="section-title">Tin tức</h2>
                    <p class="section-subtitle">Cập nhật những thông tin mới nhất từ HDU</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="news-card">
                        <div class="news-badge">Mới</div>
                        <div class="news-image">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="news-content">
                            <div class="news-date">15/01/2026</div>
                            <h3 class="news-title">Nâng tầm chất lượng giảng dạy: Hội thi giảng viên dạy giỏi GDQP&AN - GDTC tại HDU</h3>
                            <p class="news-excerpt">Hội thi giảng viên dạy giỏi các môn Giáo dục quốc phòng và an ninh; Giáo dục thể chất năm học 2025-2026 được tổ chức nhằm đánh giá thực chất trình độ chuyên môn...</p>
                            <a href="#" class="news-link">Xem chi tiết <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="news-card">
                        <div class="news-badge">Mới</div>
                        <div class="news-image">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="news-content">
                            <div class="news-date">13/01/2026</div>
                            <h3 class="news-title">Nhiều sinh viên HDU đạt danh hiệu "Sinh viên 5 tốt"</h3>
                            <p class="news-excerpt">Hành trình trở thành "Sinh viên 5 tốt" là chặng đường dài của sự kiên trì, kỷ luật và nỗ lực vượt qua chính mình...</p>
                            <a href="#" class="news-link">Xem chi tiết <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="news-card">
                        <div class="news-image">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="news-content">
                            <div class="news-date">10/01/2026</div>
                            <h3 class="news-title">Nam sinh HDU trở thành thủ khoa ngành Công nghệ Kỹ thuật Máy tính</h3>
                            <p class="news-excerpt">Với niềm đam mê sáng tạo trong lĩnh vực công nghệ ứng dụng cùng khả năng tiếp thu nhanh, ghi nhớ tốt...</p>
                            <a href="#" class="news-link">Xem chi tiết <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Admission Section -->
    <section id="admission" class="admission-section py-5 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="section-title text-white">Tuyển sinh 2026</h2>
                    <p class="admission-text">Đăng ký ngay để trở thành sinh viên của Đại học Công nghiệp TP.HCM. Chúng tôi cam kết mang đến cho bạn môi trường học tập tốt nhất với đội ngũ giảng viên giàu kinh nghiệm và cơ sở vật chất hiện đại.</p>
                    <ul class="admission-features">
                        <li><i class="fas fa-check-circle"></i> Nhiều ngành đào tạo đa dạng</li>
                        <li><i class="fas fa-check-circle"></i> Học bổng hấp dẫn</li>
                        <li><i class="fas fa-check-circle"></i> Cơ hội việc làm cao</li>
                        <li><i class="fas fa-check-circle"></i> Môi trường học tập hiện đại</li>
                    </ul>
                    <a href="#" class="btn btn-light btn-lg mt-4">
                        <i class="fas fa-file-alt"></i> Đăng ký tuyển sinh
                    </a>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="admission-info-box">
                        <h3>Thông tin liên hệ tuyển sinh</h3>
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Hotline:</strong>
                                <p>028 3985 1932 - 028 3895 5858 - 028 3985 1917</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email:</strong>
                                <p>dhcn@hdu.edu.vn</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Địa chỉ:</strong>
                                <p>Số 12 Nguyễn Văn Bảo, P. Hạnh Thông, TP.HCM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="main-footer py-5 bg-dark text-white">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="footer-brand mb-4">
                        <div class="logo-wrapper">
                            <i class="fas fa-graduation-cap"></i>
                            <span class="logo-text">HDU</span>
                        </div>
                        <p class="mt-3">Đại học Công nghiệp Thành phố Hồ Chí Minh - Một trong những trường đại học hàng đầu Việt Nam.</p>
                    </div>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h5 class="footer-title">Thông tin liên hệ</h5>
                    <ul class="footer-list">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Số 12 Nguyễn Văn Bảo, P. Hạnh Thông, TP.HCM</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>0283 8940 390</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>dhcn@hdu.edu.vn</span>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="footer-title">Các cơ sở và phân hiệu</h5>
                    <ul class="footer-list">
                        <li><i class="fas fa-building"></i> Nguyễn Văn Dung</li>
                        <li><i class="fas fa-building"></i> Phạm Văn Chiêu</li>
                        <li><i class="fas fa-building"></i> Nhơn Trạch</li>
                        <li><i class="fas fa-building"></i> Thanh Hóa</li>
                        <li><i class="fas fa-building"></i> Quảng Ngãi</li>
                    </ul>
                </div>
            </div>
            <hr class="footer-divider my-4">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="footer-copyright mb-0">© 2025 Đại học Công nghiệp TP.HCM - HDU. All rights reserved.</p>
                    <p class="mt-2 mb-0">
                        <small>Số lượng truy cập: 288,618,885 | Đang online: <strong>160</strong></small>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/landing.js') }}"></script>
</body>
</html>
