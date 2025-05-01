{{-- Blade Template - Thông báo cập nhật chia sẻ doanh thu --}}
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập Nhật Doanh Thu - CourseMeLy</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body style="margin:0; padding:0; background-color:#faf7f5; font-family: 'Manrope', sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="background-color:#faf7f5; padding:30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="700" cellpadding="0" cellspacing="0"
                    style="background-color:#ffffff; border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,0.05); overflow:hidden;">

                    {{-- Header --}}
                    <tr>
                        <td align="center"
                            style="background: linear-gradient(135deg, #E27447, #f59776); padding:40px 20px 30px;">
                            <img src="https://res.cloudinary.com/dere3na7i/image/upload/c_thumb,w_200,g_face/v1740311680/logo-container_zi19ug.png"
                                alt="CourseMeLy Logo" width="70" height="70"
                                style="box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width:70px;">
                            <h1 style="color:#ffffff; margin-top:20px; font-size:28px; font-weight:600;">CourseMeLy</h1>
                            <p style="color:#ffffff; opacity:0.9; font-size:16px;">Nền tảng học trực tuyến hàng đầu</p>
                        </td>
                    </tr>

                    {{-- Nội dung chính --}}
                    <tr>
                        <td align="center" style="padding:40px 40px 20px;">
                            <h2 style="color:#333; font-size:24px; font-weight:600; margin:0;">Xin chào
                                {{ $user->name ?? 'Giảng viên' }},</h2>
                            <p style="color:#666; font-size:16px; line-height:1.6; margin-top:15px;">
                                Chúng tôi xin thông báo rằng <strong style="color:#E27447;">chính sách chia sẻ doanh
                                    thu</strong> của bạn tại <strong style="color:#E27447;">CourseMeLy</strong> đã được
                                <strong style="color:#E27447;">cập nhật</strong>.
                            </p>
                        </td>
                    </tr>

                    {{-- Thông báo thay đổi --}}
                    <tr>
                        <td align="center" style="padding:10px 40px 40px;">
                            <div
                                style="background-color:#fff6e8; border-radius:10px; padding:25px; border:1px solid #ffe5c3;">
                                <div
                                    style="width:70px; height:70px; background-color:#ffe5c3; border-radius:50%; line-height:70px; font-size:32px; margin:0 auto 15px;">
                                    📢</div>
                                <h3 style="margin:0 0 10px; color:#d86b1d; font-size:20px; font-weight:600;">Chia sẻ
                                    doanh thu mới</h3>
                                <p style="font-size:17px; color:#5f4b36; margin:0 0 15px;">Từ ngày
                                    <strong>{{ date('d/m/Y') }}</strong>, bạn sẽ nhận được:</p>
                                <div style="font-size:42px; font-weight:700; color:#E27447; margin:25px 0;">
                                    {{ $newSharePercentage ?? 60 }}%
                                </div>
                                <p style="font-size:17px; color:#5f4b36; margin:15px 0 0;">
                                    từ doanh thu bán khóa học trực tiếp. Phần còn lại sẽ được sử dụng để duy trì hệ
                                    thống, cải thiện nền tảng và hỗ trợ kỹ thuật.
                                </p>
                            </div>
                        </td>
                    </tr>

                    {{-- Hành động --}}
                    {{-- <tr>
                    <td align="center" style="padding:0 40px 30px;">
                        <a href="http://localhost:3000/instructor"
                           style="display:inline-block; background: linear-gradient(to right, #E27447, #f59776); color:#fff; padding:15px 35px; font-size:16px; text-decoration:none; border-radius:8px; font-weight:600; letter-spacing:0.5px; box-shadow:0 4px 10px rgba(226,116,71,0.3); transition: all 0.3s;">
                            ⚙️ XEM CHI TIẾT TRONG BẢNG ĐIỀU KHIỂN
                        </a>
                    </td>
                </tr> --}}

                    {{-- Ghi chú --}}
                    <tr>
                        <td style="padding:0 40px 30px;">
                            <div
                                style="background-color:#f1f5fe; border-radius:10px; padding:20px; border-left:4px solid #4e73df;">
                                <p style="font-size:15px; color:#3a5488; margin:0;">
                                    <strong>Lưu ý:</strong> Chính sách này sẽ có hiệu lực ngay lập tức và áp dụng cho
                                    tất cả các doanh thu mới. Vui lòng liên hệ với chúng tôi nếu bạn có thắc mắc.
                                </p>
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding:0 40px 20px;">
                            <p style="font-size:14px; color:#777; border-top:1px solid #eee; padding-top:20px;">
                                Nếu bạn có bất kỳ câu hỏi nào, vui lòng <a href="#"
                                    style="color:#E27447; text-decoration:none;">liên hệ với đội hỗ trợ giảng viên</a>.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="background-color:#fff8f5; padding:25px 30px; border-bottom-left-radius:12px; border-bottom-right-radius:12px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <p style="font-size:14px; color:#888; margin:0 0 15px;">&copy; 2025 CourseMeLy.
                                            Mọi quyền được bảo lưu.</p>
                                        <div>
                                            {{-- Các icon mạng xã hội placeholder --}}
                                            <a href="#"><img src="/api/placeholder/24/24" alt="Facebook"
                                                    style="margin:0 8px;"></a>
                                            <a href="#"><img src="/api/placeholder/24/24" alt="Instagram"
                                                    style="margin:0 8px;"></a>
                                            <a href="#"><img src="/api/placeholder/24/24" alt="LinkedIn"
                                                    style="margin:0 8px;"></a>
                                            <a href="#"><img src="/api/placeholder/24/24" alt="YouTube"
                                                    style="margin:0 8px;"></a>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                {{-- Note --}}
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="margin-top:20px;">
                    <tr>
                        <td align="center">
                            <p style="font-size:13px; color:#aaa; margin-top:20px;">
                                Email này được gửi tự động, vui lòng không trả lời. Cần hỗ trợ? Liên hệ <a
                                    href="mailto:coursemely@gmail.com"
                                    style="color:#E27447;">coursemely@gmail.com</a>
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>

</html>
