<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu Cầu Kiểm Duyệt Khóa Học - CourseMeLy</title>
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
                            <img src="https://res.cloudinary.com/dere3na7i/image/upload/c_thumb,w_200,g_face/v1740311680/logo-container_zi19ug.png"
                                alt="CourseMeLy Logo" width="70" height="70"
                                style="box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width:70px; object-fit:contain;">
                            <h1
                                style="color:#ffffff; margin-top:20px; font-size:28px; font-weight:600; letter-spacing:0.5px;">
                                CourseMeLy</h1>
                            <p style="color:#ffffff; opacity:0.9; margin:5px 0 0; font-size:16px;">Nền tảng học trực
                                tuyến
                                hàng đầu</p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:40px 40px 20px;">
                            <h2 style="color:#333; margin:0; font-size:24px; font-weight:600;">Xin
                                chào {{ $admin->name ?? 'Quản trị viên' }},</h2>
                            <p style="color:#666; font-size:16px; line-height:1.6; margin-top:15px;">
                                Một <strong style="color:#E27447;">yêu cầu kiểm duyệt khóa học mới</strong> vừa được gửi
                                đến hệ thống và đang chờ bạn xem xét.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:10px 40px 30px;">
                            <div
                                style="background-color:#fff8f5; border-radius:10px; padding:25px; text-align:left; border:1px solid #fce1d6;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="100" valign="top">
                                            <img src="{{ $course->thumbnail }}" alt="Thumbnail khóa học"
                                                style="width:100px; height:100px; object-fit:cover; border-radius:8px; border:1px solid #eee;">
                                        </td>
                                        <td style="padding-left:20px;">
                                            <h3 style="margin:0 0 10px; color:#333; font-size:18px; font-weight:600;">
                                                {{ $course->name }}</h3>
                                            <p style="font-size:14px; color:#666; margin:0 0 8px;">
                                                <strong>Giảng viên:</strong> {{ $course->instructor->name }}
                                            </p>
                                            <p style="font-size:14px; color:#666; margin:0 0 8px;">
                                                <strong>Danh mục:</strong> {{ $course->category->name }}
                                            </p>
                                            <p style="font-size:14px; color:#666; margin:0;">
                                                <strong>Ngày gửi:</strong>
                                                {{ now()->format('d/m/Y H:i') }}
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 40px 30px;">
                            <p style="font-size:18px; font-weight:600; color:#333; margin-bottom:20px;">
                                <span style="color:#E27447;">📋</span> Thông tin chi tiết:
                            </p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td
                                        style="padding:15px; background-color:#fff8f5; border-radius:10px; margin-bottom:15px; border-left:3px solid #E27447;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="40" valign="top">
                                                    <div
                                                        style="width:40px; height:40px; background-color:#ffeee8; border-radius:50%; display:inline-block; text-align:center; line-height:40px; font-size:18px; color:#E27447;">
                                                        📖
                                                    </div>
                                                </td>
                                                <td style="padding-left:15px;">
                                                    <p
                                                        style="margin:0 0 5px; color:#444; font-size:16px; font-weight:500;">
                                                        Số chương: <span
                                                            style="font-weight:400;">{{ $course->chapters_count ?? '0' }}
                                                            chương</span>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="15"></td>
                                </tr>
                                <tr>
                                    <td
                                        style="padding:15px; background-color:#fff8f5; border-radius:10px; margin-bottom:15px; border-left:3px solid #E27447;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="40" valign="top">
                                                    <div
                                                        style="width:40px; height:40px; background-color:#ffeee8; border-radius:50%; display:inline-block; text-align:center; line-height:40px; font-size:18px; color:#E27447;">
                                                        📚
                                                    </div>
                                                </td>
                                                <td style="padding-left:15px;">
                                                    <p
                                                        style="margin:0 0 5px; color:#444; font-size:16px; font-weight:500;">
                                                        Số bài học: <span
                                                            style="font-weight:400;">{{ $course->lessons_count ?? '0' }}
                                                            bài học</span>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="15"></td>
                                </tr>
                                <tr>
                                    <td
                                        style="padding:15px; background-color:#fff8f5; border-radius:10px; border-left:3px solid #E27447;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="40" valign="top">
                                                    <div
                                                        style="width:40px; height:40px; background-color:#ffeee8; border-radius:50%; display:inline-block; text-align:center; line-height:40px; font-size:18px; color:#E27447;">
                                                        💰
                                                    </div>
                                                </td>
                                                <td style="padding-left:15px;">
                                                    <p
                                                        style="margin:0 0 5px; color:#444; font-size:16px; font-weight:500;">
                                                        Giá khóa học:
                                                        <span style="font-weight:400;">
                                                            @if ($course->is_free)
                                                                Miễn phí
                                                            @elseif ($course->price_sale)
                                                                {{ number_format($course->price_sale, 0, ',', '.') }} đ
                                                            @else
                                                                {{ number_format($course->price, 0, ',', '.') }} đ
                                                            @endif
                                                        </span>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 40px 30px;">
                            <div style="background-color:#f7f8fa; border-radius:10px; padding:20px;">
                                <p style="font-size:15px; line-height:1.5; color:#555; margin:0 0 15px;">
                                    <strong>Mô tả khóa học:</strong>
                                </p>
                                <textarea style="font-size:14px; line-height:1.6; color:#666; margin:0;">
                                    {{ $course->description }}
                                </textarea>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:10px 40px 40px;">
                            <a href="{{ $approvalUrl }}"
                                style="display:inline-block; background: linear-gradient(to right, #E27447, #f59776); color:#fff; padding:15px 35px; font-size:16px; text-decoration:none; border-radius:8px; font-weight:600; letter-spacing:0.5px; box-shadow:0 4px 10px rgba(226,116,71,0.3); transition: all 0.3s;">
                                🔍 XEM XÉT YÊU CẦU KIỂM DUYỆT
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:0 40px 20px;">
                            <p style="font-size:14px; color:#777; border-top:1px solid #eee; padding-top:20px;">
                                Email này được gửi tự động từ hệ thống CourseMeLy.
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
                                            Mọi
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
                                Email này được gửi tự động, vui lòng không trả lời. Nếu bạn cần hỗ trợ, vui lòng liên hệ
                                <a href="mailto:coursemely@gmail.com"
                                    style="color:#E27447; text-decoration:none;">coursemely@gmail.com</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
