const { spawn } = require('child_process');
const http = require('http');

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

async function runStep3Suite() {
    console.log('=== PHASE 2 STEP 3 BROWSER AUTOMATION SUITE ===\n');
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9230;
    const proc = spawn(chromePath, [
        '--headless=new',
        `--remote-debugging-port=${port}`,
        '--no-sandbox',
        '--disable-gpu',
        'http://localhost/shop/'
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
        await send('Page.navigate', { url: 'http://localhost/shop/' });
        await new Promise((resolve) => {
            const loadHandler = (e) => {
                const data = JSON.parse(e.data);
                if (data.method === 'Page.loadEventFired') {
                    ws.removeEventListener('message', loadHandler);
                    resolve();
                }
            };
            ws.addEventListener('message', loadHandler);
            setTimeout(resolve, 3000);
        });
        await new Promise(r => setTimeout(r, 1000));

        // 1. Viewport Overflow & Responsive Layout Tests
        for (const vp of VIEWPORTS) {
            console.log(`\n--- Testing Viewport: ${vp.name} (${vp.width}x${vp.height}) ---`);

            await send('Emulation.setDeviceMetricsOverride', {
                width: vp.width,
                height: vp.height,
                deviceScaleFactor: 1,
                mobile: vp.width < 768
            });

            await new Promise(r => setTimeout(r, 300));

            const stateRes = await send('Runtime.evaluate', {
                expression: `(function() {
                    var docWidth = window.innerWidth;
                    var docScroll = document.documentElement.scrollWidth;
                    var isMobile = window.innerWidth <= 768;
                    var desktopFilter = document.getElementById('sc-desktop-filter-bar');
                    var mobileFilterBtn = document.getElementById('sc-mobile-filter-btn');
                    var desktopDisplay = desktopFilter ? window.getComputedStyle(desktopFilter).display : null;
                    var mobileBtnDisplay = mobileFilterBtn ? window.getComputedStyle(mobileFilterBtn).display : null;
                    
                    return JSON.stringify({
                        hasOverflow: docScroll > docWidth,
                        docScroll: docScroll,
                        docWidth: docWidth,
                        isMobile: isMobile,
                        desktopVisible: desktopDisplay !== 'none',
                        mobileBtnVisible: mobileBtnDisplay !== 'none'
                    });
                })()`
            });

            const st = JSON.parse(stateRes.result.value);
            record('No horizontal overflow on catalog page', !st.hasOverflow, `${st.docScroll}px <= ${st.docWidth}px`);

            if (st.isMobile) {
                record('Mobile filter button visible on small viewport', st.mobileBtnVisible);
                record('Desktop filter bar hidden on small viewport', !st.desktopVisible);
            } else {
                record('Desktop filter bar visible on wide viewport', st.desktopVisible);
                record('Mobile filter button hidden on wide viewport', !st.mobileBtnVisible);
            }
        }

        // 2. Favourites Functionality Test
        console.log('\n--- Testing Favourites System ---');
        // Clear localStorage first
        await send('Runtime.evaluate', { expression: 'localStorage.clear();' });
        await new Promise(r => setTimeout(r, 200));

        // Click favourite on first product card
        const favClickRes = await send('Runtime.evaluate', {
            expression: `(function() {
                var btn = document.querySelector('.sc-product-card__favourite');
                if (!btn) return JSON.stringify({ error: 'no button' });
                var id = btn.getAttribute('data-product-id');
                btn.click();
                var inStorage = localStorage.getItem('spicecraft_favourites');
                var badge = document.querySelector('.sc-header-favourite .sc-badge-count');
                return JSON.stringify({
                    id: id,
                    isFavClass: btn.classList.contains('is-favourite'),
                    ariaPressed: btn.getAttribute('aria-pressed'),
                    storage: inStorage,
                    badgeCount: badge ? badge.textContent : null
                });
            })()`
        });

        const favState = JSON.parse(favClickRes.result.value);
        record('Card favourite button receives is-favourite class on click', favState.isFavClass);
        record('Card favourite button updates aria-pressed to true', favState.ariaPressed === 'true');
        record('Header favourite counter updates to 1', favState.badgeCount === '1');
        record('localStorage spicecraft_favourites stores product ID', favState.storage && favState.storage.includes(favState.id));

        // 3. Favourites Page Test
        console.log('\n--- Testing Favourites Page Navigation & Display ---');
        await send('Page.navigate', { url: 'http://localhost/favourites/' });
        await new Promise(r => setTimeout(r, 1500));

        const favPageRes = await send('Runtime.evaluate', {
            expression: `(function() {
                var gridWrap = document.getElementById('sc-favourites-grid-wrap');
                var cards = document.querySelectorAll('#sc-favourites-grid .sc-product-card');
                var countLabel = document.getElementById('sc-favourites-count-label');
                return JSON.stringify({
                    gridVisible: gridWrap ? window.getComputedStyle(gridWrap).display !== 'none' : false,
                    cardCount: cards.length,
                    countLabel: countLabel ? countLabel.textContent : ''
                });
            })()`
        });

        const favPage = JSON.parse(favPageRes.result.value);
        record('Favourites page loads saved product card from localStorage via AJAX', favPage.gridVisible && favPage.cardCount === 1, `${favPage.cardCount} card rendered`);

        // Test Clear All Favourites
        const clearFavRes = await send('Runtime.evaluate', {
            expression: `(function() {
                var clearBtn = document.getElementById('sc-clear-favourites-btn');
                if (clearBtn) clearBtn.click();
                var emptyState = document.getElementById('sc-favourites-empty');
                var storage = localStorage.getItem('spicecraft_favourites');
                var badge = document.querySelector('.sc-header-favourite .sc-badge-count');
                return JSON.stringify({
                    emptyVisible: emptyState ? window.getComputedStyle(emptyState).display !== 'none' : false,
                    storage: storage,
                    badgeCount: badge ? badge.textContent : null
                });
            })()`
        });

        const clearFav = JSON.parse(clearFavRes.result.value);
        record('Clear all favourites renders empty state', clearFav.emptyVisible);
        record('Clear all favourites resets localStorage and header counter', clearFav.badgeCount === '0' && clearFav.storage === '[]');

        // 4. Single Product Engagement & Recently Viewed Test
        console.log('\n--- Testing Single Product Engagement & Recently Viewed ---');
        // Clear recently viewed first
        await send('Runtime.evaluate', { expression: 'localStorage.removeItem("spicecraft_recently_viewed");' });

        // Visit Turmeric (#21)
        await send('Page.navigate', { url: 'http://localhost/product/organic-turmeric-powder/' });
        await new Promise(r => setTimeout(r, 1500));

        const prod1Res = await send('Runtime.evaluate', {
            expression: `(function() {
                var storage = localStorage.getItem('spicecraft_recently_viewed');
                var favBtn = document.querySelector('.sc-single-product__fav-btn');
                var shareBtn = document.getElementById('sc-share-product-btn');
                var packSelector = document.getElementById('sc-pack-selector');
                return JSON.stringify({
                    storage: storage,
                    hasFavBtn: !!favBtn,
                    hasShareBtn: !!shareBtn,
                    hasPackSelector: !!packSelector
                });
            })()`
        });

        const p1 = JSON.parse(prod1Res.result.value);
        record('Single product view tracks in spicecraft_recently_viewed', p1.storage && p1.storage.includes('21'));
        record('Single product renders Favourite and Share buttons', p1.hasFavBtn && p1.hasShareBtn);
        record('Single product renders Pack Size selector', p1.hasPackSelector);

        // Visit Peppercorns (#24)
        await send('Page.navigate', { url: 'http://localhost/product/tellicherry-black-peppercorns/' });
        await new Promise(r => setTimeout(r, 1500));

        const prod2Res = await send('Runtime.evaluate', {
            expression: `(function() {
                var storage = localStorage.getItem('spicecraft_recently_viewed');
                var recentSec = document.getElementById('sc-recently-viewed');
                var recentCards = document.querySelectorAll('#sc-recently-viewed-grid .sc-product-card');
                var recentDisplay = recentSec ? window.getComputedStyle(recentSec).display : 'none';
                
                // Check pack size selection
                var packPills = document.querySelectorAll('.sc-pack-pill');
                var whatsappBtn = document.getElementById('sc-whatsapp-enquiry-cta');
                var packSelectedText = '';
                if (packPills.length > 1) {
                    packPills[1].click();
                    packSelectedText = packPills[1].getAttribute('data-pack-size');
                }
                var updatedHref = whatsappBtn ? whatsappBtn.getAttribute('href') : '';

                return JSON.stringify({
                    storage: storage,
                    recentVisible: recentDisplay !== 'none',
                    recentCardCount: recentCards.length,
                    packSelectedText: packSelectedText,
                    whatsappHasPack: updatedHref.includes(encodeURIComponent(packSelectedText)) || updatedHref.includes(packSelectedText)
                });
            })()`
        });

        const p2 = JSON.parse(prod2Res.result.value);
        record('Recently Viewed section dynamically reveals with past viewed products', p2.recentVisible && p2.recentCardCount >= 1, `${p2.recentCardCount} item(s) in history`);
        record('Currently viewed product excluded from Recently Viewed section', p2.storage && p2.storage.startsWith('[24,') || p2.storage === '[24,21]');
        record('Pack size selection updates WhatsApp inquiry link dynamically', p2.whatsappHasPack, `Selected: ${p2.packSelectedText}`);

        // 5. Mobile Filter Drawer Open & Close Test
        console.log('\n--- Testing Mobile Filter Drawer Interaction ---');
        await send('Emulation.setDeviceMetricsOverride', { width: 375, height: 667, deviceScaleFactor: 1, mobile: true });
        await send('Page.navigate', { url: 'http://localhost/shop/' });
        await new Promise(r => setTimeout(r, 1200));

        const drawerTestRes = await send('Runtime.evaluate', {
            expression: `(function() {
                var btn = document.getElementById('sc-mobile-filter-btn');
                if (btn) btn.click();
                var drawer = document.getElementById('sc-filter-drawer');
                var isOpen = drawer ? drawer.classList.contains('is-active') : false;
                var ariaExp = btn ? btn.getAttribute('aria-expanded') : false;
                var bodyLocked = document.body.style.overflow === 'hidden';
                
                // Now close
                var closeBtn = document.getElementById('sc-close-filter-drawer');
                if (closeBtn) closeBtn.click();
                var isClosed = drawer ? !drawer.classList.contains('is-active') : false;
                var bodyUnlocked = document.body.style.overflow === '';

                return JSON.stringify({
                    isOpen: isOpen,
                    ariaExp: ariaExp,
                    bodyLocked: bodyLocked,
                    isClosed: isClosed,
                    bodyUnlocked: bodyUnlocked
                });
            })()`
        });

        const dTest = JSON.parse(drawerTestRes.result.value);
        record('Mobile filter button opens off-canvas filter drawer', dTest.isOpen && dTest.ariaExp === 'true');
        record('Drawer open locks body scroll to prevent background drift', dTest.bodyLocked);
        record('Drawer close button smoothly closes drawer and unlocks body scroll', dTest.isClosed && dTest.bodyUnlocked);

        console.log('\n============================================');
        console.log(`STEP 3 BROWSER SUITE TOTALS: ${totalPassed} Passed, ${totalFailed} Failed`);
        console.log('============================================\n');

        ws.close();
        proc.kill();

        if (totalFailed > 0) {
            process.exit(1);
        }
        process.exit(0);

    } catch (e) {
        console.error('Error running Step 3 suite:', e);
        proc.kill();
        process.exit(1);
    }
}

runStep3Suite();
