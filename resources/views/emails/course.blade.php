<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khóa Học Đang Chờ Duyệt - CourseMeLy</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body
    style="margin:0; padding:0; background-color:#faf7f5; font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"
       style="background-color:#faf7f5; padding:30px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="700" cellpadding="0" cellspacing="0"
                   style="background-color:#ffffff; padding:0; border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,0.05); overflow:hidden;">

                <tr>
                    <td align="center"
                        style="background: linear-gradient(135deg, #E27447, #f59776); padding:40px 20px 30px;">
                        <img
                            src="https://res.cloudinary.com/dere3na7i/image/upload/c_thumb,w_200,g_face/v1740311680/logo-container_zi19ug.png"
                            alt="CourseMeLy Logo" width="70" height="70"
                            style="box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width:70px; object-fit:contain;">
                        <h1 style="color:#ffffff; margin-top:20px; font-size:28px; font-weight:600; letter-spacing:0.5px;">
                            CourseMeLy</h1>
                        <p style="color:#ffffff; opacity:0.9; margin:5px 0 0; font-size:16px;">Nền tảng học trực tuyến
                            hàng đầu</p>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:40px 40px 20px;">
                        <h2 style="color:#333; margin:0; font-size:24px; font-weight:600;">Khóa học đã được chấp
                            nhận!</h2>
                        <p style="color:#666; font-size:16px; line-height:1.6; margin-top:15px;">
                            Chúng tôi rất vui mừng thông báo rằng khóa học <strong
                                style="color:#E27447;">{{ $course->name }}</strong> của bạn đã được chấp nhận và hiện
                            đang trong quá trình xét duyệt cuối cùng.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 40px 30px;">
                        <div style="background-color:#fff8f5; border-radius:10px; padding:25px; margin-bottom:20px;">
                            <h3 style="color:#E27447; margin:0 0 15px; font-size:18px;">Thông tin khóa học:</h3>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="30%" style="padding:10px 0; border-bottom:1px solid #f0e0db;">
                                        <p style="margin:0; color:#666; font-weight:600;">Tên khóa học:</p>
                                    </td>
                                    <td style="padding:10px 0; border-bottom:1px solid #f0e0db;">
                                        <p style="margin:0; color:#333;">{{ $course->name }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="30%" style="padding:10px 0; border-bottom:1px solid #f0e0db;">
                                        <p style="margin:0; color:#666; font-weight:600;">Ngày gửi:</p>
                                    </td>
                                    <td style="padding:10px 0; border-bottom:1px solid #f0e0db;">
                                        <p style="margin:0; color:#333;">{{ $course->created_at ? $course->created_at->format('d-m-Y H:i') : '' }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="30%" style="padding:10px 0; border-bottom:1px solid #f0e0db;">
                                        <p style="margin:0; color:#666; font-weight:600;">Trạng thái:</p>
                                    </td>
                                    <td style="padding:10px 0; border-bottom:1px solid #f0e0db;">
                                        <p style="margin:0;"><span
                                                style="color:#E27447; font-weight:600; background-color:#ffeee8; padding:5px 10px; border-radius:20px; font-size:14px;">Đang chờ duyệt</span>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 40px 30px;">
                        <div style="background-color:#fff8f5; border-radius:10px; padding:25px; margin-bottom:20px;">
                            <h3 style="color:#E27447; margin:0 0 15px; font-size:18px;">Mô tả khóa học:</h3>
                            <p style="margin:0; color:#444; line-height:1.6; font-size:15px;">{{ $course->description }}</p>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 40px 30px;">
                        <p style="font-size:18px; font-weight:600; color:#333; margin-bottom:20px;">
                            <span style="color:#E27447;">✨</span> Các bước tiếp theo:
                        </p>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding:15px; background-color:#fff8f5; border-radius:10px; margin-bottom:15px; border-left:3px solid #E27447;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="40" valign="top">
                                                <div
                                                    style="width:40px; height:40px; background-color:#ffeee8; border-radius:50%; display:inline-block; text-align:center; line-height:40px; font-size:18px; color:#E27447;">
                                                    1
                                                </div>
                                            </td>
                                            <td style="padding-left:15px;">
                                                <p style="margin:0; color:#444; font-size:16px; font-weight:500;">Đội
                                                    ngũ chuyên gia của chúng tôi sẽ đánh giá nội dung khóa học của
                                                    bạn</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="15"></td>
                            </tr>
                            <tr>
                                <td style="padding:15px; background-color:#fff8f5; border-radius:10px; margin-bottom:15px; border-left:3px solid #E27447;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="40" valign="top">
                                                <div
                                                    style="width:40px; height:40px; background-color:#ffeee8; border-radius:50%; display:inline-block; text-align:center; line-height:40px; font-size:18px; color:#E27447;">
                                                    2
                                                </div>
                                            </td>
                                            <td style="padding-left:15px;">
                                                <p style="margin:0; color:#444; font-size:16px; font-weight:500;">Bạn sẽ
                                                    nhận được thông báo khi khóa học được duyệt hoặc cần chỉnh sửa</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="15"></td>
                            </tr>
                            <tr>
                                <td style="padding:15px; background-color:#fff8f5; border-radius:10px; border-left:3px solid #E27447;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="40" valign="top">
                                                <div
                                                    style="width:40px; height:40px; background-color:#ffeee8; border-radius:50%; display:inline-block; text-align:center; line-height:40px; font-size:18px; color:#E27447;">
                                                    3
                                                </div>
                                            </td>
                                            <td style="padding-left:15px;">
                                                <p style="margin:0; color:#444; font-size:16px; font-weight:500;">Sau
                                                    khi được duyệt, khóa học của bạn sẽ được đưa lên nền tảng
                                                    CourseMeLy</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:10px 40px 40px;">
                        <div style="background-color:#fff8f5; border-radius:10px; padding:25px; text-align:center;">
                            <p style="font-size:16px; color:#555; margin-bottom:20px;">Kiểm tra trạng thái khóa học của
                                bạn:</p>
                            <a href="#"
                               style="display:inline-block; background: linear-gradient(to right, #E27447, #f59776); color:#fff; padding:15px 35px; font-size:16px; text-decoration:none; border-radius:8px; font-weight:600; letter-spacing:0.5px; box-shadow:0 4px 10px rgba(226,116,71,0.3); transition: all 0.3s;">
                                🔍 XEM TRẠNG THÁI
                            </a>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:0 40px 20px;">
                        <p style="font-size:14px; color:#777; border-top:1px solid #eee; padding-top:20px;">
                            Nếu bạn có bất kỳ câu hỏi nào, vui lòng <a href="#"
                                                                       style="color:#E27447; text-decoration:none;">liên
                                hệ với đội ngũ hỗ trợ</a> của chúng tôi.
                        </p>
                    </td>
                </tr>

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

            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="margin-top:20px;">
                <tr>
                    <td align="center">
                        <p style="font-size:13px; color:#aaa; margin-top:20px;">
                            Email này được gửi tự động, vui lòng không trả lời. Nếu bạn cần hỗ trợ, vui lòng liên hệ <a
                                href="mailto:coursemely@gmail.com" style="color:#E27447; text-decoration:none;">coursemely@gmail.com</a>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
