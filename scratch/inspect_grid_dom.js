const { spawn } = require('child_process');
const http = require('http');

async function inspectGrid() {
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9255;
    const proc = spawn(chromePath, [
        '--headless=new',
        `--remote-debugging-port=${port}`,
        '--no-sandbox',
        '--disable-gpu',
        'http://localhost/shop/'
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
        await send('Runtime.enable');
        await send('DOM.enable');

        async function evalJs(expr) {
            const res = await send('Runtime.evaluate', { expression: expr, returnByValue: true });
            return (res && res.result) ? res.result.value : undefined;
        }

        // Set 1440 viewport
        await send('Emulation.setDeviceMetricsOverride', {
            width: 1440,
            height: 900,
            deviceScaleFactor: 1,
            mobile: false
        });

        await new Promise(r => setTimeout(r, 1000));

        const gridInfo = await evalJs(`(() => {
            const ul = document.querySelector('.products');
            if (!ul) return { error: 'No .products found, body children: ' + document.body.children.length };

            const ulStyle = window.getComputedStyle(ul);
            const beforeStyle = window.getComputedStyle(ul, '::before');
            const afterStyle = window.getComputedStyle(ul, '::after');

            const children = Array.from(ul.children).map((c, i) => {
                const r = c.getBoundingClientRect();
                const s = window.getComputedStyle(c);
                return {
                    index: i,
                    tag: c.tagName,
                    class: c.className.split(' ').slice(0, 4).join(' '),
                    x: Math.round(r.x),
                    y: Math.round(r.y),
                    width: Math.round(r.width),
                    height: Math.round(r.height),
                    gridColumnStart: s.gridColumnStart,
                    gridColumnEnd: s.gridColumnEnd,
                    gridRowStart: s.gridRowStart,
                    gridRowEnd: s.gridRowEnd,
                    display: s.display,
                    visibility: s.visibility,
                    clear: s.clear,
                    float: s.float
                };
            });

            return {
                ulTag: ul.tagName,
                ulClass: ul.className,
                display: ulStyle.display,
                gridTemplateColumns: ulStyle.gridTemplateColumns,
                gridAutoFlow: ulStyle.gridAutoFlow,
                before: {
                    content: beforeStyle.content,
                    display: beforeStyle.display,
                    width: beforeStyle.width,
                    height: beforeStyle.height,
                    gridColumnStart: beforeStyle.gridColumnStart,
                    gridColumnEnd: beforeStyle.gridColumnEnd,
                    gridRowStart: beforeStyle.gridRowStart,
                    gridRowEnd: beforeStyle.gridRowEnd
                },
                after: {
                    content: afterStyle.content,
                    display: afterStyle.display,
                    width: afterStyle.width,
                    height: afterStyle.height
                },
                childCount: ul.children.length,
                children: children
            };
        })()`);

        const testFixResult = await evalJs(`(() => {
            const style = document.createElement('style');
            style.id = 'hotfix-test';
            style.textContent = '.woocommerce ul.products::before, .woocommerce ul.products::after, .woocommerce-page ul.products::before, .woocommerce-page ul.products::after, ul.products::before, ul.products::after { content: none !important; display: none !important; }';
            document.head.appendChild(style);

            const ul = document.querySelector('.products');
            const ulStyle = window.getComputedStyle(ul);
            const beforeStyle = window.getComputedStyle(ul, '::before');
            const afterStyle = window.getComputedStyle(ul, '::after');

            const children = Array.from(ul.children).map((c, i) => {
                const r = c.getBoundingClientRect();
                const s = window.getComputedStyle(c);
                return {
                    index: i,
                    class: c.className.split(' ').slice(0, 3).join(' '),
                    x: Math.round(r.x),
                    y: Math.round(r.y),
                    width: Math.round(r.width),
                    height: Math.round(r.height),
                    gridColumnStart: s.gridColumnStart,
                    gridColumnEnd: s.gridColumnEnd,
                    gridRowStart: s.gridRowStart,
                    gridRowEnd: s.gridRowEnd
                };
            });

            return {
                beforeDisplay: beforeStyle.display,
                beforeContent: beforeStyle.content,
                beforeWidth: beforeStyle.width,
                afterDisplay: afterStyle.display,
                children: children
            };
        })()`);

        console.log('--- WITH FIX APPLIED ---');
        console.log(JSON.stringify(testFixResult, null, 2));

        ws.close();
        proc.kill();
    } catch (err) {
        console.error(err);
        proc.kill();
    }
}

inspectGrid();
