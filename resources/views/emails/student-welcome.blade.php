<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chào mừng học sinh mới</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #4CAF50;
            border-radius: 4px;
        }
        .info-item {
            margin: 10px 0;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #777;
            font-size: 12px;
            border-top: 1px solid #ddd;
            margin-top: 20px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .login-box {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .login-credentials {
            background-color: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .password {
            font-size: 24px;
            font-weight: bold;
            color: #d32f2f;
            letter-spacing: 3px;
            text-align: center;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Chào mừng bạn đến với hệ thống!</h1>
    </div>
    
    <div class="content">
        <p>Xin chào <strong>{{ $student->ho_ten }}</strong>,</p>
        
        <p>Chúng tôi rất vui mừng thông báo rằng tài khoản của bạn đã được tạo thành công trong hệ thống quản lý hồ sơ.</p>
        
        <div class="info-box">
            <h3 style="margin-top: 0; color: #4CAF50;">Thông tin tài khoản của bạn:</h3>
            <div class="info-item">
                <span class="info-label">Mã số học sinh:</span> {{ $student->mssv }}
            </div>
            <div class="info-item">
                <span class="info-label">Họ và tên:</span> {{ $student->ho_ten }}
            </div>
            @if($student->email)
            <div class="info-item">
                <span class="info-label">Email:</span> {{ $student->email }}
            </div>
            @endif
            @if($student->lop)
            <div class="info-item">
                <span class="info-label">Lớp:</span> {{ $student->lop }}
            </div>
            @endif
        </div>
        
        @if($password)
        <div class="login-box">
            <h3 style="margin-top: 0; color: #d32f2f;">🔐 Thông tin đăng nhập:</h3>
            <div class="login-credentials">
                <div class="info-item">
                    <span class="info-label">Tài khoản (Username):</span> <strong>{{ $student->mssv }}</strong>
                </div>
                <div class="info-item">
                    <span class="info-label">Mật khẩu:</span>
                </div>
                <div class="password">{{ $password }}</div>
            </div>
            <p style="margin-top: 15px; font-size: 14px; color: #d32f2f;">
                <strong>⚠️ Lưu ý:</strong> Vui lòng đổi mật khẩu sau khi đăng nhập lần đầu để bảo mật tài khoản của bạn.
            </p>
        </div>
        @endif
        
        <p>Vui lòng đăng nhập vào hệ thống để cập nhật các thông tin còn lại như số điện thoại, ngày sinh, địa chỉ và thông tin phụ huynh.</p>
        
        <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi.</p>
        
        <p>Trân trọng,<br>
        <strong>Ban quản lý hệ thống</strong></p>
    </div>
    
    <div class="footer">
        <p>Email này được gửi tự động từ hệ thống quản lý hồ sơ.</p>
        <p>Vui lòng không trả lời email này.</p>
    </div>
</body>
</html>
