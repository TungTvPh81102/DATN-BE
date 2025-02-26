<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Completion</title>
</head>

<body style="font-family: 'DejaVu Sans', sans-serif; text-align: center; margin: 0; padding: 0;background-image:url('{{public_path('assets/images/pngtree-red-and-gold-color-certificate-border-with-modern-style-png-image_6581429.png')}}');background-size: cover;">
    <div
        style="position: relative;width: 900px; min-height: 400px; border-radius: 15px; margin: 100px auto; box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.2); padding: 40px;">
        <table style="width: 100%; position: absolute; top: 15px; left: 30px;">
            <tr>
                <td style="width: 50%; text-align: left;">
                    <img src="{{ $image['daudo'] }}"
                        style="width: 100px; height: auto;" alt="Dấu đỏ Course MeLy">
                </td>
            </tr>
        </table>
        <div
            style="font-size: 26px; font-weight: bold; text-transform: uppercase; color: #e67e22; margin-bottom: 10px;">
            GIẤY CHỨNG NHẬN HOÀN THÀNH</div>

        <div style="font-size: 18px; font-style: italic; color: #7f8c8d; margin-bottom: 20px;">Xin chúc mừng,
            <strong>{{ $user->name }}</strong>
        </div>

        <div
            style="font-size: 24px; font-weight: bold; color: #2980b9; margin-bottom: 20px; text-decoration: underline;">
            {{ $course->name }}</div>

        <div style="font-size: 16px; font-weight: bold; color: #2c3e50; margin-bottom: 20px;">Khóa học hoàn thành vào
            ngày <strong>{{ \Carbon\Carbon::now(env('APP_TIMEZONE'))->locale('vi')->translatedFormat('d F, Y') }}
            </strong></div>

        <div style="font-size: 14px; color: #7f8c8d; margin-bottom: 30px;">Cảm ơn bạn vì tất cả sự chăm chỉ và cống hiến
            của mình. Hãy tiếp tục học hỏi, vì càng có nhiều kiến thức, bạn càng có cơ hội thành công trong cuộc sống.
        </div>

        <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; text-align: left; vertical-align: middle;">
                    <div style="text-align: center">
                        <img src="{{ $image['logo'] }}" alt="Course MeLy"
                            style="width: 40px; vertical-align: middle;">
                        <span
                            style="color: red; font-size: 18px; font-weight: bold; vertical-align: middle; margin-left: 10px;">Course
                            MeLy</span>
                    </div>
                </td>

                <td style="width: 50%; text-align: right; vertical-align: middle;">
                    <div style="text-align: center">
                        <img src="{{ url('images/signature.png') }}" alt="Chữ ký" style="width: 120px;">
                        <p style="margin: 5px 0 0 0; font-weight: bold;">{{ $course->user->name }}</p>
                    </div>
                </td>
            </tr>
        </table>

        <p style="font-size: 14px; color: #7f8c8d; margin-top: 20px;">Chứng nhận số:
            {{ strtoupper(substr(md5($user->id . $course->id), 0, 10)) }}</p>
    </div>
</body>

</html>
