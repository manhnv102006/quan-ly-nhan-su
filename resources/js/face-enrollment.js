document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('face-enrollment-modal');
    if (!modal) {
        return;
    }

    const requiredSamples = Number.parseInt(modal.dataset.requiredSamples || '5', 10);
    const video = modal.querySelector('video');
    const canvas = modal.querySelector('canvas');
    const statusEl = modal.querySelector('[data-enroll-status]');
    const progressEl = modal.querySelector('[data-enroll-progress]');
    const employeeNameEl = modal.querySelector('[data-enroll-employee-name]');
    const captureBtn = modal.querySelector('[data-enroll-capture]');
    const submitBtn = modal.querySelector('[data-enroll-submit]');
    const closeButtons = modal.querySelectorAll('[data-enroll-close]');

    let stream = null;
    let enrollUrl = '';
    let samples = [];
    let capturing = false;

    const setStatus = (text) => {
        if (statusEl) {
            statusEl.textContent = text;
        }
    };

    const updateProgress = () => {
        if (progressEl) {
            progressEl.textContent = `${samples.length}/${requiredSamples} mẫu`;
        }

        if (submitBtn) {
            submitBtn.disabled = samples.length < 3;
        }
    };

    const stopCamera = () => {
        if (stream instanceof MediaStream) {
            stream.getTracks().forEach((track) => track.stop());
        }
        stream = null;
        if (video) {
            video.srcObject = null;
        }
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        samples = [];
        enrollUrl = '';
        updateProgress();
        stopCamera();
        setStatus('Đang mở camera...');
    };

    const captureFrame = () => {
        const sourceWidth = video.videoWidth || 640;
        const sourceHeight = video.videoHeight || 480;
        const maxWidth = 480;
        const scale = sourceWidth > maxWidth ? maxWidth / sourceWidth : 1;
        const width = Math.max(1, Math.round(sourceWidth * scale));
        const height = Math.max(1, Math.round(sourceHeight * scale));

        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        if (!ctx) {
            return null;
        }

        ctx.save();
        ctx.translate(width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, width, height);
        ctx.restore();
        return canvas.toDataURL('image/jpeg', 0.8);
    };

    const startCamera = async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            setStatus('Trình duyệt không hỗ trợ webcam.');
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                },
                audio: false,
            });

            video.srcObject = stream;
            await video.play();
            setStatus('Nhìn thẳng vào camera, bấm "Chụp mẫu" đủ số lượng rồi lưu.');
        } catch {
            setStatus('Không mở được webcam. Hãy cho phép quyền truy cập camera.');
        }
    };

    const openModal = async (button) => {
        enrollUrl = button.dataset.enrollUrl || '';
        samples = [];
        updateProgress();

        if (employeeNameEl) {
            employeeNameEl.textContent = button.dataset.employeeName || '';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        await startCamera();
    };

    document.querySelectorAll('[data-enroll-open]').forEach((button) => {
        button.addEventListener('click', () => {
            openModal(button);
        });
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    captureBtn?.addEventListener('click', () => {
        if (video.readyState < 2) {
            setStatus('Camera chưa sẵn sàng.');
            return;
        }

        if (samples.length >= requiredSamples) {
            setStatus(`Đã đủ ${requiredSamples} mẫu. Bấm "Lưu đăng ký" để hoàn tất.`);
            return;
        }

        const frame = captureFrame();
        if (!frame) {
            setStatus('Không chụp được ảnh. Thử lại.');
            return;
        }

        samples.push(frame);
        updateProgress();
        setStatus(`Đã chụp mẫu ${samples.length}/${requiredSamples}. Xoay nhẹ đầu rồi chụp tiếp.`);
    });

    submitBtn?.addEventListener('click', async () => {
        if (!enrollUrl || samples.length < 3 || capturing) {
            return;
        }

        capturing = true;
        submitBtn.disabled = true;
        captureBtn.disabled = true;
        setStatus('Đang xử lý và lưu khuôn mặt...');

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

        try {
            const response = await fetch(enrollUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ samples }),
                credentials: 'same-origin',
            });

            const payload = await response.json().catch(() => ({}));

            if (payload.success) {
                closeModal();
                window.location.reload();
                return;
            }

            setStatus(payload.message || 'Không lưu được dữ liệu khuôn mặt.');
        } catch {
            setStatus('Không kết nối được máy chủ. Kiểm tra Laravel và Face API (port 5555).');
        } finally {
            capturing = false;
            captureBtn.disabled = false;
            submitBtn.disabled = samples.length < 3;
        }
    });

    window.addEventListener('beforeunload', () => {
        stopCamera();
    });
});
