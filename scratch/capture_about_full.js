/**
 * Capture full-page screenshot of About page
 */
const http = require('http');
const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');

const ARTIFACT_DIR = 'C:\\Users\\kumar\\.gemini\\antigravity-ide\\brain\\cd2cb949-2e08-4939-b99e-abed143333ef';

function requestJson(url) {
    return new Promise((resolve, reject) => {
        http.get(url, (res) => {
            let body = '';
            res.on('data', chunk => body += chunk);
            res.on('end', () => {
                try { resolve(JSON.parse(body)); }
                catch (e) { reject(e); }
            });
        }).on('error', reject);
    });
}

function connectWebSocket(url) {
    return new Promise((resolve, reject) => {
        const ws = new WebSocket(url);
        ws.onopen = () => resolve(ws);
        ws.onerror = reject;
    });
}

let idCounter = 1;
function sendCdp(ws, method, params = {}) {
    return new Promise((resolve, reject) => {
        const id = idCounter++;
        const handler = (event) => {
            const raw = typeof event.data === 'string' ? event.data : event;
            const data = JSON.parse(raw);
            if (data.id === id) {
                ws.removeEventListener('message', handler);
                if (data.error) reject(data.error);
                else resolve(data.result);
            }
        };
        ws.addEventListener('message', handler);
        ws.send(JSON.stringify({ id, method, params }));
    });
}

async function evalScript(ws, expression) {
    const res = await sendCdp(ws, 'Runtime.evaluate', {
        expression,
        returnByValue: true,
        awaitPromise: true
    });
    return res.result ? res.result.value : undefined;
}

async function captureFullPage() {
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9265;
    const proc = spawn(chromePath, [
        '--headless=new',
        `--remote-debugging-port=${port}`,
        '--no-sandbox',
        '--disable-gpu',
        'http://localhost/about/'
    ]);

    await new Promise(r => setTimeout(r, 2200));

    try {
        const pages = await requestJson(`http://127.0.0.1:${port}/json/list`);
        const page = pages.find(p => p.type === 'page');
        const ws = await connectWebSocket(page.webSocketDebuggerUrl);
        await sendCdp(ws, 'Page.enable');
        await sendCdp(ws, 'DOM.enable');

        await sendCdp(ws, 'Emulation.setDeviceMetricsOverride', {
            width: 1440,
            height: 900,
            deviceScaleFactor: 1,
            mobile: false
        });

        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/about/' });
        await new Promise(r => setTimeout(r, 2000));

        // Scroll down page to trigger lazy loading on all images
        await evalScript(ws, `(async () => {
            await new Promise(resolve => {
                let totalHeight = 0;
                const distance = 800;
                const timer = setInterval(() => {
                    const scrollHeight = document.body.scrollHeight;
                    window.scrollBy(0, distance);
                    totalHeight += distance;

                    if (totalHeight >= scrollHeight) {
                        clearInterval(timer);
                        window.scrollTo(0, 0);
                        resolve();
                    }
                }, 100);
            });
        })()`);

        await new Promise(r => setTimeout(r, 1500));

        // Get full scroll height
        const height = await evalScript(ws, `Math.max(document.body.scrollHeight, document.documentElement.scrollHeight)`);

        console.log('Document total height:', height);

        // Capture full page screenshot
        const screenshot = await sendCdp(ws, 'Page.captureScreenshot', {
            format: 'jpeg',
            quality: 80,
            captureBeyondViewport: true,
            fromSurface: true
        });

        const filepath = path.join(ARTIFACT_DIR, 'about_page_full.jpg');
        fs.writeFileSync(filepath, Buffer.from(screenshot.data, 'base64'));
        console.log('Full page screenshot saved to:', filepath);

        // Also capture specific section clips
        // Timeline & Leadership
        const timelineBox = await evalScript(ws, `(() => {
            const el = document.getElementById('journey-milestones');
            if (!el) return null;
            const r = el.getBoundingClientRect();
            return { x: 0, y: r.top + window.scrollY, width: 1440, height: r.height };
        })()`);

        if (timelineBox) {
            const tlShot = await sendCdp(ws, 'Page.captureScreenshot', {
                format: 'jpeg',
                quality: 85,
                clip: { ...timelineBox, scale: 1 }
            });
            fs.writeFileSync(path.join(ARTIFACT_DIR, 'about_timeline_section.jpg'), Buffer.from(tlShot.data, 'base64'));
            console.log('Timeline screenshot saved.');
        }

        const teamBox = await evalScript(ws, `(() => {
            const el = document.getElementById('leadership-team');
            if (!el) return null;
            const r = el.getBoundingClientRect();
            return { x: 0, y: r.top + window.scrollY, width: 1440, height: r.height };
        })()`);

        if (teamBox) {
            const teamShot = await sendCdp(ws, 'Page.captureScreenshot', {
                format: 'jpeg',
                quality: 85,
                clip: { ...teamBox, scale: 1 }
            });
            fs.writeFileSync(path.join(ARTIFACT_DIR, 'about_leadership_section.jpg'), Buffer.from(teamShot.data, 'base64'));
            console.log('Leadership screenshot saved.');
        }

    } catch (e) {
        console.error(e);
    } finally {
        proc.kill();
    }
}

captureFullPage();
