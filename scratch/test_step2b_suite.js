const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const path = require('path');

const VIEWPORTS = [
    { width: 1920, height: 1080, name: 'Desktop_1920' },
    { width: 1440, height: 900,  name: 'Desktop_1440' },
    { width: 1280, height: 800,  name: 'Laptop_1280' },
    { width: 1024, height: 768,  name: 'Tablet_1024' },
    { width: 768,  height: 1024, name: 'Tablet_768' },
    { width: 430,  height: 932,  name: 'Mobile_430' },
    { width: 375,  height: 667,  name: 'Mobile_375' },
    { width: 320,  height: 568,  name: 'Mobile_320' },
];

async function runSuite() {
    console.log('=== PHASE 2 STEP 2B BROWSER AUTOMATION SUITE ===\n');
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

    let totalPassed = 0;
    let totalFailed = 0;

    function record(testName, passed, details = '') {
        if (passed) {
            totalPassed++;
            console.log(`  [+] PASS: ${testName} ${details ? '(' + details + ')' : ''}`);
        } else {
            totalFailed++;
            console.log(`  [-] FAIL: ${testName} ${details ? '(' + details + ')' : ''}`);
        }
    }

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
        // Wait for page to fully load
        await send('Page.navigate', { url: 'http://localhost/' });
        await new Promise((resolve) => {
            const loadHandler = (e) => {
                const data = JSON.parse(e.data);
                if (data.method === 'Page.loadEventFired') {
                    ws.removeEventListener('message', loadHandler);
                    resolve();
                }
            };
            ws.addEventListener('message', loadHandler);
            setTimeout(resolve, 3000); // Fallback timeout
        });
        await new Promise(r => setTimeout(r, 1000));

        for (const vp of VIEWPORTS) {
            console.log(`\n--- Testing Viewport: ${vp.name} (${vp.width}x${vp.height}) ---`);

            await send('Emulation.setDeviceMetricsOverride', {
                width: vp.width,
                height: vp.height,
                deviceScaleFactor: 1,
                mobile: vp.width < 768
            });

            // Ensure scrolled to top initially
            await send('Runtime.evaluate', { expression: 'window.scrollTo(0, 0);' });
            await new Promise(r => setTimeout(r, 300));

            // Test 1: Initial state (at page top)
            const initialRes = await send('Runtime.evaluate', {
                expression: `(function() {
                    var header = document.querySelector('.site-header');
                    var scrollTopBtn = document.getElementById('sc-scroll-top');
                    var docWidth = window.innerWidth;
                    var docScroll = document.documentElement.scrollWidth;
                    return JSON.stringify({
                        headerScrolled: header ? header.classList.contains('is-scrolled') : null,
                        scrollTopVisible: scrollTopBtn ? scrollTopBtn.classList.contains('is-visible') : null,
                        hasOverflow: docScroll > docWidth,
                        docScrollWidth: docScroll,
                        windowInnerWidth: docWidth
                    });
                })()`
            });

            const initial = JSON.parse(initialRes.result.value);
            record('Header normal at page top (not is-scrolled)', initial.headerScrolled === false);
            record('Scroll-to-top hidden at page top (not is-visible)', initial.scrollTopVisible === false);
            record('No horizontal overflow at initial load', !initial.hasOverflow, `${initial.docScrollWidth}px <= ${initial.windowInnerWidth}px`);

            // Test 2: Scroll to 100px (header sticky and is-scrolled activated)
            await send('Runtime.evaluate', { expression: 'window.scrollTo(0, 100);' });
            await new Promise(r => setTimeout(r, 400));

            const scroll100Res = await send('Runtime.evaluate', {
                expression: `(function() {
                    var header = document.querySelector('.site-header');
                    var rect = header ? header.getBoundingClientRect() : null;
                    return JSON.stringify({
                        headerScrolled: header ? header.classList.contains('is-scrolled') : false,
                        headerTop: rect ? rect.top : null,
                        headerBottom: rect ? rect.bottom : null,
                        isSticky: rect ? Math.abs(rect.top) <= 2 : false
                    });
                })()`
            });

            const s100 = JSON.parse(scroll100Res.result.value);
            record('Header sticks to top on scroll', s100.isSticky, `top: ${s100.headerTop}px`);
            record('Header receives is-scrolled class after 50px threshold', s100.headerScrolled === true);

            // Test 3: Scroll to 600px (scroll-to-top button visible)
            await send('Runtime.evaluate', { expression: 'window.scrollTo(0, 600);' });
            await new Promise(r => setTimeout(r, 400));

            const scroll600Res = await send('Runtime.evaluate', {
                expression: `(function() {
                    var btn = document.getElementById('sc-scroll-top');
                    var rect = btn ? btn.getBoundingClientRect() : null;
                    var winW = window.innerWidth;
                    var winH = window.innerHeight;
                    var isVisible = btn ? btn.classList.contains('is-visible') : false;
                    var inBottomRight = rect ? (rect.right <= winW && rect.bottom <= winH && rect.top > 0 && rect.left > 0) : false;
                    return JSON.stringify({
                        isVisible: isVisible,
                        inBottomRight: inBottomRight,
                        rect: rect,
                        winW: winW,
                        winH: winH
                    });
                })()`
            });

            const s600 = JSON.parse(scroll600Res.result.value);
            record('Scroll-to-top button becomes is-visible after threshold', s600.isVisible === true);
            record('Scroll-to-top button positioned bottom-right within viewport', s600.inBottomRight === true, `right: ${s600.rect ? Math.round(s600.rect.right) : 0}/${s600.winW}px, bottom: ${s600.rect ? Math.round(s600.rect.bottom) : 0}/${s600.winH}px`);

            // Test 4: Click Scroll-to-Top Button
            const clickRes = await send('Runtime.evaluate', {
                expression: `(function() {
                    var btn = document.getElementById('sc-scroll-top');
                    if (btn) btn.click();
                    return true;
                })()`
            });

            await new Promise(r => setTimeout(r, 600));

            const afterClickRes = await send('Runtime.evaluate', {
                expression: `(function() {
                    var scrollY = window.pageYOffset || document.documentElement.scrollTop;
                    var header = document.querySelector('.site-header');
                    var btn = document.getElementById('sc-scroll-top');
                    return JSON.stringify({
                        scrollY: scrollY,
                        returnedToTop: scrollY <= 5,
                        headerScrolled: header ? header.classList.contains('is-scrolled') : null,
                        btnVisible: btn ? btn.classList.contains('is-visible') : null
                    });
                })()`
            });

            const afterClick = JSON.parse(afterClickRes.result.value);
            record('Clicking scroll-to-top scrolls user smoothly to top (scrollY=0)', afterClick.returnedToTop === true, `scrollY: ${afterClick.scrollY}`);
            record('Scroll-to-top button hides when returned to top', afterClick.btnVisible === false);
            record('Header returns to normal un-scrolled state at top', afterClick.headerScrolled === false);

            // Test 5: Mobile Drawer toggle while sticky (for viewports < 992px)
            if (vp.width < 992) {
                // Scroll to 200px first to simulate interacting while sticky
                await send('Runtime.evaluate', { expression: 'window.scrollTo(0, 200);' });
                await new Promise(r => setTimeout(r, 300));

                const drawerRes = await send('Runtime.evaluate', {
                    expression: `(function() {
                        var toggle = document.querySelector('.sc-menu-toggle');
                        var drawer = document.getElementById('mobile-navigation');
                        if (!toggle || !drawer) return JSON.stringify({ error: 'Elements missing' });

                        // Click to open
                        toggle.click();
                        var isOpen = drawer.classList.contains('is-active');
                        var drawerRect = drawer.getBoundingClientRect();
                        var headerMain = document.querySelector('.sc-header-main');
                        var headerMainRect = headerMain ? headerMain.getBoundingClientRect() : null;

                        // Click to close
                        toggle.click();
                        var isClosed = !drawer.classList.contains('is-active');

                        return JSON.stringify({
                            isOpen: isOpen,
                            isClosed: isClosed,
                            drawerAttachedUnderHeader: headerMainRect ? Math.abs(drawerRect.top - headerMainRect.bottom) <= 5 : false,
                            drawerTop: drawerRect.top,
                            headerMainBottom: headerMainRect ? headerMainRect.bottom : null
                        });
                    })()`
                });

                const drawerData = JSON.parse(drawerRes.result.value);
                record('Mobile menu toggle opens mobile drawer when sticky', drawerData.isOpen === true);
                record('Mobile drawer stays attached under sticky header', drawerData.drawerAttachedUnderHeader === true);
                record('Mobile menu toggle closes mobile drawer', drawerData.isClosed === true);
            }

            // Test 6: Check for any horizontal overflow across the page
            const overflowRes = await send('Runtime.evaluate', {
                expression: `(function() {
                    return document.documentElement.scrollWidth <= window.innerWidth;
                })()`
            });
            record('Zero horizontal overflow across all sections', overflowRes.result.value === true);
        }

        // Test 7: WordPress Admin Bar compensation verification
        console.log('\n--- Testing WordPress Admin Bar Compensation ---');
        const adminBarTest = await send('Runtime.evaluate', {
            expression: `(function() {
                // Simulate logged-in admin bar
                document.body.classList.add('admin-bar');
                var header = document.querySelector('.site-header');
                var compStyle = window.getComputedStyle(header);
                var topVal = compStyle.top;
                document.body.classList.remove('admin-bar');
                return JSON.stringify({ top: topVal });
            })()`
        });
        const abData = JSON.parse(adminBarTest.result.value);
        console.log('Admin Bar simulated computed top offset:', abData.top);
        record('body.admin-bar applies top offset to site-header', abData.top !== '0px', `top: ${abData.top}`);

        console.log('\n============================================');
        console.log(`BROWSER SUITE TOTALS: ${totalPassed} Passed, ${totalFailed} Failed`);
        console.log('============================================\n');

        ws.close();
        proc.kill();

        if (totalFailed > 0) {
            process.exit(1);
        } else {
            process.exit(0);
        }
    } catch (err) {
        console.error('Test error:', err);
        proc.kill();
        process.exit(1);
    }
}

runSuite();
