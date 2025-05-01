<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đăng ký gói membership - CourseMeLy</title>
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
    style="margin:0; padding:0; background-color:#f5f5f8; font-family: 'Manrope', Arial, Helvetica, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"
       style="background-color:#f5f5f8; padding:40px 20px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0"
                   style="background-color:#ffffff; padding:0; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.06); overflow:hidden; max-width:100%;">

                <tr>
                    <td align="center"
                        style="background: linear-gradient(135deg, #E27447, #f59776); padding:40px 20px 30px;">
                        <img
                            src="https://res.cloudinary.com/dere3na7i/image/upload/c_thumb,w_200,g_face/v1740311680/logo-container_zi19ug.png"
                            alt="CourseMeLy Logo" width="80" height="80"
                            style="box-shadow: 0 6px 15px rgba(0,0,0,0.12); border-radius: 16px; max-width:80px; object-fit:contain;">
                        <h1 style="color:#ffffff; margin-top:20px; font-size:28px; font-weight:700; letter-spacing:0.5px;">
                            CourseMeLy</h1>
                        <p style="color:#ffffff; opacity:0.95; margin:8px 0 0; font-size:16px; letter-spacing: 0.3px;">
                            Nền tảng học trực tuyến hàng đầu</p>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:40px 30px 20px;">
                        <h2 style="color:#222; margin:0; font-size:24px; font-weight:600;">Xin
                            chào {{$member->name}},</h2>
                        <p style="color:#555; font-size:16px; line-height:1.6; margin-top:16px; text-align: center;">
                            Cảm ơn bạn đã đăng ký gói membership với <strong style="color:#E27447;">CourseMeLy</strong>!
                            Đơn hàng của bạn đã được xử lý thành công. Bạn có thể bắt đầu trải nghiệm các đặc quyền
                            thành viên ngay từ bây giờ.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 30px 30px;">
                        <p style="font-size:18px; font-weight:600; color:#222; margin-bottom:16px; display: flex; align-items: center;">
                            <span
                                style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background-color: #fff2ed; border-radius: 50%; margin-right: 10px;">
                                <span style="color:#E27447; font-size: 16px;">🧾</span>
                            </span>
                            Chi tiết hóa đơn
                        </p>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding:16px; background-color:#fff8f5; border-radius:12px; margin-bottom:12px; border-left:4px solid #E27447;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="40" valign="top">
                                                <div
                                                    style="width:40px; height:40px; background-color:#ffeee8; border-radius:50%; display:flex; align-items:center; justify-content:center; text-align:center;">
                                                    <span style="color:#E27447; font-size:18px;">🔖</span>
                                                </div>
                                            </td>
                                            <td style="padding-left:16px;">
                                                <p style="margin:0; color:#444; font-size:15px; font-weight:500;">Mã đơn
                                                    hàng:
                                                    <strong
                                                        style="color:#222; font-weight:600;">{{$invoice->code}}</strong>
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="12"></td>
                            </tr>
                            <tr>
                                <td style="padding:16px; background-color:#fff8f5; border-radius:12px; margin-bottom:12px; border-left:4px solid #E27447;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="40" valign="top">
                                                <div
                                                    style="width:40px; height:40px; background-color:#ffeee8; border-radius:50%; display:flex; align-items:center; justify-content:center; text-align:center;">
                                                    <span style="color:#E27447; font-size:18px;">📅</span>
                                                </div>
                                            </td>
                                            <td style="padding-left:16px;">
                                                <p style="margin:0; color:#444; font-size:15px; font-weight:500;">Ngày
                                                    đăng ký:
                                                    <strong
                                                        style="color:#222; font-weight:600;">{{ \Illuminate\Support\Carbon::parse($transaction->created_at)->locale('vi')->translatedFormat('d F Y') }}</strong>
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="12"></td>
                            </tr>
                            <tr>
                                <td style="padding:16px; background-color:#fff8f5; border-radius:12px; border-left:4px solid #E27447;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="40" valign="top">
                                                <div
                                                    style="width:40px; height:40px; background-color:#ffeee8; border-radius:50%; display:flex; align-items:center; justify-content:center; text-align:center;">
                                                    <span style="color:#E27447; font-size:18px;">💳</span>
                                                </div>
                                            </td>
                                            <td style="padding-left:16px;">
                                                <p style="margin:0; color:#444; font-size:15px; font-weight:500;">Phương
                                                    thức thanh toán:
                                                    <strong style="color:#222; font-weight:600;">VNPAY</strong></p>
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
                        <p style="font-size:18px; font-weight:600; color:#222; margin-bottom:16px; display: flex; align-items: center;">
                            <span
                                style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; background-color: #fff2ed; border-radius: 50%; margin-right: 10px;">
                                <span style="color:#E27447; font-size: 16px;">🎓</span>
                            </span>
                            Chi tiết gói Membership
                        </p>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                               style="border-collapse: collapse; border:1px solid #f0f0f0; border-radius:12px; overflow:hidden;">
                            <tr style="background-color:#fff8f5;">
                                <th style="padding:14px 16px; text-align:left; border-bottom:1px solid #f0f0f0; color:#444; font-weight:600; font-size: 15px;">
                                    Tên gói
                                </th>
                                <th style="padding:14px 16px; text-align:center; border-bottom:1px solid #f0f0f0; color:#444; font-weight:600; font-size: 15px;">
                                    Thời hạn
                                </th>
                                <th style="padding:14px 16px; text-align:right; border-bottom:1px solid #f0f0f0; color:#444; font-weight:600; font-size: 15px;">
                                    Giá
                                </th>
                            </tr>
                            <tr>
                                <td style="padding:14px 16px; border-bottom:1px solid #f0f0f0; font-size: 15px;">
                                    <strong style="color:#333;">{{$membership->name}}</strong>
                                </td>
                                <td style="padding:14px 16px; border-bottom:1px solid #f0f0f0; font-size: 15px; text-align:center;">
                                    <span
                                        style="background-color: #ffeee8; color: #E27447; padding: 4px 10px; border-radius: 20px; font-weight: 500; display: inline-block;">
                                        {{$membership->duration_months}} tháng
                                    </span>
                                </td>
                                <td style="padding:14px 16px; text-align:right; border-bottom:1px solid #f0f0f0; font-size: 15px;">{{ number_format(round($membership->price, 2), 0, ',', '.') }}
                                    VND
                                </td>
                            </tr>
                            <tr style="background-color:#fff8f5;">
                                <td colspan="2"
                                    style="padding:14px 16px; text-align:right; font-weight:600; color:#333; font-size: 15px;">
                                    Tổng thanh toán:
                                </td>
                                <td style="padding:14px 16px; text-align:right; font-weight:700; color:#E27447; font-size: 16px;">{{ number_format($transaction->amount, 0, ',', '.') }}
                                    VND
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:10px 30px 30px;">
                        <div
                            style="background: linear-gradient(to bottom right, #fff8f5, #ffefe9); border-radius:14px; padding:25px 20px; text-align:center; box-shadow: 0 8px 15px rgba(226,116,71,0.08);">
                            <p style="font-size:18px; color:#E27447; font-weight:600; margin-bottom:20px;">Gói
                                membership của bạn sẽ có hiệu lực:</p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="50%" align="center" style="padding: 0 8px;">
                                        <div
                                            style="background-color:white; border-radius:10px; padding:18px; box-shadow:0 6px 15px rgba(226,116,71,0.1);">
                                            <div
                                                style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background-color: #fff2ed; border-radius: 50%; margin: 0 auto 8px;">
                                                <span style="color:#E27447; font-size: 18px;">🗓️</span>
                                            </div>
                                            <p style="margin:6px 0; font-size:14px; color:#666;">Từ ngày:</p>
                                            <p style="margin:6px 0 0; font-size:16px; font-weight:600; color:#333;">{{ \Illuminate\Support\Carbon::parse($membership->start_date)->locale('vi')->translatedFormat('d F Y') }}</p>
                                        </div>
                                    </td>
                                    <td width="50%" align="center" style="padding: 0 8px;">
                                        <div
                                            style="background-color:white; border-radius:10px; padding:18px; box-shadow:0 6px 15px rgba(226,116,71,0.1);">
                                            <div
                                                style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background-color: #fff2ed; border-radius: 50%; margin: 0 auto 8px;">
                                                <span style="color:#E27447; font-size: 18px;">📆</span>
                                            </div>
                                            <p style="margin:6px 0; font-size:14px; color:#666;">Đến ngày:</p>
                                            <p style="margin:6px 0 0; font-size:16px; font-weight:600; color:#333;"> @php
                                                    $displayEndDate = \Illuminate\Support\Carbon::parse($membership->end_date);
                                                    if ($displayEndDate < \Illuminate\Support\Carbon::parse($membership->start_date)) {
                                                        $displayEndDate = \Illuminate\Support\Carbon::parse($membership->start_date)->addMonths($membership->duration_months);
                                                    }
                                                @endphp
                                                {{ $displayEndDate->locale('vi')->translatedFormat('d F Y') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <a href="#"
                               style="display:inline-block; margin-top:25px; background: linear-gradient(to right, #E27447, #f59776); color:#fff; padding:14px 30px; font-size:16px; text-decoration:none; border-radius:10px; font-weight:600; letter-spacing:0.5px; box-shadow:0 6px 15px rgba(226,116,71,0.3); transition: all 0.3s;">
                                👉 TRUY CẬP NGAY
                            </a>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 30px 30px;">
                        <p style="font-size:18px; font-weight:600; color:#222; margin-bottom:16px; display: flex; align-items: center;">
                            <span
                                style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; background-color: #fff2ed; border-radius: 50%; margin-right: 10px;">
                                <span style="color:#E27447; font-size: 16px;">⭐</span>
                            </span>
                            Quyền lợi thành viên của bạn
                        </p>

                        <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 12px;">
                            @foreach($membership->benefits as $benefit)
                                <div
                                    style="background-color: #fff8f5; border-radius: 12px; padding: 14px; border-left: 4px solid #E27447; margin-bottom: 14px;">
                                    <div style="display: flex; align-items: flex-start;">
                                        <div
                                            style="width: 24px; height: 24px; min-width: 24px; background-color: #ffeee8; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                            <span style="color: #E27447; font-weight: bold; font-size: 12px;">✓</span>
                                        </div>
                                        <p style="margin: 0; padding: 0; color: #444; font-size: 15px; line-height: 1.5;">
                                            {{$benefit}}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:5px 30px 30px;">
                        <div style="border-top:1px solid #eee; padding-top:20px; max-width: 90%; margin: 0 auto;">
                            <p style="font-size:15px; color:#555; line-height: 1.6; text-align: center;">
                                Cảm ơn bạn đã trở thành thành viên của cộng đồng CourseMeLy. Chúng tôi cam kết mang lại
                                trải nghiệm học tập tốt nhất, giúp bạn phát triển kỹ năng và tiến xa hơn trong sự
                                nghiệp.
                            </p>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="background-color:#fff8f5; padding:30px; border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
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
                                    <p style="font-size:14px; color:#777; margin:0 0 5px;">&copy; 2025 CourseMeLy. Mọi
                                        quyền được bảo lưu.</p>
                                    <p style="font-size:13px; color:#999; margin:5px 0 0;">
                                        Email này được gửi tự động, vui lòng không trả lời. Nếu bạn cần hỗ trợ, vui lòng
                                        liên hệ <a
                                            href="mailto:coursemely@gmail.com"
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
