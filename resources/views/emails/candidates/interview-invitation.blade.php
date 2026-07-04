<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư mời phỏng vấn</title>
</head>
<body style="margin: 0; padding: 40px 24px; background-color: #f8fafc; color: #0f172a; font-family: Arial, Helvetica, sans-serif;">
    <div style="max-width: 680px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.05), 0 4px 6px -4px rgb(0 0 0 / 0.05);">
        <!-- Header với Gradient -->
        <div style="padding: 36px 40px; background: linear-gradient(135deg, #0891b2, #0f766e); color: #ffffff;">
            <p style="margin: 0; font-size: 13px; letter-spacing: 0.15em; text-transform: uppercase; opacity: 0.85; font-weight: 600;">{{ config('app.name') }} • Talent Acquisition Department</p>
            <h1 style="margin: 14px 0 0; font-size: 26px; line-height: 1.4; font-weight: 700; letter-spacing: -0.02em;">Thư Mời Tham Gia Vòng Phỏng Vấn Trực Tiếp</h1>
        </div>

        <!-- Body Content -->
        <div style="padding: 40px;">
            <p style="margin: 0 0 24px; font-size: 16px; line-height: 1.7; color: #0f172a;">Kính gửi <strong>{{ $candidate->full_name }}</strong>,</p>

            <p style="margin: 0 0 18px; font-size: 16px; line-height: 1.8; color: #334155;">
                Lời đầu tiên, thay mặt Bộ phận Tuyển dụng và Hội đồng đánh giá năng lực tại <strong>{{ config('app.name') }}</strong>, chúng tôi xin gửi lời chào trân trọng nhất đến bạn. Cảm ơn bạn đã quan tâm và dành thời gian nộp hồ sơ ứng tuyển vào doanh nghiệp của chúng tôi trong chiến dịch tuyển dụng lần này.
            </p>
            
            <p style="margin: 0 0 24px; font-size: 16px; line-height: 1.8; color: #334155;">
                Trải qua vòng sàng lọc hồ sơ ban đầu, Hội đồng chuyên môn đánh giá rất cao nền tảng kiến thức tư duy, kinh nghiệm thực tế cũng như sự chuẩn bị chu đáo trong CV của bạn. Nhận thấy tiềm năng và những định hướng phát triển cá nhân của bạn có mức độ tương thích cao với tiêu chí văn hóa cũng như yêu cầu chuyên môn của tổ chức, chúng tôi trân trọng kính mời bạn tham gia buổi phỏng vấn trực tiếp nhằm trao đổi sâu hơn về cơ hội hợp tác 
                @if ($jobTitle)
                    cho vị trí <span style="color: #0f766e; font-weight: 700;">{{ $jobTitle }}</span>
                @endif
                tại công ty.
            </p>

            <!-- Hộp Thông Tin Lịch Hẹn Trực Diện (Nổi bật, dễ nhìn nhất) -->
            <div style="margin: 0 0 24px; padding: 24px; border: 1px solid #bae6fd; border-left: 5px solid #0891b2; border-radius: 8px; background-color: #f0f9ff;">
                <p style="margin: 0 0 12px; font-size: 16px; line-height: 1.6; color: #0369a1;">
                    <strong style="text-transform: uppercase; letter-spacing: 0.05em;">Chi tiết lịch hẹn phỏng vấn:</strong>
                </p>
                <table style="width: 100%; border-collapse: collapse; font-size: 15px; line-height: 1.6; color: #1e293b;">
                    <tr>
                        <td style="padding: 6px 0; vertical-align: top; width: 150px;"><strong>Thời gian:</strong></td>
                        <td style="padding: 6px 0; vertical-align: top; color: #0369a1; font-weight: 700;">{{ $interviewTime }}</td>
                    </tr>
                    @if ($interviewerName)
                    <tr>
                        <td style="padding: 6px 0; vertical-align: top;"><strong>Người phỏng vấn:</strong></td>
                        <td style="padding: 6px 0; vertical-align: top;">{{ $interviewerName }}</td>
                    </tr>
                    @endif
                    @if ($note)
                    <tr>
                        <td style="padding: 6px 0; vertical-align: top;"><strong>Ghi chú / Địa điểm:</strong></td>
                        <td style="padding: 6px 0; vertical-align: top; color: #475569; font-style: italic;">{{ $note }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <!-- Các dặn dò bổ sung dài, chi tiết -->
            <p style="margin: 0 0 18px; font-size: 16px; line-height: 1.8; color: #334155;">
                Buổi gặp gỡ này không chỉ là dịp để công ty đánh giá chi tiết hơn về năng lực chuyên môn thực tế, mà còn là cơ hội tuyệt vời để chính bạn tìm hiểu sâu hơn về môi trường làm việc, lộ trình phát triển sự nghiệp, cũng như văn hóa vận hành cốt lõi tại {{ config('app.name') }}. 
            </p>

            <p style="margin: 0 0 18px; font-size: 16px; line-height: 1.8; color: #334155;">
                Để buổi phỏng vấn diễn ra thuận lợi nhất, bạn vui lòng chuẩn bị trang phục lịch sự, có mặt trước giờ hẹn từ <strong>5 - 10 phút</strong> để check-in và mang theo thiết bị cá nhân (nếu vị trí có bài test trực tiếp) hoặc các tài liệu liên quan đến sản phẩm/dự án bạn đã từng thực hiện (nếu có).
            </p>

            <p style="margin: 0 0 24px; font-size: 16px; line-height: 1.8; color: #334155;">
                <strong>Xác nhận lịch hẹn:</strong> Bạn vui lòng phản hồi lại email này theo cú pháp <em>"Xác nhận tham gia phỏng vấn"</em> trước giờ hẹn tối thiểu 24 tiếng để chúng tôi kịp thời chuẩn bị phòng họp và tiếp đón chu đáo nhất. Trong trường hợp có sự cố bất khả kháng hoặc muốn thay đổi lịch trình vì lý do cá nhân, xin vui lòng thông báo sớm cho Bộ phận Tuyển dụng bằng cách phản hồi email này để được hỗ trợ sắp xếp lại khung giờ phù hợp hơn.
            </p>

            <p style="margin: 0 0 24px; font-size: 16px; line-height: 1.8; color: #334155;">
                Chúc bạn có một sự chuẩn bị thật tốt và giữ vững tâm thế tự tin nhất cho buổi trò chuyện sắp tới. Chúng tôi rất mong chờ được gặp bạn!
            </p>

            <!-- Footer Sign-off -->
            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 32px 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 0;">
                        <p style="margin: 0; font-size: 15px; line-height: 1.6; color: #64748b;">
                            Trân trọng,<br>
                            <strong style="color: #0f172a; font-size: 16px; display: inline-block; margin-top: 4px;">Phòng Phát triển & Thu hút Tài năng</strong><br>
                            <span style="font-size: 14px;">{{ config('app.name') }} Việt Nam</span>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>