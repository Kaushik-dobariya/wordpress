const { spawn } = require('child_process');
const http = require('http');

async function testViewport(width, height) {
    console.log(`\n=== Testing Viewport: ${width}x${height} ===`);
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9224;
    const proc = spawn(chromePath, [
        '--headless=new',
        `--remote-debugging-port=${port}`,
        '--no-sandbox',
        '--disable-gpu',
        `--window-size=${width},${height}`,
        'http://localhost/'
    ]);

    await new Promise(r => setTimeout(r, 1500));

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

        // Evaluate overflow script
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
                            cls: typeof el.className === 'string' ? el.className.split(' ').slice(0, 3).join(' ') : '',
                            right: Math.round(rect.right),
                            width: Math.round(rect.width)
                        });
                    }
                }
                return JSON.stringify({
                    windowWidth: docWidth,
                    docScroll: docScroll,
                    bodyScroll: bodyScroll,
                    offendersCount: offenders.length,
                    offenders: offenders.slice(0, 15)
                });
            })()`
        });

        console.log('Result:', JSON.parse(evalRes.result.value));
        ws.close();
    } catch (err) {
        console.error('Error:', err);
    } finally {
        proc.kill();
    }
}

async function run() {
    await testViewport(375, 812);
    await testViewport(320, 568);
}
run();
