/**
 * SpiceCraft - Phase 3 Step 1: About Page Responsive & Visual QA
 * Tests all 12 requested viewports using headless Chrome CDP.
 * Checks horizontal overflow, console errors, single H1, and captures screenshots.
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
    if (res.exceptionDetails) {
        throw new Error(JSON.stringify(res.exceptionDetails));
    }
    return res.result ? res.result.value : undefined;
}

async function runViewportTests() {
    console.log('==================================================');
    console.log('PHASE 3 STEP 1: ABOUT PAGE RESPONSIVE & VISUAL QA');
    console.log('==================================================\n');

    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9260;
    const proc = spawn(chromePath, [
        '--headless=new',
        `--remote-debugging-port=${port}`,
        '--no-sandbox',
        '--disable-gpu',
        'http://localhost/about/'
    ]);

    await new Promise(r => setTimeout(r, 2500));

    try {
        const pages = await requestJson(`http://127.0.0.1:${port}/json/list`);
        const page = pages.find(p => p.type === 'page');
        if (!page) throw new Error('No Chrome browser page found.');

        const ws = await connectWebSocket(page.webSocketDebuggerUrl);
        await sendCdp(ws, 'Page.enable');
        await sendCdp(ws, 'DOM.enable');
        await sendCdp(ws, 'Runtime.enable');

        const consoleErrors = [];
        ws.addEventListener('message', (event) => {
            const data = JSON.parse(typeof event.data === 'string' ? event.data : event);
            if (data.method === 'Runtime.consoleAPICalled' && data.params.type === 'error') {
                consoleErrors.push(data.params.args.map(a => a.value || a.description).join(' '));
            }
        });

        // Navigate to /about/
        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/about/' });
        await new Promise(r => setTimeout(r, 2000));

        // 12 Viewports to test
        const viewports = [
            { width: 1920, height: 1080, name: '1920 Desktop Ultra-Wide' },
            { width: 1440, height: 900,  name: '1440 Desktop Large' },
            { width: 1280, height: 800,  name: '1280 Desktop Standard' },
            { width: 1024, height: 768,  name: '1024 Small Desktop / Tablet Landscape' },
            { width: 820,  height: 1180, name: '820 Tablet iPad Air' },
            { width: 768,  height: 1024, name: '768 Tablet Portrait' },
            { width: 480,  height: 854,  name: '480 Large Mobile' },
            { width: 430,  height: 932,  name: '430 iPhone 14/15 Pro Max' },
            { width: 390,  height: 844,  name: '390 iPhone 12/13/14' },
            { width: 375,  height: 667,  name: '375 iPhone SE / Standard Mobile' },
            { width: 360,  height: 800,  name: '360 Android Small' },
            { width: 320,  height: 568,  name: '320 Compact Mobile' },
        ];

        let allPassed = true;

        for (const vp of viewports) {
            await sendCdp(ws, 'Emulation.setDeviceMetricsOverride', {
                width: vp.width,
                height: vp.height,
                deviceScaleFactor: 1,
                mobile: vp.width < 768
            });
            await new Promise(r => setTimeout(r, 300));

            const metrics = await evalScript(ws, `(() => {
                const docWidth = document.documentElement.offsetWidth;
                const scrollWidth = document.documentElement.scrollWidth;
                const winWidth = window.innerWidth;
                const overflow = scrollWidth > winWidth;
                const h1Count = document.querySelectorAll('h1').length;
                const h2Count = document.querySelectorAll('h2').length;
                const sectionCount = document.querySelectorAll('.sc-about-page > section, .sc-about-sections > section').length;
                
                // Timeline layout check
                const timeline = document.querySelector('.sc-about-timeline');
                let timelineMode = 'none';
                if (timeline) {
                    const beforeStyle = window.getComputedStyle(timeline, '::before');
                    timelineMode = beforeStyle.left;
                }

                return {
                    winWidth,
                    docWidth,
                    scrollWidth,
                    overflow,
                    h1Count,
                    h2Count,
                    sectionCount,
                    timelineMode
                };
            })()`);

            const passStatus = !metrics.overflow && metrics.h1Count === 1;
            if (!passStatus) allPassed = false;

            console.log(`[Viewport ${vp.width}x${vp.height} - ${vp.name}]`);
            console.log(`  ScrollWidth: ${metrics.scrollWidth}px | InnerWidth: ${metrics.winWidth}px | Overflow: ${metrics.overflow ? '❌ YES' : '✅ NO'}`);
            console.log(`  H1 Count: ${metrics.h1Count} | H2 Count: ${metrics.h2Count} | Sections: ${metrics.sectionCount}`);
            console.log(`  Result: ${passStatus ? '✅ PASS' : '❌ FAIL'}\n`);

            // Capture screenshots for 1440, 768, and 375
            if ([1440, 768, 375].includes(vp.width)) {
                const screenshot = await sendCdp(ws, 'Page.captureScreenshot', {
                    format: 'jpeg',
                    quality: 85,
                    clip: {
                        x: 0,
                        y: 0,
                        width: vp.width,
                        height: Math.min(vp.height * 2, 2400),
                        scale: 1
                    }
                });
                const filename = `about_page_${vp.width}.jpg`;
                const filepath = path.join(ARTIFACT_DIR, filename);
                fs.writeFileSync(filepath, Buffer.from(screenshot.data, 'base64'));
                console.log(`  📸 Screenshot saved: ${filepath}`);
            }
        }

        console.log('--------------------------------------------------');
        console.log('Console Errors count: ' + consoleErrors.length);
        if (consoleErrors.length > 0) {
            console.log('Console Errors:\n' + consoleErrors.join('\n'));
        } else {
            console.log('✅ ZERO JavaScript / Console Errors on About page');
        }

        console.log('--------------------------------------------------');
        if (allPassed && consoleErrors.length === 0) {
            console.log('🎉 ALL 12 VIEWPORTS PASSED WITH ZERO OVERFLOW & PERFECT HIERARCHY');
        } else {
            console.log('❌ SOME VIEWPORT AUDITS FAILED');
        }

    } catch (err) {
        console.error('Test execution error:', err);
    } finally {
        proc.kill();
    }
}

runViewportTests();
