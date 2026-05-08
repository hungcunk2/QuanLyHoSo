<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mật khẩu đã được đổi</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5; color: #111;">
    <h2 style="margin:0 0 8px 0;">Mật khẩu của bạn đã được đổi</h2>
    <p style="margin:0 0 10px 0;">
        Xin chào <strong>{{ $teacher->ho_ten }}</strong>,
    </p>
    <p style="margin:0 0 10px 0;">
        Quản trị viên đã đổi mật khẩu cho tài khoản giáo viên <strong>{{ $teacher->msgv }}</strong>.
    </p>
    <p style="margin:0 0 10px 0;">Mật khẩu mới của bạn là:</p>
    <div style="display:inline-block;padding:12px 16px;border:1px solid #ddd;border-radius:8px;background:#f8f9fa;margin:0 0 12px 0;">
        <div style="font-size:22px;letter-spacing:4px;font-weight:700;">{{ $password }}</div>
    </div>
    <p style="margin:0 0 10px 0;">
        Vui lòng đăng nhập và đổi mật khẩu ngay sau khi đăng nhập để đảm bảo an toàn.
    </p>
    <p style="margin:16px 0 0 0;color:#666;font-size:12px;">
        Nếu bạn không nhận ra thay đổi này, vui lòng liên hệ quản trị.
    </p>
</body>
</html>

