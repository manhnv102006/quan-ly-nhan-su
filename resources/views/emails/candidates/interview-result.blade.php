<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả phỏng vấn</title>
</head>
<body style="margin: 0; padding: 40px 24px; background-color: #f8fafc; color: #0f172a; font-family: Arial, Helvetica, sans-serif;">
    <div style="max-width: 680px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.05), 0 4px 6px -4px rgb(0 0 0 / 0.05);">
        <!-- Header Gradient -->
        <div style="padding: 36px 40px; background: linear-gradient(135deg, #0f766e, #0ea5e9); color: #ffffff;">
            <p style="margin: 0; font-size: 13px; letter-spacing: 0.15em; text-transform: uppercase; opacity: 0.85; font-weight: 600;">{{ config('app.name') }} • Talent Acquisition Department</p>
            <h1 style="margin: 14px 0 0; font-size: 26px; line-height: 1.4; font-weight: 700; letter-spacing: -0.02em;">
                {{ $isPassed ? 'Chúc mừng bạn đã xuất sắc vượt qua vòng phỏng vấn!' : 'Thông báo kết quả phỏng vấn & Lời cảm ơn' }}
            </h1>
        </div>

        <!-- Body Content -->
        <div style="padding: 40px;">
            <p style="margin: 0 0 24px; font-size: 16px; line-height: 1.7; color: #0f172a;">Kính gửi <strong>{{ $candidate->full_name }}</strong>,</p>

            @if ($isPassed)
                <!-- ==================== TRƯỜNG HỢP ĐẠT (PASSED) ==================== -->
                
                <!-- Hộp thông báo trực diện kết quả Đậu -->
                <div style="margin: 0 0 24px; padding: 16px 20px; background-color: #f0fdf4; border-left: 4px solid #16a34a; border-radius: 4px 8px 8px 4px;">
                    <p style="margin: 0; font-size: 16px; line-height: 1.6; color: #15803d; font-weight: 700;">
                        [THÔNG BÁO CHÍNH THỨC]: BẠN ĐÃ VƯỢT QUA VÒNG PHỎNG VẤN TRỰC TIẾP
                    </p>
                </div>

                <p style="margin: 0 0 18px; font-size: 16px; line-height: 1.8; color: #334155;">
                    Thay mặt Ban Giám đốc, Hội đồng tuyển dụng và toàn thể đội ngũ nhân sự tại <strong>{{ config('app.name') }}</strong>, chúng tôi xin gửi lời chúc mừng chân thành và nồng nhiệt nhất đến bạn. Trải qua những vòng đánh giá năng lực nghiêm túc và khắt khe, bạn đã xuất sắc chứng minh được bản lĩnh của mình để chính thức trúng tuyển 
                    @if ($jobTitle)
                        vị trí <span style="color: #0f766e; font-weight: 700;">{{ $jobTitle }}</span>
                    @endif
                    vừa qua.
                </p>
                <p style="margin: 0 0 18px; font-size: 16px; line-height: 1.8; color: #334155;">
                    Trong suốt buổi trò chuyện, Hội đồng chuyên môn của chúng tôi đã vô cùng ấn tượng trước nền tảng tư duy vững chắc, kỹ năng giải quyết vấn đề thực tế, cũng như tinh thần trách nhiệm và ngọn lửa nhiệt huyết mà bạn mang lại. Chúng tôi nhận thấy ở bạn không chỉ là một ứng viên có năng lực chuyên môn cao, mà còn là một người sở hữu những giá trị cốt lõi vô cùng tương đồng với văn hóa làm việc, định hướng phát triển dài hạn của tổ chức chúng tôi.
                </p>
                <p style="margin: 0 0 18px; font-size: 16px; line-height: 1.8; color: #334155;">
                    Tại {{ config('app.name') }}, chúng tôi luôn coi con người là tài sản quý giá nhất và liên tục tìm kiếm những người đồng hành có thể cùng nhau bứt phá, kiến tạo nên những giá trị mới. Sự hiện diện của bạn trong đội ngũ sắp tới được kỳ vọng sẽ mang lại một luồng gió mới, một nguồn năng lượng mạnh mẽ để thúc đẩy các dự án chiến lược đi đến thành công.
                </p>
                <p style="margin: 0 0 18px; font-size: 16px; line-height: 1.8; color: #334155;">
                    Để chuẩn bị thật chu đáo cho quá trình gia nhập và làm việc của bạn, Bộ phận Nhân sự (HR) sẽ liên hệ trực tiếp thông qua điện thoại và gửi email chính thức trong vòng <strong>1 đến 2 ngày làm việc tiếp theo</strong>. Nội dung liên hệ sẽ bao gồm Thư mời nhận việc (Offer Letter) chi tiết về mức lương, các chế độ đãi ngộ, bảo hiểm, phúc lợi, cùng với lộ trình đào tạo hội nhập (Onboarding) dành riêng cho bạn.
                </p>
                <p style="margin: 0 0 24px; font-size: 16px; line-height: 1.8; color: #334155;">
                    Một lần nữa, xin chân thành cảm ơn bạn đã tin tưởng lựa chọn {{ config('app.name') }} làm bến đỗ tiếp theo trên con đường sự nghiệp của mình. Chúc bạn có một tâm thế thật tốt và chúng tôi rất hào hứng được chào đón bạn chính thức trở thành một thành viên của đại gia đình chúng ta!
                </p>

            @else
                <!-- ==================== TRƯỜNG HỢP CHƯA ĐẠT (FAILED) ==================== -->
                
                <!-- Hộp thông báo trực diện kết quả Trượt -->
                <div style="margin: 0 0 24px; padding: 16px 20px; background-color: #fef2f2; border-left: 4px solid #dc2626; border-radius: 4px 8px 8px 4px;">
                    <p style="margin: 0; font-size: 16px; line-height: 1.6; color: #b91c1c; font-weight: 700;">
                        [THÔNG BÁO CHÍNH THỨC]: KẾT QUẢ CHƯA PHÙ HỢP VỚI TIÊU CHÍ TUYỂN DỤNG HIỆN TẠI
                    </p>
                </div>

                <p style="margin: 0 0 18px; font-size: 16px; line-height: 1.8; color: #334155;">
                    Lời đầu tiên, thay mặt Bộ phận Tuyển dụng và Hội đồng đánh giá của <strong>{{ config('app.name') }}</strong>, chúng tôi xin gửi lời cảm ơn sâu sắc nhất vì bạn đã dành thời gian, công sức và sự quan tâm rất lớn cho cơ hội nghề nghiệp 
                    @if ($jobTitle)
                        vị trí <span style="color: #475569; font-weight: 700;">{{ $jobTitle }}</span>
                    @endif
                    tại công ty chúng tôi.
                </p>
                <p style="margin: 0 0 18px; font-size: 16px; line-height: 1.8; color: #334155;">
                    Hội đồng tuyển dụng rất trân trọng buổi gặp gỡ, những chia sẻ thẳng thắn về kinh nghiệm thực tế cũng như góc nhìn chuyên môn thú vị mà bạn đã mang đến trong buổi phỏng vấn. Tuy nhiên, sau khi cân nhắc và đặt lên bàn cân một cách vô cùng kỹ lưỡng dựa trên tổng thể các tiêu chí sàng lọc khắt khe về mức độ tương thích với cấu trúc nhân sự hiện tại, quy mô vận hành của dự án và định hướng cốt lõi của tổ chức ở thời điểm này, chúng tôi rất tiếc khi phải thông báo <strong>chưa thể trao cơ hội đồng hành cùng bạn ở vị trí này vào lúc này</strong>.
                </p>
                <p style="margin: 0 0 18px; font-size: 16px; line-height: 1.8; color: #334155;">
                    Đây thực sự là một quyết định vô cùng khó khăn đối với đội ngũ nhân sự, bởi chiến dịch tuyển dụng lần này nhận được rất nhiều hồ sơ chất lượng và mỗi ứng viên đều mang những thế mạnh riêng biệt. Chúng tôi hiểu rằng, tuyển dụng là một hành trình tìm kiếm "mảnh ghép phù hợp nhất tại một thời điểm nhất định" chứ không thuần túy là việc định đoạt ai giỏi hơn ai. Vì vậy, kết quả chưa phù hợp ngày hôm nay hoàn toàn không phản ánh năng lực, kinh nghiệm hay giá trị bản thân của bạn, mà chỉ là do định hướng của hai bên chưa thực sự giao nhau ở cột mốc này.
                </p>
                <p style="margin: 0 0 18px; font-size: 16px; line-height: 1.8; color: #334155;">
                    Với sự trân trọng dành cho tiềm năng của bạn, nếu được bạn cho phép, {{ config('app.name') }} xin phép được lưu trữ hồ sơ thông tin (CV) của bạn trong Kho lưu trữ tài năng (Talent Pool) bảo mật của công ty. Trong tương lai gần, khi các dự án được mở rộng hoặc có những vị trí mới xuất hiện phù hợp hơn với thế mạnh vượt trội của bạn, chúng tôi sẽ chủ động ưu tiên liên hệ lại để thảo luận về các cơ hội hợp tác mới.
                </p>
                <p style="margin: 0 0 24px; font-size: 16px; line-height: 1.8; color: #334155;">
                    Chúc bạn luôn giữ vững ngọn lửa nhiệt huyết, đam mê với nghề và không ngừng gặt hái được những thành tựu rực rỡ trên con đường sự nghiệp phía trước. Rất hy vọng sẽ có một cơ hội khác tốt đẹp hơn để chúng ta có thể làm việc cùng nhau trong tương lai!
                </p>
            @endif

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