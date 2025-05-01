<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu rút tiền - CourseMeLy</title>
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

<body style="margin:0; padding:0; background-color:#f5f5f8; font-family: 'Manrope', Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="background-color:#f5f5f8; padding:30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="700" cellpadding="0" cellspacing="0"
                    style="background-color:#ffffff; padding:0; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.06); overflow:hidden; max-width:100%;">
                    <tr>
                        <td align="center"
                            style="background: linear-gradient(135deg, #E27447, #f59776); padding:40px 20px 30px;">
                            <img src="https://res.cloudinary.com/dere3na7i/image/upload/c_thumb,w_200,g_face/v1740311680/logo-container_zi19ug.png"
                                alt="CourseMeLy Logo" width="80" height="80"
                                style="box-shadow: 0 6px 15px rgba(0,0,0,0.12); border-radius: 16px; max-width:80px; object-fit:contain;">
                            <h1
                                style="color:#ffffff; margin-top:20px; font-size:28px; font-weight:700; letter-spacing:0.5px;">
                                CourseMeLy</h1>
                            <p
                                style="color:#ffffff; opacity:0.95; margin:8px 0 0; font-size:16px; letter-spacing: 0.3px;">
                                Nền tảng học trực tuyến hàng đầu</p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:40px 30px 20px;">
                            <h2 style="color:#222; margin:0; font-size:24px; font-weight:600;">Yêu cầu rút tiền mới</h2>
                            <p
                                style="color:#555; font-size:16px; line-height:1.6; margin-top:16px; text-align: center;">
                                Chúng tôi vừa nhận được yêu cầu rút tiền mới từ giảng viên
                                <strong style="color:#E27447;">{{ $instructor->name }}</strong>.
                                Vui lòng xem xét và xử lý yêu cầu này trong thời gian sớm nhất.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 30px 30px;">
                            <p style="font-size:18px; font-weight:600; color:#333; margin-bottom:20px;">
                                <span style="color:#E27447; font-size: 16px;">📋</span> Chi tiết yêu cầu
                            </p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td
                                        style="padding:16px; background-color:#fff8f5; border-radius:12px; margin-bottom:12px; border-left:4px solid #E27447;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="40" valign="top">
                                                    <table role="presentation" cellpadding="0" cellspacing="0"
                                                        width="40" height="40"
                                                        style="border-collapse: collapse;">
                                                        <tr>
                                                            <td
                                                                style="width:40px; height:40px; background-color:#ffeee8; border-radius:50%; text-align:center; vertical-align:middle; line-height:0;">
                                                                <div style="font-size:18px; line-height:40px;">🔖</div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td style="padding-left:16px;">
                                                    <p style="margin:0; color:#444; font-size:15px; font-weight:500;">Mã
                                                        yêu cầu:
                                                        <strong
                                                            style="color:#222; font-weight:600;">WD-{{ $withdrawalRequest->id }}</strong>
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
                                    <td
                                        style="padding:16px; background-color:#fff8f5; border-radius:12px; margin-bottom:12px; border-left:4px solid #E27447;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="40" valign="top">
                                                    <table role="presentation" cellpadding="0" cellspacing="0"
                                                        width="40" height="40"
                                                        style="border-collapse: collapse;">
                                                        <tr>
                                                            <td
                                                                style="width:40px; height:40px; background-color:#ffeee8; border-radius:50%; text-align:center; vertical-align:middle; line-height:0;">
                                                                <div style="font-size:18px; line-height:40px;">📅</div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td style="padding-left:16px;">
                                                    <p style="margin:0; color:#444; font-size:15px; font-weight:500;">
                                                        Ngày yêu cầu:
                                                        <strong
                                                            style="color:#222; font-weight:600;">{{ $withdrawalRequest->request_date }}</strong>
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
                                    <td
                                        style="padding:16px; background-color:#fff8f5; border-radius:12px; border-left:4px solid #E27447;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="40" valign="top">
                                                    <table role="presentation" cellpadding="0" cellspacing="0"
                                                        width="40" height="40"
                                                        style="border-collapse: collapse;">
                                                        <tr>
                                                            <td
                                                                style="width:40px; height:40px; background-color:#ffeee8; border-radius:50%; text-align:center; vertical-align:middle; line-height:0;">
                                                                <div style="font-size:18px; line-height:40px;">💰</div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td style="padding-left:16px;">
                                                    <p style="margin:0; color:#444; font-size:15px; font-weight:500;">
                                                        Số tiền yêu cầu:
                                                        <strong
                                                            style="color:#222; font-weight:600;">{{ number_format($withdrawalRequest->amount, 0, ',', '.') }}
                                                            VND</strong>
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
                                    style="display: inline-block; width: 32px; height: 32px; background-color: #fff2ed; border-radius: 50%; margin-right: 10px; text-align: center; line-height: 32px;">
                                    <span style="color:#E27447; font-size: 16px;">👨‍🏫</span>
                                </span>
                                Thông tin giảng viên
                            </p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="border-collapse: collapse; border:1px solid #f0f0f0; border-radius:12px; overflow:hidden;">
                                <tr style="background-color:#fff8f5;">
                                    <th
                                        style="padding:14px 16px; text-align:left; border-bottom:1px solid #f0f0f0; color:#444; font-weight:600; font-size: 15px;">
                                        Họ tên
                                    </th>
                                    <th
                                        style="padding:14px 16px; text-align:center; border-bottom:1px solid #f0f0f0; color:#444; font-weight:600; font-size: 15px;">
                                        Email
                                    </th>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px; border-bottom:1px solid #f0f0f0; font-size: 15px;">
                                        <div style="display: flex; align-items: center; justify-content: center;">
                                            <img src="{{ $instructor->avatar }}" alt="{{ $instructor->name }}"
                                                style="width: 36px; height: 36px; border-radius: 50%; margin-right: 10px; object-fit: cover;">
                                            <strong style="color:#333;">{{ $instructor->name }}</strong>
                                        </div>
                                    </td>
                                    <td
                                        style="padding:14px 16px; border-bottom:1px solid #f0f0f0; font-size: 15px; text-align:center;">
                                        <span
                                            style="background-color: #ffeee8; color: #E27447; padding: 4px 10px; border-radius: 20px; font-weight: 500; display: inline-block;">
                                            {{ $instructor->email }}
                                        </span>
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
                                    style="display: inline-block; width: 32px; height: 32px; background-color: #fff2ed; border-radius: 50%; margin-right: 10px; text-align: center; line-height: 32px;">
                                    <span style="color:#E27447; font-size: 16px;">🏦</span>
                                </span>
                                Thông tin thanh toán
                            </p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="border-collapse: collapse; border:1px solid #f0f0f0; border-radius:12px; overflow:hidden;">
                                <tr style="background-color:#fff8f5;">
                                    <th
                                        style="padding:14px 16px; text-align:left; border-bottom:1px solid #f0f0f0; color:#444; font-weight:600; font-size: 15px;">
                                        Ngân hàng
                                    </th>
                                    <th
                                        style="padding:14px 16px; text-align:center; border-bottom:1px solid #f0f0f0; color:#444; font-weight:600; font-size: 15px;">
                                        Số tài khoản
                                    </th>
                                    <th
                                        style="padding:14px 16px; text-align:right; border-bottom:1px solid #f0f0f0; color:#444; font-weight:600; font-size: 15px;">
                                        Chủ tài khoản
                                    </th>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px; border-bottom:1px solid #f0f0f0; font-size: 15px;">
                                        <strong style="color:#333;">{{ $withdrawalRequest->bank_name }}</strong>
                                    </td>
                                    <td
                                        style="padding:14px 16px; border-bottom:1px solid #f0f0f0; font-size: 15px; text-align:center;">
                                        <span
                                            style="background-color: #ffeee8; color: #E27447; padding: 4px 10px; border-radius: 20px; font-weight: 500; display: inline-block;">
                                            {{ $withdrawalRequest->account_number }}
                                        </span>
                                    </td>
                                    <td
                                        style="padding:14px 16px; text-align:right; border-bottom:1px solid #f0f0f0; font-size: 15px;">
                                        {{ $withdrawalRequest->account_holder }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:10px 30px 30px;">
                            <div
                                style="background: linear-gradient(to bottom right, #fff8f5, #ffefe9); border-radius:14px; padding:25px 20px; text-align:center; box-shadow: 0 8px 15px rgba(226,116,71,0.08);">
                                <p style="font-size:18px; color:#E27447; font-weight:600; margin-bottom:20px;">Yêu cầu
                                    cần được xử lý</p>
                                <p style="font-size:15px; color:#555; line-height:1.5; margin-bottom:25px;">
                                    Vui lòng xem xét và xử lý yêu cầu rút tiền này<br>trong vòng 24 giờ làm việc.
                                </p>
                                <a href="{{ url('/admin/withdrawals/' . $withdrawalRequest->id) }}"
                                    style="display:inline-block; background: linear-gradient(to right, #E27447, #f59776); color:#fff; padding:14px 30px; font-size:16px; text-decoration:none; border-radius:10px; font-weight:600; letter-spacing:0.5px; box-shadow:0 6px 15px rgba(226,116,71,0.3); transition: all 0.3s;">
                                    👉 XEM CHI TIẾT
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:5px 30px 30px;">
                            <div style="border-top:1px solid #eee; padding-top:20px; max-width: 90%; margin: 0 auto;">
                                <p style="font-size:15px; color:#555; line-height: 1.6; text-align: center;">
                                    Email này được gửi tự động từ hệ thống CourseMeLy. Đây là thông báo yêu cầu rút tiền
                                    mới cần được xử lý. Vui lòng không trả lời email này.
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
                                            Mọi quyền được bảo lưu.</p>
                                        <p style="font-size:13px; color:#999; margin:5px 0 0;">
                                            Email này được gửi tự động, vui lòng không trả lời. Nếu bạn cần hỗ trợ, vui
                                            lòng liên hệ
                                            <a href="mailto:coursemely@gmail.com"
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
