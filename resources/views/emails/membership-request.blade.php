<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu Cầu Phê Duyệt Gói Thành Viên - CourseMeLy</title>
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
                        <h2 style="color:#333; margin:0; font-size:24px; font-weight:600;">Xin
                            chào {{$notifiable->name ?? 'Quản trị viên'}},</h2>
                        <p style="color:#666; font-size:16px; line-height:1.6; margin-top:15px;">
                            Một yêu cầu phê duyệt gói thành viên mới vừa được gửi đến <strong
                                style="color:#E27447;">CourseMeLy</strong>! Vui lòng xem xét thông tin chi tiết về
                            gói thành viên này để đưa ra quyết định phê duyệt.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 40px 30px;">
                        <p style="font-size:18px; font-weight:600; color:#333; margin-bottom:20px;">
                            <span style="color:#E27447;">💼</span> Thông tin gói thành viên:
                        </p>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding:15px; background-color:#fff8f5; border-radius:10px; margin-bottom:15px; border-left:3px solid #E27447;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="40" valign="top">
                                                <div
                                                    style="width:40px; height:40px; background-color:#ffeee8; border-radius:50%; display:inline-block; text-align:center; line-height:40px; font-size:18px; color:#E27447;">
                                                    📋
                                                </div>
                                            </td>
                                            <td style="padding-left:15px;">
                                                <p style="margin:0; color:#444; font-size:16px; font-weight:500;">Tên
                                                    gói:
                                                    <strong>{{$membershipPlan->name}}</strong></p>
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
                                                    🏷️
                                                </div>
                                            </td>
                                            <td style="padding-left:15px;">
                                                <p style="margin:0; color:#444; font-size:16px; font-weight:500;">Mã
                                                    gói:
                                                    <strong>{{$membershipPlan->code}}</strong></p>
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
                                                    👨‍🏫
                                                </div>
                                            </td>
                                            <td style="padding-left:15px;">
                                                <p style="margin:0; color:#444; font-size:16px; font-weight:500;">Giảng
                                                    viên:
                                                    <strong>{{$instructor->name}}</strong></p>
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
                                                    📚
                                                </div>
                                            </td>
                                            <td style="padding-left:15px;">
                                                <p style="margin:0; color:#444; font-size:16px; font-weight:500;">Số
                                                    lượng khóa học:
                                                    <strong>{{$courseCount}}</strong></p>
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
                                                    📆
                                                </div>
                                            </td>
                                            <td style="padding-left:15px;">
                                                <p style="margin:0; color:#444; font-size:16px; font-weight:500;">Ngày
                                                    yêu cầu:
                                                    <strong>{{$approvalRequest->created_at->format('d/m/Y H:i')}}</strong>
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
                    <td align="center" style="padding:10px 40px 40px;">
                        <div style="background-color:#fff8f5; border-radius:10px; padding:25px; text-align:center;">
                            <p style="font-size:16px; color:#555; margin-bottom:20px;">Vui lòng xem xét yêu cầu này và
                                tiến hành phê duyệt hoặc từ chối:</p>
                            <a href="{{$url ?? '#'}}"
                               style="display:inline-block; background: linear-gradient(to right, #E27447, #f59776); color:#fff; padding:15px 35px; font-size:16px; text-decoration:none; border-radius:8px; font-weight:600; letter-spacing:0.5px; box-shadow:0 4px 10px rgba(226,116,71,0.3); transition: all 0.3s;">
                                👉 XEM CHI TIẾT YÊU CẦU
                            </a>
                            <p style="font-size:14px; color:#999; margin-top:25px; font-style:italic;">
                                Yêu cầu đang chờ xử lý. Vui lòng phản hồi trong vòng 48 giờ.
                            </p>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 40px 30px;">
                        <p style="font-size:18px; font-weight:600; color:#333; margin-bottom:20px;">
                            <span style="color:#E27447;">⚡</span> Các bước xét duyệt:
                        </p>
                        <ol style="color:#555; font-size:15px; line-height:1.6; margin:0; padding-left:25px;">
                            <li style="margin-bottom:10px;">Xem xét thông tin chung và mô tả gói thành viên</li>
                            <li style="margin-bottom:10px;">Kiểm tra danh sách khóa học được đưa vào gói</li>
                            <li style="margin-bottom:10px;">Đánh giá giá trị và tính phù hợp của gói thành viên</li>
                            <li style="margin-bottom:10px;">Xem xét chính sách thanh toán và thời hạn áp dụng</li>
                            <li>Đưa ra quyết định phê duyệt hoặc từ chối kèm lý do</li>
                        </ol>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:0 40px 20px;">
                        <p style="font-size:14px; color:#777; border-top:1px solid #eee; padding-top:20px;">
                            Email này được gửi tự động từ hệ thống. Nếu bạn cần hỗ trợ, hãy <a href="#"
                                                                                               style="color:#E27447; text-decoration:none;">liên
                                hệ với đội ngũ kỹ thuật</a>.
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
