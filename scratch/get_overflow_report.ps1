$chrome = "C:\Program Files\Google\Chrome\Application\chrome.exe"
$out = Start-Process -FilePath $chrome -ArgumentList @(
    "--headless=new",
    "--no-sandbox",
    "--disable-gpu",
    "--window-size=375,812",
    "--virtual-time-budget=2500",
    "--dump-dom",
    "http://localhost/"
) -RedirectStandardOutput "scratch/dom_dump.html" -Wait -PassThru

$content = [System.IO.File]::ReadAllText("scratch/dom_dump.html")
if ($content -match '<div id="overflow-debug-report"[^>]*>(.*?)</div>') {
    Write-Host "FOUND REPORT:"
    Write-Host $matches[1]
} else {
    Write-Host "Report div not found."
}
