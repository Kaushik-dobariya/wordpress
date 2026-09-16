const { execSync } = require('child_process');
const http = require('http');

// Use Chrome with remote debugging
const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const cp = require('child_process').spawn(chromePath, [
    '--headless=new',
    '--remote-debugging-port=9223',
    '--no-sandbox',
    '--disable-gpu',
    '--window-size=375,812',
    'http://localhost/'
]);

setTimeout(async () => {
    try {
        const fetch = (url) => new Promise((resolve, reject) => {
            http.get(url, (res) => {
                let data = '';
                res.on('data', chunk => data += chunk);
                res.on('end', () => resolve(JSON.parse(data)));
            }).on('error', reject);
        });

        const targets = await fetch('http://localhost:9223/json');
        const pageTarget = targets.find(t => t.type === 'page');
        if (!pageTarget || !pageTarget.webSocketDebuggerUrl) {
            console.log('No page target found');
            cp.kill();
            return;
        }

        const WebSocket = require('stream'); // fallback
        console.log('Target found:', pageTarget.webSocketDebuggerUrl);
    } catch (e) {
        console.error(e);
    }
    cp.kill();
}, 2000);
