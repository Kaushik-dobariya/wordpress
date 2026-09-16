/**
 * SpiceCraft - Phase 2 Step 4A Hotfix Verification Script
 * Spawns headless Chrome with CDP to test all 7 listing contexts & 8 breakpoints.
 */

const http = require('http');
const { spawn } = require('child_process');

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

async function runTests() {
    console.log('=== PHASE 2 STEP 4A: HOTFIX VERIFICATION SUITE ===\n');

    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9250;
    const proc = spawn(chromePath, [
        '--headless=new',
        `--remote-debugging-port=${port}`,
        '--no-sandbox',
        '--disable-gpu',
        'http://localhost/shop/'
    ]);

    await new Promise(r => setTimeout(r, 2200));

    try {
        const pages = await requestJson(`http://127.0.0.1:${port}/json/list`);
        const page = pages.find(p => p.type === 'page');
        if (!page) {
            throw new Error('No Chrome browser page found.');
        }

        const ws = await connectWebSocket(page.webSocketDebuggerUrl);
        await sendCdp(ws, 'Page.enable');
        await sendCdp(ws, 'DOM.enable');
        await sendCdp(ws, 'CSS.enable');

        // Set default desktop viewport: 1440x900
        await sendCdp(ws, 'Emulation.setDeviceMetricsOverride', {
            width: 1440,
            height: 900,
            deviceScaleFactor: 1,
            mobile: false
        });

        const results = [];

        async function navigateAndAudit(url, contextName, selector, setupFn = null) {
            console.log(`\n--- Testing Context: ${contextName} (${url}) ---`);
            
            // If setupFn needs to run on domain first (e.g. for localStorage)
            if (setupFn) {
                await evalScript(ws, setupFn);
                await new Promise(r => setTimeout(r, 200));
            }

            await sendCdp(ws, 'Page.navigate', { url });
            
            // Poll until container and items are loaded
            let audit = null;
            for (let attempt = 0; attempt < 25; attempt++) {
                await new Promise(r => setTimeout(r, 300));
                audit = await evalScript(ws, `
                    (function() {
                        const grid = document.querySelector('${selector}');
                        if (!grid) return { found: false, reason: 'Selector not found' };

                        const products = Array.from(grid.querySelectorAll('li.product, .sc-product-card'));
                        if (products.length === 0) {
                            return { found: true, itemCount: 0, reason: 'No items yet' };
                        }

                        const beforeStyle = window.getComputedStyle(grid, '::before');
                        const afterStyle = window.getComputedStyle(grid, '::after');
                        const gridStyle = window.getComputedStyle(grid);
                        const gridRect = grid.getBoundingClientRect();

                        const productPositions = products.slice(0, 8).map((p, idx) => {
                            const r = p.getBoundingClientRect();
                            return {
                                index: idx,
                                id: p.id || p.className.substring(0, 30),
                                x: Math.round(r.x),
                                y: Math.round(r.y),
                                width: Math.round(r.width),
                                height: Math.round(r.height)
                            };
                        });

                        const firstProduct = productPositions[0];
                        const containerPaddingLeft = parseFloat(gridStyle.paddingLeft) || 0;
                        const isFirstInCol1 = firstProduct ? Math.abs(firstProduct.x - (gridRect.x + containerPaddingLeft)) < 8 : false;

                        const row1Y = firstProduct ? firstProduct.y : null;
                        const row1Items = productPositions.filter(p => Math.abs(p.y - row1Y) < 5);

                        return {
                            found: true,
                            itemCount: products.length,
                            display: gridStyle.display,
                            gridTemplateColumns: gridStyle.gridTemplateColumns,
                            beforeContent: beforeStyle.content,
                            beforeDisplay: beforeStyle.display,
                            afterContent: afterStyle.content,
                            afterDisplay: afterStyle.display,
                            firstProductX: firstProduct ? firstProduct.x : null,
                            gridX: Math.round(gridRect.x),
                            isFirstInCol1,
                            row1Count: row1Items.length,
                            positions: productPositions
                        };
                    })()
                `);

                if (audit && audit.found && audit.itemCount > 0) {
                    break;
                }
            }

            if (!audit || !audit.found) {
                console.error(`❌ FAILED: Grid container '${selector}' not found!`);
                results.push({ context: contextName, pass: false, reason: 'Container not found' });
                return;
            }

            if (audit.itemCount === 0) {
                console.error(`❌ FAILED: Grid container '${selector}' found but contains 0 items!`);
                results.push({ context: contextName, pass: false, reason: '0 items found' });
                return;
            }

            const isBeforeSuppressed = (audit.beforeContent === 'none' || audit.beforeDisplay === 'none' || audit.beforeContent === 'normal');
            const isCol1Valid = audit.isFirstInCol1;

            console.log(`Container: display=${audit.display}, items=${audit.itemCount}, cols=${audit.gridTemplateColumns}`);
            console.log(`::before pseudo: content="${audit.beforeContent}", display="${audit.beforeDisplay}" -> Suppressed: ${isBeforeSuppressed ? '✅ YES' : '❌ NO'}`);
            console.log(`First product starts at X: ${audit.firstProductX} (Grid X: ${audit.gridX}) -> Column 1: ${isCol1Valid ? '✅ YES' : '❌ NO'}`);
            console.log(`Row 1 product count: ${audit.row1Count} items`);

            if (audit.positions.length > 0) {
                console.log('Product Positions (first 4):');
                audit.positions.slice(0, 4).forEach(p => {
                    console.log(`  [P${p.index + 1}] x:${p.x}, y:${p.y}, w:${p.width}, h:${p.height}`);
                });
            }

            const pass = isBeforeSuppressed && isCol1Valid;
            results.push({ context: contextName, pass, audit });
        }

        // Context 1: Main Shop
        await navigateAndAudit('http://localhost/shop/', '1. Main Shop Archive', 'ul.products');

        // Context 2: Product Category Archive
        await navigateAndAudit('http://localhost/product-category/ground-spices/', '2. Category Archive (Ground Spices)', 'ul.products');

        // Context 3: Search / Filter Results
        await navigateAndAudit('http://localhost/shop/?s=chilli&post_type=product', '3. Search Results ("chilli")', 'ul.products');

        // Context 4: Homepage Featured Products
        await navigateAndAudit('http://localhost/', '4. Homepage Featured Products', '#featured-products ul.products');

        // Context 5: Related Products on Single Product
        await navigateAndAudit('http://localhost/product/organic-turmeric-powder/', '5. Related Products', '.sc-related-products ul.products');

        // Context 6: Recently Viewed on Single Product
        await navigateAndAudit(
            'http://localhost/product/organic-turmeric-powder/',
            '6. Recently Viewed',
            '#sc-recently-viewed-grid',
            `localStorage.setItem('spicecraft_recently_viewed', JSON.stringify([22, 23, 24, 25]));`
        );

        // Context 7: Favourites Page
        await navigateAndAudit(
            'http://localhost/favourites/',
            '7. Favourites Page',
            '#sc-favourites-grid',
            `localStorage.setItem('spicecraft_favourites', JSON.stringify([21, 22, 23, 24]));`
        );

        // Responsive Breakpoint Audits on Main Shop
        console.log('\n==================================================');
        console.log('STEP 9 — RESPONSIVE VERIFICATION');
        console.log('==================================================');

        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/shop/' });
        await new Promise(r => setTimeout(r, 1200));

        const breakpoints = [
            { width: 1920, height: 1080, name: '1920px (Desktop XL)', expectedCols: 4 },
            { width: 1440, height: 900,  name: '1440px (Desktop)',    expectedCols: 4 },
            { width: 1280, height: 800,  name: '1280px (Desktop S)',   expectedCols: 4 },
            { width: 1024, height: 768,  name: '1024px (Tablet L)',   expectedCols: 3 },
            { width: 768,  height: 1024, name: '768px (Tablet P)',    expectedCols: 2 },
            { width: 430,  height: 932,  name: '430px (Mobile L)',    expectedCols: 1 },
            { width: 375,  height: 812,  name: '375px (Mobile M)',    expectedCols: 1 },
            { width: 320,  height: 568,  name: '320px (Mobile S)',    expectedCols: 1 }
        ];

        const respResults = [];

        for (const bp of breakpoints) {
            await sendCdp(ws, 'Emulation.setDeviceMetricsOverride', {
                width: bp.width,
                height: bp.height,
                deviceScaleFactor: 1,
                mobile: bp.width < 768
            });
            await new Promise(r => setTimeout(r, 400));

            const bpAudit = await evalScript(ws, `
                (function() {
                    const grid = document.querySelector('ul.products');
                    if (!grid) return null;

                    const gridRect = grid.getBoundingClientRect();
                    const products = Array.from(grid.querySelectorAll('li.product'));
                    if (!products.length) return null;

                    const p0 = products[0].getBoundingClientRect();
                    const startsAtCol1 = Math.abs(p0.x - gridRect.x) < 8;

                    // Count items on row 1
                    const row1Items = products.filter(p => Math.abs(p.getBoundingClientRect().y - p0.y) < 5);

                    return {
                        firstX: Math.round(p0.x),
                        gridX: Math.round(gridRect.x),
                        startsAtCol1,
                        row1Count: row1Items.length,
                        firstWidth: Math.round(p0.width)
                    };
                })()
            `);

            console.log(`Viewport ${bp.name}: GridX=${bpAudit.gridX}, FirstItemX=${bpAudit.firstX}, Row1Items=${bpAudit.row1Count}, Col1Valid=${bpAudit.startsAtCol1 ? '✅ PASS' : '❌ FAIL'}`);
            respResults.push({ bp: bp.name, width: bp.width, pass: bpAudit.startsAtCol1, audit: bpAudit });
        }

        // Reset emulation
        await sendCdp(ws, 'Emulation.clearDeviceMetricsOverride');

        ws.close();

        console.log('\n==================================================');
        console.log('AUDIT SUMMARY');
        console.log('==================================================');

        let allPassed = true;
        for (const r of results) {
            if (!r.pass) {
                allPassed = false;
                console.log(`❌ ${r.context}: FAILED`);
            } else {
                console.log(`✅ ${r.context}: PASSED`);
            }
        }

        for (const r of respResults) {
            if (!r.pass) {
                allPassed = false;
                console.log(`❌ Viewport ${r.bp}: FAILED`);
            } else {
                console.log(`✅ Viewport ${r.bp}: PASSED`);
            }
        }

        if (allPassed) {
            console.log('\n>>> ALL TESTS PASSED: PRODUCT GRID BLANK-SLOT BUG — RESOLVED <<<\n');
        } else {
            console.log('\n>>> ONE OR MORE TESTS FAILED: PRODUCT GRID BLANK-SLOT BUG — NOT RESOLVED <<<\n');
        }
    } finally {
        proc.kill();
    }
}

runTests().catch(err => {
    console.error('Fatal test error:', err);
    process.exit(1);
});
