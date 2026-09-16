const http = require('http');
const { spawn } = require('child_process');
const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const port = 9228;
const proc = spawn(chromePath, [
    '--headless=new',
    `--remote-debugging-port=${port}`,
    '--no-sandbox',
    '--disable-gpu',
    'http://localhost/'
]);

setTimeout(async () => {
    try {
        const list = await new Promise((res, rej) => http.get('http://127.0.0.1:' + port + '/json', r => {
            let d = ''; r.on('data', c => d += c); r.on('end', () => res(JSON.parse(d)));
        }));
        const ws = new WebSocket(list[0].webSocketDebuggerUrl);
        await new Promise(r => ws.onopen = r);
        let id = 1;
        function send(m, p = {}) {
            return new Promise(res => {
                const mid = id++;
                const h = e => {
                    const data = JSON.parse(e.data);
                    if (data.id === mid) { ws.removeEventListener('message', h); res(data.result); }
                };
                ws.addEventListener('message', h);
                ws.send(JSON.stringify({ id: mid, method: m, params: p }));
            });
        }
        await send('Page.enable');
        await send('Emulation.setDeviceMetricsOverride', { width: 1920, height: 1080, deviceScaleFactor: 1, mobile: false });
        await new Promise(r => setTimeout(r, 1000));
        
        const res = await send('Runtime.evaluate', { expression: 'JSON.stringify({ scrollY: window.scrollY, headerClass: document.querySelector(".site-header").className, btnClass: document.getElementById("sc-scroll-top").className })' });
        console.log('1920 initial:', res.result.value);

        await send('Runtime.evaluate', { expression: 'window.scrollTo(0, 150);' });
        await new Promise(r => setTimeout(r, 500));
        const res2 = await send('Runtime.evaluate', { expression: 'JSON.stringify({ scrollY: window.scrollY, headerClass: document.querySelector(".site-header").className, top: document.querySelector(".site-header").getBoundingClientRect().top })' });
        console.log('1920 scrolled 150:', res2.result.value);

        ws.close();
        proc.kill();
    } catch(e) {
        console.error(e);
        proc.kill();
    }
}, 2000);
