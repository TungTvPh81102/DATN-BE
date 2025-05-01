<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Báo cáo bình luận</title>
</head>
<body>
    <h2>Báo cáo bình luận từ người dùng</h2>

    <p><strong>Người gửi:</strong> {{ $data['reporter_name'] }}</p>
    <p><strong>Nội dung báo cáo:</strong> {{ $data['report_content'] }}</p>

    <hr>

    <p><strong>Khóa học:</strong> {{ $data['course_name'] }}</p>
    <p><strong>Chương:</strong> {{ $data['chapter_name'] }}</p>
    <p><strong>Bài học:</strong> {{ $data['lesson_name'] }}</p>

    <p><strong>Người bình luận:</strong> {{ $data['comment_author'] }}</p>
    <p><strong>Nội dung bình luận:</strong> {{ $data['comment_content'] }}</p>
</body>
</html>
