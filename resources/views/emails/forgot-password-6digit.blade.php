<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khôi phục mật khẩu</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5; color: #111;">
    <h2 style="margin:0 0 8px 0;">Khôi phục mật khẩu</h2>
    <p style="margin:0 0 10px 0;">
        Xin chào <strong>{{ $user->email }}</strong>,
    </p>
    <p style="margin:0 0 10px 0;">
        Hệ thống đã tạo mật khẩu mới cho tài khoản của bạn:
    </p>
    <div style="display:inline-block;padding:12px 16px;border:1px solid #ddd;border-radius:8px;background:#f8f9fa;margin:0 0 12px 0;">
        <div style="font-size:22px;letter-spacing:4px;font-weight:700;">{{ $password }}</div>
    </div>
    <p style="margin:0 0 10px 0;">
        Vui lòng đăng nhập và đổi mật khẩu ngay sau khi đăng nhập để đảm bảo an toàn.
    </p>
    <p style="margin:16px 0 0 0;color:#666;font-size:12px;">
        Nếu bạn không yêu cầu khôi phục mật khẩu, vui lòng liên hệ quản trị.
    </p>
</body>
</html>

