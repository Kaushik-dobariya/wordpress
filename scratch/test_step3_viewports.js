/**
 * SpiceCraft - Phase 3 Step 3: Certifications Archive & Detail Viewports & Visual QA
 * Tests all 14 requested viewports across BOTH pages using headless Chrome CDP.
 * Viewports: 1920, 1600, 1440, 1366, 1280, 1024, 820, 768, 480, 430, 390, 375, 360, 320.
 */

const http = require('http');
const fs = require('fs');
const path = require('path');
const { spawn, execSync } = require('child_process');

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

const VIEWPORTS = [
    { width: 1920, height: 1080, name: '1920 Ultra-Wide' },
    { width: 1600, height: 900,  name: '1600 Desktop Large' },
    { width: 1440, height: 900,  name: '1440 Desktop Standard' },
    { width: 1366, height: 768,  name: '1366 Desktop HD' },
    { width: 1280, height: 800,  name: '1280 Desktop Compact' },
    { width: 1024, height: 768,  name: '1024 Tablet Landscape' },
    { width: 820,  height: 1180, name: '820 iPad Air' },
    { width: 768,  height: 1024, name: '768 Tablet Portrait' },
    { width: 480,  height: 854,  name: '480 Large Mobile' },
    { width: 430,  height: 932,  name: '430 iPhone 15 Pro Max' },
    { width: 390,  height: 844,  name: '390 iPhone 14' },
    { width: 375,  height: 667,  name: '375 iPhone SE' },
    { width: 360,  height: 800,  name: '360 Android Small' },
    { width: 320,  height: 568,  name: '320 Compact Mobile' },
];

async function testPage(ws, pageUrl, pageName) {
    console.log(`\n==================================================`);
    console.log(`TESTING ${pageName.toUpperCase()}: ${pageUrl}`);
    console.log(`==================================================\n`);

    await sendCdp(ws, 'Page.navigate', { url: pageUrl });
    await new Promise(r => setTimeout(r, 2000));

    let pagePassed = true;

    for (const vp of VIEWPORTS) {
        await sendCdp(ws, 'Emulation.setDeviceMetricsOverride', {
            width: vp.width,
            height: vp.height,
            deviceScaleFactor: 1,
            mobile: vp.width < 768
        });
        await new Promise(r => setTimeout(r, 350));

        const metrics = await evalScript(ws, `(() => {
            const scrollWidth = document.documentElement.scrollWidth;
            const winWidth = window.innerWidth;
            const overflow = scrollWidth > winWidth;
            const h1Count = document.querySelectorAll('h1').length;
            
            // Check button touch targets
            const buttons = Array.from(document.querySelectorAll('.sc-btn, button, .sc-header-action, .sc-cert-download-btn'));
            const smallTargets = buttons.filter(b => {
                const rect = b.getBoundingClientRect();
                return (rect.width > 0 && rect.height > 0) && (rect.width < 36 || rect.height < 36);
            }).length;

            return {
                scrollWidth,
                winWidth,
                overflow,
                h1Count,
                smallTargets
            };
        })()`);

        const status = !metrics.overflow && metrics.h1Count === 1 ? 'PASS' : 'FAIL';
        if (status === 'FAIL') pagePassed = false;

        console.log(` [${status}] ${vp.width}x${vp.height} (${vp.name}): scrollWidth=${metrics.scrollWidth}px, winWidth=${metrics.winWidth}px, H1s=${metrics.h1Count}, overflow=${metrics.overflow ? 'YES' : 'NO'}`);

        // Capture screenshot at 1440, 768, and 375
        if ([1440, 768, 375].includes(vp.width)) {
            const ss = await sendCdp(ws, 'Page.captureScreenshot', { format: 'jpeg', quality: 80 });
            const filename = `${pageName}_${vp.width}.jpg`;
            fs.writeFileSync(path.join(ARTIFACT_DIR, filename), Buffer.from(ss.data, 'base64'));
            console.log(`   -> Captured artifact screenshot: ${filename}`);
        }
    }

    // Capture full-page screenshot at 1440
    await sendCdp(ws, 'Emulation.setDeviceMetricsOverride', {
        width: 1440,
        height: 900,
        deviceScaleFactor: 1,
        mobile: false
    });
    await new Promise(r => setTimeout(r, 500));
    const fullSS = await sendCdp(ws, 'Page.captureScreenshot', { format: 'jpeg', quality: 80, captureBeyondViewport: true });
    const fullFilename = `${pageName}_full.jpg`;
    fs.writeFileSync(path.join(ARTIFACT_DIR, fullFilename), Buffer.from(fullSS.data, 'base64'));
    console.log(`   -> Captured full-page artifact screenshot: ${fullFilename}`);

    return pagePassed;
}

async function runAll() {
    console.log('Setting up fixture...');
    const fixtureOutput = execSync('php scratch/setup_viewport_fixtures.php').toString();
    console.log(fixtureOutput);

    const detailUrlMatch = fixtureOutput.match(/FIXTURE_READY:\s*(.*)/);
    const detailUrl = detailUrlMatch ? detailUrlMatch[1].trim() : 'http://localhost/certification/iso-22000-food-safety/';

    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9266;
    const proc = spawn(chromePath, [
        '--headless=new',
        `--remote-debugging-port=${port}`,
        '--no-sandbox',
        '--disable-gpu',
        'http://localhost/certifications/'
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

        const archivePass = await testPage(ws, 'http://localhost/certifications/', 'certifications_archive');
        const detailPass  = await testPage(ws, detailUrl, 'certification_detail');

        console.log(`\n==================================================`);
        console.log(`RESPONSIVE TEST SUMMARY:`);
        console.log(`Certifications Archive (14 viewports): ${archivePass ? 'ALL PASSED' : 'SOME FAILED'}`);
        console.log(`Certification Detail (14 viewports): ${detailPass ? 'ALL PASSED' : 'SOME FAILED'}`);
        console.log(`==================================================\n`);

        ws.close();
        proc.kill();

        console.log('Cleaning up test fixture...');
        const cleanOutput = execSync('php scratch/cleanup_viewport_fixtures.php').toString();
        console.log(cleanOutput);

        process.exit(archivePass && detailPass ? 0 : 1);
    } catch (err) {
        console.error('Error in viewport tests:', err);
        proc.kill();
        execSync('php scratch/cleanup_viewport_fixtures.php');
        process.exit(1);
    }
}

runAll();
