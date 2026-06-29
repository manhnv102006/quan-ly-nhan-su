<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả phỏng vấn</title>
</head>
<body style="margin: 0; padding: 24px; background-color: #f8fafc; color: #0f172a; font-family: Arial, Helvetica, sans-serif;">
    <div style="max-width: 640px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; overflow: hidden;">
        <div style="padding: 24px 28px; background: linear-gradient(135deg, #0f766e, #0ea5e9); color: #ffffff;">
            <p style="margin: 0; font-size: 13px; letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.9;">{{ config('app.name') }}</p>
            <h1 style="margin: 12px 0 0; font-size: 24px; line-height: 1.35;">
                {{ $isPassed ? 'Chúc mừng bạn đã vượt qua vòng phỏng vấn' : 'Thông báo kết quả phỏng vấn' }}
            </h1>
        </div>

        <div style="padding: 28px;">
            <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.7;">Chào {{ $candidate->full_name }},</p>

            @if ($isPassed)
                <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.7;">
                    Chúc mừng bạn đã đạt vòng phỏng vấn
                    @if ($jobTitle)
                        cho vị trí <strong>{{ $jobTitle }}</strong>
                    @endif
                    tại {{ config('app.name') }}.
                </p>
                <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.7;">
                    Bộ phận tuyển dụng sẽ sớm liên hệ với bạn để trao đổi các bước tiếp theo. Cảm ơn bạn đã dành thời gian tham gia buổi phỏng vấn cùng chúng tôi.
                </p>
            @else
                <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.7;">
                    Cảm ơn bạn đã tham gia phỏng vấn
                    @if ($jobTitle)
                        cho vị trí <strong>{{ $jobTitle }}</strong>
                    @endif
                    tại {{ config('app.name') }}.
                </p>
                <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.7;">
                    Sau khi xem xét, chúng tôi rất tiếc phải thông báo rằng bạn chưa phù hợp với nhu cầu tuyển dụng ở thời điểm hiện tại.
                </p>
                <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.7;">
                    Chúng tôi trân trọng thời gian và sự chuẩn bị của bạn, đồng thời hy vọng sẽ có dịp đồng hành cùng bạn trong những cơ hội tiếp theo.
                </p>
            @endif

            <p style="margin: 24px 0 0; font-size: 16px; line-height: 1.7;">
                Trân trọng,<br>
                <strong>{{ config('app.name') }}</strong>
            </p>
        </div>
    </div>
</body>
</html>
