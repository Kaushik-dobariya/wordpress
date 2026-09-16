const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const path = require('path');

async function captureViewportCDP(width, height, name) {
    console.log(`\n=== Capturing with Device Emulation: ${name} (${width}x${height}) ===`);
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9225;
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
        if (!page) {
            console.log('No page target found');
            proc.kill();
            return;
        }

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

        // Enable Page & Emulation
        await send('Page.enable');
        await send('Emulation.setDeviceMetricsOverride', {
            width: width,
            height: height,
            deviceScaleFactor: 1,
            mobile: width < 768
        });

        // Wait a moment for layout reflow
        await new Promise(r => setTimeout(r, 1000));

        // Evaluate window dimensions & overflow
        const evalRes = await send('Runtime.evaluate', {
            expression: `(function() {
                var docWidth = window.innerWidth;
                var docScroll = document.documentElement.scrollWidth;
                var bodyScroll = document.body.scrollWidth;
                var offenders = [];
                var all = document.querySelectorAll('*');
                for (var i = 0; i < all.length; i++) {
                    var el = all[i];
                    var rect = el.getBoundingClientRect();
                    if (rect.right > docWidth + 1) {
                        offenders.push({
                            tag: el.tagName,
                            id: el.id,
                            cls: typeof el.className === 'string' ? el.className.split(' ').slice(0, 2).join(' ') : '',
                            right: Math.round(rect.right),
                            width: Math.round(rect.width)
                        });
                    }
                }
                return JSON.stringify({
                    windowInnerWidth: docWidth,
                    docScrollWidth: docScroll,
                    bodyScrollWidth: bodyScroll,
                    hasHorizontalScroll: docScroll > docWidth,
                    offendersCount: offenders.length,
                    offenders: offenders.slice(0, 5)
                });
            })()`
        });

        const metrics = JSON.parse(evalRes.result.value);
        console.log('Metrics:', metrics);

        // Capture screenshot via CDP
        const snap = await send('Page.captureScreenshot', {
            format: 'png',
            clip: {
                x: 0,
                y: 0,
                width: width,
                height: height,
                scale: 1
            }
        });

        const outPath = path.join(
            'C:\\Users\\kumar\\.gemini\\antigravity-ide\\brain\\68126d3d-7856-46a1-bbb4-09a5e2aae306\\.tempmediaStorage',
            `${name}.png`
        );
        fs.writeFileSync(outPath, Buffer.from(snap.data, 'base64'));
        console.log(`Saved emulated screenshot to ${outPath}`);

        ws.close();
    } catch (err) {
        console.error('Error:', err);
    } finally {
        proc.kill();
    }
}

async function run() {
    await captureViewportCDP(375, 812, 'screen_375_emulated');
    await captureViewportCDP(320, 568, 'screen_320_emulated');
    await captureViewportCDP(430, 932, 'screen_430_emulated');
}
run();
