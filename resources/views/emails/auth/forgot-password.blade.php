<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lại Mật Khẩu - CourseMeLy</title>
</head>
<body
    style="margin:0; padding:0; background-color:#faf7f5; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"
       style="background-color:#faf7f5; padding:30px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0"
                   style="background-color:#ffffff; padding:0; border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,0.05); overflow:hidden;">

                <!-- Header with gradient background -->
                <tr>
                    <td align="center"
                        style="background: linear-gradient(135deg, #E27447, #f59776); padding:40px 20px 30px;">
                        <img
                            src="https://res.cloudinary.com/dere3na7i/image/upload/c_thumb,w_200,g_face/v1740311680/logo-container_zi19ug.png"
                            alt="CourseMeLy Logo" width="70" height="70"
                            style="box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width:70px; object-fit:contain;">
                        <h1 style="color:#ffffff; margin-top:20px; font-size:28px; font-weight:600; letter-spacing:0.5px;">
                            CourseMeLy</h1>
                        <p style="color:#ffffff; opacity:0.9; margin:5px 0 0; font-size:16px;">Đặt Lại Mật Khẩu</p>
                    </td>
                </tr>

                <!-- Greeting section -->
                <tr>
                    <td align="center" style="padding:40px 40px 20px;">
                        <h2 style="color:#333; margin:0; font-size:24px; font-weight:600;">Xin
                            chào {{$user->name ?? 'Bạn'}},</h2>
                        <p style="color:#666; font-size:16px; line-height:1.6; margin-top:15px;">
                            Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản <strong
                                style="color:#E27447;">CourseMeLy</strong> của bạn. Vui lòng sử dụng liên kết dưới đây
                            để tạo mật khẩu mới.
                        </p>
                    </td>
                </tr>

                <!-- Security info section -->
                <tr>
                    <td style="padding:0 40px 30px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                               style="background-color:#fff8f5; border-radius:10px; padding:20px;">
                            <tr>
                                <td style="padding-bottom:15px;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="40" valign="top">
                                                <div
                                                    style="width:40px; height:40px; background-color:#ffeee8; border-radius:50%; display:inline-block; text-align:center; line-height:40px; font-size:18px; color:#E27447;">
                                                    🔒
                                                </div>
                                            </td>
                                            <td style="padding-left:15px;">
                                                <p style="margin:0; color:#444; font-size:16px; font-weight:500;">Thông
                                                    tin bảo mật</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <ul style="margin:0; padding-left:20px; color:#666;">
                                        <li style="margin-bottom:10px;">Liên kết này sẽ hết hạn sau <strong>30
                                                phút</strong>.
                                        </li>
                                        <li style="margin-bottom:10px;">Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng
                                            bỏ qua email này.
                                        </li>
                                        <li>Nếu gặp vấn đề, vui lòng liên hệ đội ngũ hỗ trợ của chúng tôi.</li>
                                    </ul>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Call to action button -->
                <tr>
                    <td align="center" style="padding:10px 40px 40px;">
                        <a href="{{$verificationUrl ?? '#'}}"
                           style="display:inline-block; background: linear-gradient(to right, #E27447, #f59776); color:#fff; padding:15px 35px; font-size:16px; text-decoration:none; border-radius:8px; font-weight:600; letter-spacing:0.5px; box-shadow:0 4px 10px rgba(226,116,71,0.3); transition: all 0.3s;">
                            🔑 ĐẶT LẠI MẬT KHẨU
                        </a>
                        <p style="font-size:14px; color:#999; margin-top:20px;">
                            Nếu nút không hoạt động, bạn có thể sao chép và dán liên kết sau vào trình duyệt:
                        </p>
                        <p style="font-size:12px; color:#666; margin-top:5px; word-break:break-all; background-color:#f9f9f9; padding:10px; border-radius:5px; max-width:400px;">
                            {{$verificationUrl ?? 'https://coursemely.com/reset-password?token=example123456789'}}
                        </p>
                    </td>
                </tr>

                <!-- Additional info -->
                <tr>
                    <td align="center" style="padding:0 40px 20px;">
                        <p style="font-size:14px; color:#777; border-top:1px solid #eee; padding-top:20px;">
                            Nếu bạn không thực hiện yêu cầu này, vui lòng <a href="#"
                                                                             style="color:#E27447; text-decoration:none;">báo
                                cáo hoạt động đáng ngờ</a> cho chúng tôi.
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background-color:#fff8f5; padding:25px 30px; border-bottom-left-radius:12px; border-bottom-right-radius:12px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center">
                                    <p style="font-size:14px; color:#888; margin:0 0 15px;">&copy; 2025 CourseMeLy. Mọi
                                        quyền được bảo lưu.</p>
                                    <div>
                                        <a href="#" style="display:inline-block; margin:0 8px;"><img
                                                src="/api/placeholder/24/24" alt="Facebook"
                                                style="width:24px; height:24px; border-radius:4px;"></a>
                                        <a href="#" style="display:inline-block; margin:0 8px;"><img
                                                src="/api/placeholder/24/24" alt="Instagram"
                                                style="width:24px; height:24px; border-radius:4px;"></a>
                                        <a href="#" style="display:inline-block; margin:0 8px;"><img
                                                src="/api/placeholder/24/24" alt="LinkedIn"
                                                style="width:24px; height:24px; border-radius:4px;"></a>
                                        <a href="#" style="display:inline-block; margin:0 8px;"><img
                                                src="/api/placeholder/24/24" alt="YouTube"
                                                style="width:24px; height:24px; border-radius:4px;"></a>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- Support contact info -->
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="margin-top:20px;">
                <tr>
                    <td align="center">
                        <p style="font-size:14px; color:#999; margin-bottom:10px;">Cần trợ giúp?</p>
                        <p style="font-size:13px; color:#aaa; margin:0;">
                            Email hỗ trợ: <a href="mailto:coursemely@gmail.com"
                                             style="color:#E27447; text-decoration:none;">coursemely@gmail.com</a> |
                            Hotline: <a href="tel:+84123456789" style="color:#E27447; text-decoration:none;">0123 456
                                789</a>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
