$chrome = "C:\Program Files\Google\Chrome\Application\chrome.exe"
$outDir = "C:\Users\kumar\.gemini\antigravity-ide\brain\68126d3d-7856-46a1-bbb4-09a5e2aae306\.tempmediaStorage"

if (-not (Test-Path $outDir)) {
    New-Item -ItemType Directory -Path $outDir -Force | Out-Null
}

$viewports = @(
    @{ width = 1920; height = 1080; name = "screen_1920" },
    @{ width = 1440; height = 900;  name = "screen_1440" },
    @{ width = 1280; height = 800;  name = "screen_1280" },
    @{ width = 1024; height = 768;  name = "screen_1024" },
    @{ width = 768;  height = 1024; name = "screen_768" },
    @{ width = 430;  height = 932;  name = "screen_430" },
    @{ width = 375;  height = 812;  name = "screen_375" },
    @{ width = 320;  height = 568;  name = "screen_320" }
)

foreach ($vp in $viewports) {
    $outFile = Join-Path $outDir "$($vp.name).png"
    Write-Host "Capturing $($vp.name) ($($vp.width)x$($vp.height))..."
    $proc = Start-Process -FilePath $chrome -ArgumentList @(
        "--headless=new",
        "--no-sandbox",
        "--disable-gpu",
        "--window-size=$($vp.width),$($vp.height)",
        "--virtual-time-budget=3000",
        "--screenshot=$outFile",
        "http://localhost/"
    ) -Wait -PassThru
    Write-Host "Captured $($vp.name) with exit code $($proc.ExitCode)"
}

Write-Host "All screenshots successfully captured."
