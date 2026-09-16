const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const path = require('path');

const viewports = [
    { width: 1920, height: 1080, name: 'screen_1920', mobile: false },
    { width: 1440, height: 900,  name: 'screen_1440', mobile: false },
    { width: 1280, height: 800,  name: 'screen_1280', mobile: false },
    { width: 1024, height: 768,  name: 'screen_1024', mobile: false },
    { width: 768,  height: 1024, name: 'screen_768',  mobile: true },
    { width: 430,  height: 932,  name: 'screen_430',  mobile: true },
    { width: 375,  height: 812,  name: 'screen_375',  mobile: true },
    { width: 320,  height: 568,  name: 'screen_320',  mobile: true }
];

const outDir = 'C:\\Users\\kumar\\.gemini\\antigravity-ide\\brain\\68126d3d-7856-46a1-bbb4-09a5e2aae306\\.tempmediaStorage';
if (!fs.existsSync(outDir)) {
    fs.mkdirSync(outDir, { recursive: true });
}

async function main() {
    console.log('=== STARTING MULTI-VIEWPORT CDP EMULATION CAPTURE ===');
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9226;
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
            console.error('No page target found');
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

        await send('Page.enable');

        for (const vp of viewports) {
            console.log(`\n--- Emulating ${vp.name} (${vp.width}x${vp.height}) ---`);
            await send('Emulation.setDeviceMetricsOverride', {
                width: vp.width,
                height: vp.height,
                deviceScaleFactor: 1,
                mobile: vp.mobile
            });

            await new Promise(r => setTimeout(r, 600));

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
                        windowWidth: docWidth,
                        scrollWidth: docScroll,
                        hasOverflow: docScroll > docWidth,
                        offendersCount: offenders.length,
                        offenders: offenders.slice(0, 3)
                    });
                })()`,
                returnByValue: true
            });

            const diag = typeof evalRes.result.value === 'string' ? JSON.parse(evalRes.result.value) : (evalRes.result.value || {});
            console.log(`Diag: innerWidth=${diag.windowWidth}, scrollWidth=${diag.scrollWidth}, hasOverflow=${diag.hasOverflow}, offenders=${diag.offendersCount}`);
            if (diag.offendersCount > 0) {
                console.log('Offenders:', diag.offenders);
            }

            const snap = await send('Page.captureScreenshot', {
                format: 'png',
                clip: {
                    x: 0,
                    y: 0,
                    width: vp.width,
                    height: vp.height,
                    scale: 1
                }
            });

            const filePath = path.join(outDir, `${vp.name}.png`);
            fs.writeFileSync(filePath, Buffer.from(snap.data, 'base64'));
            console.log(`Saved screenshot: ${vp.name}.png (${fs.statSync(filePath).size} bytes)`);
        }

        ws.close();
        console.log('\n=== ALL 8 VIEWPORTS CAPTURED SUCCESSFULLY ===');
    } catch (err) {
        console.error('Fatal error:', err);
    } finally {
        proc.kill();
    }
}

main();
