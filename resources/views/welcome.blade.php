<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <title>Đại học Công nghiệp Thành phố Hồ Chí Minh - IIUH</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        html, body { height: 100%; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #0b1220;
            overflow-x: hidden;
        }
        .auth-bg {
            min-height: 100vh;
            background-image:
                linear-gradient(0deg, rgba(6, 10, 18, .35), rgba(6, 10, 18, .35)),
                url("{{ asset('images/iuh-campus.png') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            position: relative;
            --news-panel-h: 700px;
            --login-panel-h: 540px;
            --login-w: 520px;
            --login-shift-x: clamp(96px, 11vw, 180px);
        }
        .auth-shell {
            width: 100%;
            padding: 24px;
        }
        .auth-grid {
            display: flex;
            justify-content: flex-end;
        }
        .login-news-wrap {
            position: absolute;
            left: 100px;
            top: 50%;
            transform: translateY(-50%);
            width: min(920px, calc(100vw - 100px - 24px - var(--login-w) - 26px));
            z-index: 2;
        }
        .login-news {
            max-width: 920px;
            color: #fff;
            margin-left: 0;
            font-family: Arial, Helvetica, sans-serif;
        }
        .login-news,
        .login-news * {
            font-family: Arial, Helvetica, sans-serif !important;
        }
        .auth-card-wrap {
            flex: 0 0 auto;
            display: flex;
            justify-content: flex-end;
            width: min(100%, var(--login-w));
            max-width: 100%;
            transform: translateX(var(--login-shift-x));
        }
        .login-news__panel {
            background: rgba(15, 23, 42, 0.34);
            border: 1px solid rgba(255,255,255,0.20);
            border-radius: 12px;
            overflow: hidden;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
            height: var(--news-panel-h);
            display: flex;
            flex-direction: column;
        }
        .login-news__panel,
        .login-news__header,
        .login-news__list,
        .login-news__item,
        .login-news__date,
        .login-news__content,
        .login-news__title,
        .login-news__more,
        .login-news__headline,
        .login-news__desc,
        .login-news__link {
            font-family: Arial, Helvetica, sans-serif !important;
            letter-spacing: 0;
        }
        .login-news__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            background: rgba(2, 6, 23, 0.52);
            border-bottom: 1px solid rgba(255,255,255,0.16);
        }
        .login-news__title {
            font-weight: 700;
            letter-spacing: 0;
            text-transform: none;
            font-size: 16px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .login-news__title-badge {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.18);
        }
        .login-news__more {
            font-size: 15px;
            font-weight: 700;
            color: #ffedd5;
            text-decoration: none;
            text-transform: none;
        }
        .login-news__more:hover { text-decoration: underline; }
        .login-news__list {
            max-height: none;
            flex: 1 1 auto;
            overflow: auto;
            padding: 6px 0;
        }
        .login-news__item {
            display: flex;
            gap: 16px;
            padding: 18px 22px;
            border-bottom: 1px solid rgba(255,255,255,0.14);
        }
        .login-news__item:last-child { border-bottom: 0; }
        .login-news__date {
            width: 86px;
            flex: 0 0 86px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.22);
            background: rgba(255,255,255,0.10);
        }
        .login-news__month {
            background: rgba(37, 99, 235, 0.88);
            color: #fff;
            font-size: 12.5px;
            font-weight: 700;
            text-align: center;
            padding: 6px 0;
            line-height: 1;
        }
        .login-news__day {
            color: #fff;
            font-size: 32px;
            font-weight: 700;
            text-align: center;
            padding: 16px 0 14px;
            line-height: 1;
        }
        .login-news__content { min-width: 0; }
        .login-news__headline {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            line-height: 1.25;
        }
        .login-news__desc {
            margin: 4px 0 0;
            font-size: 14.5px;
            font-weight: 400;
            color: rgba(255,255,255,0.82);
            line-height: 1.35;
        }
        .login-news__link {
            display: inline-block;
            margin-top: 6px;
            font-size: 14px;
            font-weight: 700;
            color: #bfdbfe;
            text-decoration: none;
        }
        .login-news__link:hover { text-decoration: underline; }

        .auth-card {
            width: var(--login-w);
            max-width: none;
            margin-left: auto;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.65);
            border-radius: 12px;
            box-shadow: 0 18px 50px rgba(0,0,0,.25);
            overflow: hidden;
            height: var(--login-panel-h);
            display: flex;
            flex-direction: column;
        }
        .auth-card__header {
            padding: 20px 22px 14px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            text-align: center;
        }
        .auth-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: #0f172a;
        }
        .auth-logo img { height: 38px; width: auto; }
        .auth-title {
            margin: 10px 0 0;
            font-size: 14px;
            font-weight: 700;
            color: #2563eb;
            letter-spacing: .02em;
            text-transform: uppercase;
        }
        .auth-card__body {
            padding: 18px 22px 22px;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .form-control { height: 46px; font-size: 14px; }
        .btn-login {
            height: 46px;
            background: #f97316;
            border-color: #f97316;
            font-weight: 700;
            font-size: 14px;
        }
        .btn-login:hover { background: #ea580c; border-color: #ea580c; }
        .muted-note { font-size: 12px; color: #64748b; }

        @media (max-width: 991.98px) {
            .auth-grid { flex-direction: column; align-items: stretch; }
            .login-news-wrap {
                position: static;
                width: auto;
                transform: none;
                margin-bottom: 14px;
            }
            .login-news { max-width: none; margin-left: 0; margin-right: 0; }
            .auth-card { width: 100%; max-width: none; margin-left: 0; }
            .login-news__list { max-height: 340px; }
            .auth-shell { padding: 18px 12px; }
            .auth-bg { --login-shift-x: 0px; }
        }
    </style>
</head>
<body>
    <div class="auth-bg">
        <div class="login-news-wrap">
            @php
                $loginAnnouncements = \App\Models\Announcement::query()
                    ->where(function ($q) {
                        // Trang đăng nhập chỉ hiển thị thông báo do Admin tạo (hoặc dữ liệu cũ chưa có created_by_user_id)
                        $adminIds = \App\Models\User::query()
                            ->where('role', 'admin')
                            ->pluck('id')
                            ->all();
                        $q->whereNull('created_by_user_id')
                          ->orWhereIn('created_by_user_id', $adminIds);
                    })
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get();
                $newsMoreUrl = route('announcements.index');
            @endphp

            <div class="login-news">
                <div class="login-news__panel">
                    <div class="login-news__header">
                        <h2 class="login-news__title">
                            <span class="login-news__title-badge" aria-hidden="true"></span>
                            <span>Tin tức - Sự kiện</span>
                        </h2>
                        <a class="login-news__more" href="{{ $newsMoreUrl }}">XEM THÊM</a>
                    </div>
                    <div class="login-news__list">
                        @foreach($loginAnnouncements as $a)
                            @php
                                $dt = $a->published_at ? \Carbon\Carbon::parse($a->published_at) : \Carbon\Carbon::parse($a->created_at);
                                $day = $dt->format('d');
                                $month = 'Tháng ' . $dt->format('m');
                            @endphp
                            <div class="login-news__item">
                                <div class="login-news__date" aria-hidden="true">
                                    <div class="login-news__month">{{ $month }}</div>
                                    <div class="login-news__day">{{ $day }}</div>
                                </div>
                                <div class="login-news__content">
                                    <p class="login-news__headline">{{ $a->title }}</p>
                                    @if(!empty($a->summary))
                                        <p class="login-news__desc">{{ $a->summary }}</p>
                                    @endif
                                    <a class="login-news__link" href="{{ route('announcements.show', $a->slug) }}">Xem chi tiết</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="container auth-shell">
            <div class="auth-grid">
                <div class="auth-card-wrap">
                    <div class="auth-card">
                    <div class="auth-card__header">
                        <div class="auth-logo">
                            <span>Cổng thông tin sinh viên</span>
                        </div>
                        <div class="auth-title">Đăng nhập hệ thống</div>
                    </div>
                    <div class="auth-card__body">
                        @if (session('status'))
                            <div class="alert alert-success py-2" role="alert">{{ session('status') }}</div>
                        @endif
                    @if ($errors->any())
                        <div class="alert alert-danger py-2" role="alert">
                            Thông tin tài khoản, mật khẩu không đúng. Vui lòng sử dụng chức năng quên mật khẩu.
                        </div>
                    @endif

                        <form method="POST" action="{{ route('login') }}" autocomplete="on">
                            @csrf
                            <div class="mb-3">
                                <input
                                    type="text"
                                    class="form-control"
                                    id="loginUsername"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="Nhập tài khoản"
                                    required
                                    autofocus
                                    autocomplete="username"
                                >
                            </div>
                            <div class="mb-3">
                                <input
                                    type="password"
                                    class="form-control"
                                    name="password"
                                    placeholder="Nhập mật khẩu"
                                    required
                                    autocomplete="current-password"
                                >
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                                    <span class="form-check-label">Ghi nhớ</span>
                                </label>
                                @if (Route::has('password.request'))
                                    <a class="muted-note text-decoration-none" href="{{ url('/?forgot=1') }}">Quên mật khẩu?</a>
                                @endif
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-login text-white">ĐĂNG NHẬP</button>
                            </div>
                        </form>
                    </div>
                </div>
                    </div>
            </div>
        </div>
    </div>

    <!-- Forgot password modal -->
    <div class="modal fade" id="forgotModal" tabindex="-1" aria-labelledby="forgotModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotModalLabel">Quên mật khẩu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-2 text-muted" style="font-size: 13px;">
                            Nhập email để nhận mật khẩu mới (6 chữ số).
                        </div>
                        <input
                            type="email"
                            class="form-control"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Nhập email"
                            required
                            autocomplete="email"
                        >
                        @if($errors->forgot->has('email'))
                            <div class="text-danger mt-2" style="font-size: 13px;">
                                {{ $errors->forgot->first('email') }}
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Gửi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            // Remember username on this device (do NOT store password)
            try {
                var userEl = document.getElementById('loginUsername');
                var rememberEl = document.getElementById('rememberMe');
                if (userEl && rememberEl) {
                    var key = 'qlhs_login_username';
                    var saved = localStorage.getItem(key);
                    if (!userEl.value && saved) {
                        userEl.value = saved;
                    }
                    if (saved) {
                        rememberEl.checked = true;
                    }
                    rememberEl.addEventListener('change', function () {
                        if (!rememberEl.checked) {
                            localStorage.removeItem(key);
                        } else if (userEl.value) {
                            localStorage.setItem(key, userEl.value);
                        }
                    });
                    userEl.addEventListener('input', function () {
                        if (rememberEl.checked) {
                            localStorage.setItem(key, userEl.value);
                        }
                    });
                }
            } catch (e) {}

            var params = new URLSearchParams(window.location.search || '');
            var shouldOpen = params.get('forgot') === '1';
            var hasEmailError = {{ $errors->forgot->has('email') ? 'true' : 'false' }};

            if (!shouldOpen && !hasEmailError) return;
            var el = document.getElementById('forgotModal');
            if (!el || !window.bootstrap) return;
            var modal = window.bootstrap.Modal.getOrCreateInstance(el);
            modal.show();

            if (shouldOpen) {
                params.delete('forgot');
                var qs = params.toString();
                var newUrl = window.location.pathname + (qs ? ('?' + qs) : '') + window.location.hash;
                window.history.replaceState({}, '', newUrl);
            }
        })();
    </script>
</body>
</html>
