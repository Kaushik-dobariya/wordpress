const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const path = require('path');

async function captureFullPage() {
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9227;
    const proc = spawn(chromePath, [
        '--headless=new',
        `--remote-debugging-port=${port}`,
        '--no-sandbox',
        '--disable-gpu',
        'http://localhost/'
    ]);

    await new Promise(r => setTimeout(r, 2000));

    try {
        const listData = await new Promise((resolve, reject) => {
            http.get(`http://127.0.0.1:${port}/json`, res => {
                let d = '';
                res.on('data', chunk => d += chunk);
                res.on('end', () => resolve(JSON.parse(d)));
            }).on('error', reject);
        });

        const page = listData.find(x => x.type === 'page');
        const ws = new WebSocket(page.webSocketDebuggerUrl);
        await new Promise((resolve, reject) => {
            ws.onopen = resolve;
            ws.onerror = reject;
        });

        let id = 1;
        function send(method, params = {}) {
            return new Promise(res => {
                const msgId = id++;
                const handler = e => {
                    const data = JSON.parse(e.data);
                    if (data.id === msgId) {
                        ws.removeEventListener('message', handler);
                        res(data.result);
                    }
                };
                ws.addEventListener('message', handler);
                ws.send(JSON.stringify({ id: msgId, method, params }));
            });
        }

        await send('Page.enable');
        await send('Emulation.setDeviceMetricsOverride', {
            width: 1440,
            height: 900,
            deviceScaleFactor: 1,
            mobile: false
        });

        // Trigger lazy loading by scrolling down
        await send('Runtime.evaluate', {
            expression: `(async function() {
                window.scrollTo(0, document.body.scrollHeight / 3);
                await new Promise(r => setTimeout(r, 400));
                window.scrollTo(0, (document.body.scrollHeight / 3) * 2);
                await new Promise(r => setTimeout(r, 400));
                window.scrollTo(0, document.body.scrollHeight);
                await new Promise(r => setTimeout(r, 600));
                window.scrollTo(0, 0);
            })()`,
            awaitPromise: true
        });

        await new Promise(r => setTimeout(r, 1200));

        const heightRes = await send('Runtime.evaluate', {
            expression: 'Math.max(document.body.scrollHeight, document.documentElement.scrollHeight)'
        });
        const height = Math.ceil(heightRes.result.value);

        const snap = await send('Page.captureScreenshot', {
            format: 'png',
            captureBeyondViewport: true,
            clip: {
                x: 0,
                y: 0,
                width: 1440,
                height: height,
                scale: 1
            }
        });

        const outPath = 'C:\\Users\\kumar\\.gemini\\antigravity-ide\\brain\\68126d3d-7856-46a1-bbb4-09a5e2aae306\\.tempmediaStorage\\screen_full_homepage.png';
        fs.writeFileSync(outPath, Buffer.from(snap.data, 'base64'));
        console.log(`Full page screenshot saved (${fs.statSync(outPath).size} bytes, height: ${height}px)`);

        ws.close();
    } catch (err) {
        console.error('Error capturing full page:', err);
    } finally {
        proc.kill();
    }
}

captureFullPage();
