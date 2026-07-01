<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư mời phỏng vấn</title>
</head>
<body style="margin: 0; padding: 24px; background-color: #f8fafc; color: #0f172a; font-family: Arial, Helvetica, sans-serif;">
    <div style="max-width: 640px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; overflow: hidden;">
        <div style="padding: 24px 28px; background: linear-gradient(135deg, #0891b2, #0f766e); color: #ffffff;">
            <p style="margin: 0; font-size: 13px; letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.9;">{{ config('app.name') }}</p>
            <h1 style="margin: 12px 0 0; font-size: 24px; line-height: 1.35;">Thư mời phỏng vấn</h1>
        </div>

        <div style="padding: 28px;">
            <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.7;">Chào {{ $candidate->full_name }},</p>

            <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.7;">
                {{ config('app.name') }} trân trọng mời bạn đến công ty tham gia buổi phỏng vấn
                @if ($jobTitle)
                    cho vị trí <strong>{{ $jobTitle }}</strong>
                @endif
                theo thông tin bên dưới.
            </p>

            <div style="margin: 24px 0; padding: 20px; border: 1px solid #bae6fd; border-radius: 16px; background-color: #f0f9ff;">
                <p style="margin: 0 0 10px; font-size: 15px; line-height: 1.6;"><strong>Thời gian:</strong> {{ $interviewTime }}</p>
                @if ($interviewerName)
                    <p style="margin: 0 0 10px; font-size: 15px; line-height: 1.6;"><strong>Người phỏng vấn:</strong> {{ $interviewerName }}</p>
                @endif
                @if ($note)
                    <p style="margin: 0; font-size: 15px; line-height: 1.6;"><strong>Ghi chú:</strong> {{ $note }}</p>
                @endif
            </div>

            <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.7;">
                Vui lòng có mặt đúng giờ và chuẩn bị các giấy tờ cần thiết nếu có. Nếu bạn cần đổi lịch, hãy phản hồi email này để bộ phận tuyển dụng hỗ trợ.
            </p>

            <p style="margin: 24px 0 0; font-size: 16px; line-height: 1.7;">
                Trân trọng,<br>
                <strong>{{ config('app.name') }}</strong>
            </p>
        </div>
    </div>
</body>
</html>
