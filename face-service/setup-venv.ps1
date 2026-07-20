# Cài môi trường Python cho face-service (chạy tại thư mục gốc dự án)
# Usage: powershell -ExecutionPolicy Bypass -File face-service\setup-venv.ps1

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

$pyCandidates = @(
    "$env:LOCALAPPDATA\Programs\Python\Python311\python.exe",
    "$env:LOCALAPPDATA\Programs\Python\Python312\python.exe",
    "python"
)

$python = $null
foreach ($candidate in $pyCandidates) {
    try {
        if ($candidate -eq "python") {
            $ver = & python --version 2>&1
            if ($LASTEXITCODE -eq 0 -and "$ver" -match "Python 3\.") {
                $python = "python"
                break
            }
        } elseif (Test-Path $candidate) {
            $python = $candidate
            break
        }
    } catch {
        continue
    }
}

if (-not $python) {
    Write-Host "Chua cai Python 3.11+. Tai tai: https://www.python.org/downloads/" -ForegroundColor Red
    Write-Host "Khi cai, bat tick 'Add python.exe to PATH'." -ForegroundColor Yellow
    exit 1
}

Write-Host "Dung Python: $python"
& $python --version

if (-not (Test-Path ".\venv\Scripts\python.exe")) {
    Write-Host "Tao venv..."
    & $python -m venv venv
}

Write-Host "Cai packages tu requirements.txt..."
& .\venv\Scripts\python.exe -m pip install --upgrade pip
& .\venv\Scripts\python.exe -m pip install -r requirements.txt

Write-Host "Kiem tra import..."
& .\venv\Scripts\python.exe -c "import numpy, onnxruntime, flask; import cv2; import insightface; print('OK', cv2.__version__)"

if (-not (Test-Path "face-service\.env")) {
    Copy-Item "face-service\.env.example" "face-service\.env"
    Write-Host "Da tao face-service\.env tu .env.example - hay sua FACE_KIOSK_TOKEN cho trung Laravel." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Xong. Moi lan dung:" -ForegroundColor Green
Write-Host "  .\venv\Scripts\activate"
Write-Host "  python face-service\api_server.py"
