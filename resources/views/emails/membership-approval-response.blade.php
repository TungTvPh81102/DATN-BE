<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo kiểm duyệt gói membership - CourseMeLy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Manrope', Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f8;
        }
    </style>
</head>

<body
    style="margin:0; padding:0; background-color:#faf7f5; font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;">
    >
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="background-color:#faf7f5;padding:30px 0;">
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
                                chào {{ $membershipPlan->instructor->name }},</h2>
                            <p
                                style="color:#666; font-size:16px; line-height:1.6; margin-top:16px; text-align: center;">
                                @if ($status === 'approved')
                                    Chúng tôi vui mừng thông báo rằng gói membership <strong
                                        style="color:#E27447;">{{ $membershipPlan->name }}</strong> của bạn đã được phê
                                    duyệt thành công và sẵn sàng để người học đăng ký!
                                @else
                                    Chúng tôi đã xem xét gói membership <strong
                                        style="color:#E27447;">{{ $membershipPlan->name }}</strong> của bạn và rất tiếc
                                    phải thông báo rằng gói này cần một số điều chỉnh trước khi có thể được xuất bản.
                                @endif
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 30px 30px;">
                            <p
                                style="font-size:18px; font-weight:600; color:#222; margin-bottom:16px; display: flex; align-items: center;">
                                <span
                                    style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background-color: #fff2ed; border-radius: 50%; margin-right: 10px;">
                                    @if ($status === 'approved')
                                        <span style="color:#E27447; font-size: 16px;">✅</span>
                                    @else
                                        <span style="color:#E27447; font-size: 16px;">📝</span>
                                    @endif
                                </span>
                                Trạng thái kiểm duyệt
                            </p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td
                                        style="padding:16px; background-color:#fff8f5; border-radius:12px; margin-bottom:12px; 
                                @if ($status === 'approved') border-left:4px solid #4CAF50;
                                @else
                                border-left:4px solid #FF5722; @endif
                                ">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="40" valign="top">
                                                    <div
                                                        style="width:40px; height:40px; 
                                                    @if ($status === 'approved') background-color:#e8f5e9;
                                                    @else
                                                    background-color:#fbe9e7; @endif
                                                    border-radius:50%; display:flex; align-items:center; justify-content:center; text-align:center;">
                                                        @if ($status === 'approved')
                                                            <span style="color:#4CAF50; font-size:18px;">✓</span>
                                                        @else
                                                            <span style="color:#FF5722; font-size:18px;">!</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td style="padding-left:16px;">
                                                    <p style="margin:0; color:#444; font-size:15px; font-weight:500;">
                                                        Trạng thái:
                                                        <strong
                                                            style="
                                                        @if ($status === 'approved') color:#4CAF50;
                                                        @else
                                                        color:#FF5722; @endif
                                                        font-weight:600;">
                                                            @if ($status === 'approved')
                                                                Đã phê duyệt
                                                            @else
                                                                Cần điều chỉnh
                                                            @endif
                                                        </strong>
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
                        <td style="padding:0 30px 30px;">
                            <p
                                style="font-size:18px; font-weight:600; color:#222; margin-bottom:16px; display: flex; align-items: center;">
                                <span
                                    style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; background-color: #fff2ed; border-radius: 50%; margin-right: 10px;">
                                    <span style="color:#E27447; font-size: 16px;">🎓</span>
                                </span>
                                Chi tiết gói Membership
                            </p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="border-collapse: collapse; border:1px solid #f0f0f0; border-radius:12px; overflow:hidden;">
                                <tr style="background-color:#fff8f5;">
                                    <th
                                        style="padding:14px 16px; text-align:left; border-bottom:1px solid #f0f0f0; color:#444; font-weight:600; font-size: 15px;">
                                        Tên gói
                                    </th>
                                    <th
                                        style="padding:14px 16px; text-align:center; border-bottom:1px solid #f0f0f0; color:#444; font-weight:600; font-size: 15px;">
                                        Thời hạn
                                    </th>
                                    <th
                                        style="padding:14px 16px; text-align:right; border-bottom:1px solid #f0f0f0; color:#444; font-weight:600; font-size: 15px;">
                                        Giá
                                    </th>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px; border-bottom:1px solid #f0f0f0; font-size: 15px;">
                                        <strong style="color:#333;">{{ $membershipPlan->name }}</strong>
                                    </td>
                                    <td
                                        style="padding:14px 16px; border-bottom:1px solid #f0f0f0; font-size: 15px; text-align:center;">
                                        <span
                                            style="background-color: #ffeee8; color: #E27447; padding: 4px 10px; border-radius: 20px; font-weight: 500; display: inline-block;">
                                            {{ $membershipPlan->duration_months }} tháng
                                        </span>
                                    </td>
                                    <td
                                        style="padding:14px 16px; text-align:right; border-bottom:1px solid #f0f0f0; font-size: 15px;">
                                        {{ number_format(round($membershipPlan->price, 2), 0, ',', '.') }}
                                        VND
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 30px 30px;">
                            <p
                                style="font-size:18px; font-weight:600; color:#222; margin-bottom:16px; display: flex; align-items: center;">
                                <span
                                    style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; background-color: #fff2ed; border-radius: 50%; margin-right: 10px;">
                                    <span style="color:#E27447; font-size: 16px;">💬</span>
                                </span>
                                Nhận xét từ người kiểm duyệt
                            </p>

                            <div
                                style="background-color: #fff8f5; border-radius:12px; padding:20px; border-left:4px solid #E27447;">
                                <p style="margin:0; font-size:15px; line-height:1.6; color:#555;">
                                    {{ $note }}
                                </p>
                            </div>
                        </td>
                    </tr>

                    @if ($status === 'approved')
                        <tr>
                            <td align="center" style="padding:10px 30px 30px;">
                                <div
                                    style="background: linear-gradient(to bottom right, #e8f5e9, #c8e6c9); border-radius:14px; padding:25px 20px; text-align:center; box-shadow: 0 8px 15px rgba(76,175,80,0.08);">
                                    <p style="font-size:18px; color:#2E7D32; font-weight:600; margin-bottom:20px;">Gói
                                        membership của bạn đã sẵn sàng!</p>

                                    <p style="color:#555; line-height:1.6; margin-bottom:20px;">
                                        Gói membership của bạn hiện đã được đăng tải công khai và người học có thể bắt
                                        đầu đăng ký. Hãy thông báo cho cộng đồng của bạn ngay bây giờ!
                                    </p>

                                    <a href="#"
                                        style="display:inline-block; margin-top:10px; background: linear-gradient(to right, #4CAF50, #66BB6A); color:#fff; padding:14px 30px; font-size:16px; text-decoration:none; border-radius:10px; font-weight:600; letter-spacing:0.5px; box-shadow:0 6px 15px rgba(76,175,80,0.3); transition: all 0.3s;">
                                        👉 XEM GÓI MEMBERSHIP
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td align="center" style="padding:10px 30px 30px;">
                                <div
                                    style="background: linear-gradient(to bottom right, #fbe9e7, #ffccbc); border-radius:14px; padding:25px 20px; text-align:center; box-shadow: 0 8px 15px rgba(255,87,34,0.08);">
                                    <p style="font-size:18px; color:#D84315; font-weight:600; margin-bottom:20px;">Các
                                        bước tiếp theo</p>

                                    <p style="color:#555; line-height:1.6; margin-bottom:20px;">
                                        Vui lòng xem xét các nhận xét trên và chỉnh sửa gói membership của bạn theo đó.
                                        Sau khi hoàn thành các điều chỉnh, bạn có thể gửi lại để kiểm duyệt.
                                    </p>

                                    <a href="{{ config('app.fe_url') . '/instructor/memberships' }}"
                                        style="display:inline-block; margin-top:10px; background: linear-gradient(to right, #FF5722, #FF8A65); color:#fff; padding:14px 30px; font-size:16px; text-decoration:none; border-radius:10px; font-weight:600; letter-spacing:0.5px; box-shadow:0 6px 15px rgba(255,87,34,0.3); transition: all 0.3s;">
                                        👉 CHỈNH SỬA GÓI MEMBERSHIP
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td align="center" style="padding:5px 30px 30px;">
                            <div style="border-top:1px solid #eee; padding-top:20px; max-width: 90%; margin: 0 auto;">
                                <p style="font-size:15px; color:#555; line-height: 1.6; text-align: center;">
                                    @if ($status === 'approved')
                                        Cảm ơn bạn đã tạo nội dung chất lượng cho cộng đồng CourseMeLy. Chúng tôi rất
                                        vui mừng được hợp tác với bạn để mang đến những trải nghiệm học tập tuyệt vời
                                        cho người học.
                                    @else
                                        Cảm ơn bạn đã hiểu và hợp tác. Mục tiêu của chúng tôi là đảm bảo tất cả nội dung
                                        trên CourseMeLy đều có chất lượng cao và mang lại giá trị tốt nhất cho người
                                        học.
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="background-color:#fff8f5; padding:30px; border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <div style="margin-bottom: 16px;">
                                            <a href="#" style="display:inline-block; margin:0 8px;"><img
                                                    src="/api/placeholder/28/28" alt="Facebook"
                                                    style="width:28px; height:28px; border-radius:6px;"></a>
                                            <a href="#" style="display:inline-block; margin:0 8px;"><img
                                                    src="/api/placeholder/28/28" alt="Instagram"
                                                    style="width:28px; height:28px; border-radius:6px;"></a>
                                            <a href="#" style="display:inline-block; margin:0 8px;"><img
                                                    src="/api/placeholder/28/28" alt="LinkedIn"
                                                    style="width:28px; height:28px; border-radius:6px;"></a>
                                            <a href="#" style="display:inline-block; margin:0 8px;"><img
                                                    src="/api/placeholder/28/28" alt="YouTube"
                                                    style="width:28px; height:28px; border-radius:6px;"></a>
                                        </div>
                                        <p style="font-size:14px; color:#777; margin:0 0 5px;">&copy; 2025 CourseMeLy.
                                            Mọi
                                            quyền được bảo lưu.</p>
                                        <p style="font-size:13px; color:#999; margin:5px 0 0;">
                                            Email này được gửi tự động, vui lòng không trả lời. Nếu bạn cần hỗ trợ, vui
                                            lòng
                                            liên hệ <a href="mailto:coursemely@gmail.com"
                                                style="color:#E27447; text-decoration:none; font-weight: 500;">coursemely@gmail.com</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
