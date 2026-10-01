const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const path = require('path');

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const viewports = [
    { name: '320px (Mobile Small)', width: 320, height: 568 },
    { name: '375px (Mobile Standard)', width: 375, height: 667 },
    { name: '430px (Mobile Large)', width: 430, height: 932 },
    { name: '768px (Tablet Portrait)', width: 768, height: 1024 },
    { name: '1024px (Tablet Landscape/Small Desktop)', width: 1024, height: 768 },
    { name: '1280px (Desktop Medium)', width: 1280, height: 800 },
    { name: '1440px (Desktop Large)', width: 1440, height: 900 },
    { name: '1920px (Full HD)', width: 1920, height: 1080 }
];

let currentPort = 9230;

async function testPage(url, pageName) {
    const port = currentPort++;
    console.log(`\n======================================================`);
    console.log(`TESTING VIEWPORTS FOR: ${pageName} (${url}) on port ${port}`);
    console.log(`======================================================`);

    const proc = spawn(chromePath, [
        '--headless=new',
        `--remote-debugging-port=${port}`,
        '--no-sandbox',
        '--disable-gpu',
        '--window-size=1440,900',
        url
    ]);

    await new Promise(r => setTimeout(r, 2500));

    try {
        const listData = await new Promise((resolve, reject) => {
            http.get(`http://127.0.0.1:${port}/json`, res => {
                let d = '';
                res.on('data', chunk => d += chunk);
                res.on('end', () => resolve(JSON.parse(d)));
            }).on('error', reject);
        });

        const targetPage = listData.find(x => x.type === 'page');
        if (!targetPage) {
            console.error('No page target found');
            proc.kill();
            return;
        }

        const ws = new WebSocket(targetPage.webSocketDebuggerUrl);
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
            await send('Emulation.setDeviceMetricsOverride', {
                width: vp.width,
                height: vp.height,
                deviceScaleFactor: 1,
                mobile: vp.width < 768
            });

            await new Promise(r => setTimeout(r, 400));

            const check = await send('Runtime.evaluate', {
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
                                cls: typeof el.className === 'string' ? el.className.split(' ').slice(0, 3).join(' ') : '',
                                right: Math.round(rect.right),
                                width: Math.round(rect.width)
                            });
                        }
                    }
                    var hasOverflow = docScroll > docWidth || bodyScroll > docWidth;
                    return JSON.stringify({
                        windowWidth: docWidth,
                        docScroll: docScroll,
                        bodyScroll: bodyScroll,
                        hasOverflow: hasOverflow,
                        offendersCount: hasOverflow ? offenders.length : 0,
                        offenders: hasOverflow ? offenders.slice(0, 5) : []
                    });
                })()`
            });

            if (!check || !check.result || !check.result.value) {
                console.log(`[SKIP] Could not evaluate ${vp.name}`);
                continue;
            }

            const res = JSON.parse(check.result.value);
            const pass = !res.hasOverflow && res.offendersCount === 0;
            const status = pass ? '[PASS]' : '[FAIL]';
            console.log(`${status} ${vp.name.padEnd(35)} (Window: ${res.windowWidth}px, docScroll: ${res.docScroll}px, bodyScroll: ${res.bodyScroll}px, offenders: ${res.offendersCount})`);

            if (!pass && res.offenders.length > 0) {
                console.log('   Offenders:', JSON.stringify(res.offenders));
            }
        }

        // Take a 1440px desktop snapshot
        await send('Emulation.setDeviceMetricsOverride', {
            width: 1440,
            height: 900,
            deviceScaleFactor: 1,
            mobile: false
        });
        await new Promise(r => setTimeout(r, 500));
        const snap = await send('Page.captureScreenshot', { format: 'png' });
        const snapPath = path.join(__dirname, `screenshot_${pageName.toLowerCase().replace(/[^a-z0-9]/g, '_')}_1440.png`);
        fs.writeFileSync(snapPath, Buffer.from(snap.data, 'base64'));
        console.log(`Saved screenshot: ${snapPath}`);

        ws.close();
    } catch (err) {
        console.error('Error in testPage:', err);
    } finally {
        proc.kill();
    }
}

async function main() {
    await testPage('http://localhost/testimonials/', 'testimonials_archive');
    await testPage('http://localhost/testimonials/chef-vikramaditya-rathore/', 'single_testimonial');
    await testPage('http://localhost/', 'homepage_testimonials');
}

main();
