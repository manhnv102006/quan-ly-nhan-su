document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('face-attendance-scanner');
    if (!root || root.dataset.showCamera !== '1') {
        return;
    }

    const scanEnabled = root.dataset.enabled === '1';
    const scanUrl = root.dataset.scanUrl;
    const intervalMs = Number.parseInt(root.dataset.intervalMs || '4000', 10);
    const video = root.querySelector('video');
    const canvas = root.querySelector('canvas');
    const statusEl = root.querySelector('[data-face-status]');
    const ringEl = root.querySelector('[data-face-ring]');

    if (!video || !canvas) {
        return;
    }

    let scanning = false;
    let stopped = false;
    let timer = null;
    let failCount = 0;

    const setStatus = (text) => {
        if (statusEl) {
            statusEl.textContent = text;
        }
    };

    const stopCamera = () => {
        const stream = video.srcObject;
        if (stream instanceof MediaStream) {
            stream.getTracks().forEach((track) => track.stop());
        }
        video.srcObject = null;
    };

    const stopScanning = (message) => {
        stopped = true;
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
        stopCamera();
        if (message) {
            setStatus(message);
        }
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

        ctx.drawImage(video, 0, 0, width, height);
        return canvas.toDataURL('image/jpeg', 0.72);
    };

    const scanOnce = async () => {
        if (!scanEnabled || !scanUrl || scanning || stopped || video.readyState < 2) {
            return;
        }

        scanning = true;
        ringEl?.classList.add('scanning');

        try {
            const imageBase64 = captureFrame();
            if (!imageBase64) {
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

            const response = await fetch(scanUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ image_base64: imageBase64 }),
                credentials: 'same-origin',
            });

            const payload = await response.json().catch(() => ({}));
            const message = String(payload.message || '');

            if (payload.success) {
                stopScanning(message || 'Chấm công thành công.');
                window.setTimeout(() => window.location.reload(), 1800);
                return;
            }

            if (payload.action === 'done') {
                stopScanning(message || 'Bạn đã hoàn tất chấm công hôm nay.');
                return;
            }

            if (response.status === 429) {
                setStatus(message || 'Đang xử lý ảnh trước, vui lòng chờ...');
                return;
            }

            if (message) {
                failCount += 1;
                if (typeof payload.score === 'number' && payload.score > 0) {
                    const pct = Math.round(payload.score * 100);
                    setStatus(`${message} (lần thử ${failCount})`);
                } else {
                    setStatus(`${message} (lần thử ${failCount})`);
                }
            } else {
                setStatus('Không nhận diện được. Kiểm tra Face API (port 5555).');
            }
        } catch {
            setStatus('Không kết nối được máy chủ. Kiểm tra Laravel và Face API (port 5555).');
        } finally {
            scanning = false;
            ringEl?.classList.remove('scanning');
        }
    };

    const startCamera = async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            setStatus('Trình duyệt không hỗ trợ webcam.');
            return;
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                },
                audio: false,
            });

            video.srcObject = stream;
            await video.play();

            if (scanEnabled) {
                setStatus('Đưa mặt vào khung — hệ thống tự động chấm công...');
                timer = window.setInterval(scanOnce, intervalMs);
                window.setTimeout(scanOnce, 800);
            } else {
                setStatus('Camera đã bật. Chưa trong khung giờ chấm công tự động.');
            }
        } catch {
            setStatus('Không mở được webcam. Hãy cho phép quyền truy cập camera.');
        }
    };

    startCamera();

    window.addEventListener('beforeunload', () => {
        stopScanning();
    });
});
