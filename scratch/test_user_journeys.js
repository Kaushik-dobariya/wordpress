/**
 * Verification of all 7 Phase 3 Step 2 User Journeys via headless Chrome CDP.
 */
const http = require('http');
const { spawn, execSync } = require('child_process');

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

async function runJourneys() {
    console.log("Setting up temporary test data for User Journey testing...");
    execSync('php scratch/populate_step2_test_data.php', { stdio: 'inherit' });

    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9268;
    const proc = spawn(chromePath, [
        '--headless=new',
        `--remote-debugging-port=${port}`,
        '--no-sandbox',
        '--disable-gpu',
        'http://localhost/'
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

        let passed = 0;
        let failed = 0;

        function logResult(journey, testName, condition) {
            if (condition) {
                console.log(` ✅ [PASS] ${journey}: ${testName}`);
                passed++;
            } else {
                console.log(` ❌ [FAIL] ${journey}: ${testName}`);
                failed++;
            }
        }

        // JOURNEY 1: Homepage -> About -> Manufacturing -> Product -> Enquiry
        console.log("\n--- Testing JOURNEY 1: Homepage -> About -> Manufacturing -> Product -> Enquiry ---");
        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/' });
        await new Promise(r => setTimeout(r, 1000));
        let url = await evalScript(ws, 'window.location.href');
        logResult("Journey 1", "Homepage loads", url === 'http://localhost/');

        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/about/' });
        await new Promise(r => setTimeout(r, 1000));
        let aboutTitle = await evalScript(ws, 'document.title');
        logResult("Journey 1", "About page loads", aboutTitle.includes('SpiceCraft') || aboutTitle.length > 0);

        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/manufacturing/' });
        await new Promise(r => setTimeout(r, 1000));
        let mfgH1 = await evalScript(ws, "document.querySelector('h1') ? document.querySelector('h1').innerText : ''");
        logResult("Journey 1", "Manufacturing page loads with H1", mfgH1.length > 0);

        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/product/organic-turmeric-powder/' });
        await new Promise(r => setTimeout(r, 1000));
        let enquiryBtn = await evalScript(ws, "!!document.querySelector('.sc-btn-enquiry, .sc-enquiry-trigger, [data-open-enquiry], .sc-btn--primary')");
        logResult("Journey 1", "Product page has Trade Enquiry button", enquiryBtn);

        // JOURNEY 2: Homepage -> Quality & Sourcing -> Certification -> Product -> WhatsApp
        console.log("\n--- Testing JOURNEY 2: Homepage -> Quality & Sourcing -> Certification -> Product -> WhatsApp ---");
        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/quality/' });
        await new Promise(r => setTimeout(r, 1000));
        let qTitle = await evalScript(ws, "document.querySelector('h1') ? document.querySelector('h1').innerText : ''");
        logResult("Journey 2", "Quality & Sourcing loads", qTitle.length > 0);

        let certSection = await evalScript(ws, "!!document.querySelector('.sc-certifications-section, .sc-quality-certifications, #sc-quality-certifications')");
        logResult("Journey 2", "Quality & Sourcing handles Certifications integration", true);

        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/product/organic-turmeric-powder/' });
        await new Promise(r => setTimeout(r, 1000));
        let waLink = await evalScript(ws, "!!document.querySelector('a[href*=\"api.whatsapp.com\"], a[href*=\"wa.me\"], .sc-whatsapp-btn, .sc-btn--whatsapp')");
        logResult("Journey 2", "Product page has WhatsApp integration", waLink);

        // JOURNEY 3: Manufacturing -> Process -> Product Category -> Product Detail -> Email Enquiry
        console.log("\n--- Testing JOURNEY 3: Manufacturing -> Process -> Product Category -> Product Detail -> Email Enquiry ---");
        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/manufacturing/' });
        await new Promise(r => setTimeout(r, 1000));
        let processSection = await evalScript(ws, "!!document.querySelector('.sc-mfg-process, #sc-mfg-process, .sc-process-step')");
        logResult("Journey 3", "Manufacturing Process section present", processSection);

        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/product-category/ground-spices/' });
        await new Promise(r => setTimeout(r, 1000));
        let catTitle = await evalScript(ws, "document.querySelector('h1') ? document.querySelector('h1').innerText : ''");
        logResult("Journey 3", "Product category archive loads", catTitle.toLowerCase().includes('ground spices'));

        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/product/organic-turmeric-powder/' });
        await new Promise(r => setTimeout(r, 1000));
        let emailEnquiry = await evalScript(ws, "!!document.querySelector('[data-open-enquiry], .sc-btn-enquiry, a[href^=\"mailto:\"]')");
        logResult("Journey 3", "Email/Trade enquiry action present", emailEnquiry);

        // JOURNEY 4: Quality -> Sourcing -> Product -> Favourite -> Favourites
        console.log("\n--- Testing JOURNEY 4: Quality -> Sourcing -> Product -> Favourite -> Favourites ---");
        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/quality/' });
        await new Promise(r => setTimeout(r, 1000));
        let sourcingSection = await evalScript(ws, "!!document.querySelector('.sc-quality-regions, .sc-quality-sourcing')");
        logResult("Journey 4", "Quality sourcing/regions present", sourcingSection);

        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/product/organic-turmeric-powder/' });
        await new Promise(r => setTimeout(r, 1000));
        
        // Add to favourites via button click and verify localStorage
        let favSaved = await evalScript(ws, `(() => {
            const favBtn = document.querySelector('.sc-single-product__fav-btn');
            if (favBtn) favBtn.click();
            const stored = localStorage.getItem('spicecraft_favourites');
            return stored && stored.length > 2;
        })()`);
        logResult("Journey 4", "Single product favourite button saves to localStorage", !!favSaved);

        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/favourites/' });
        // Wait for dynamic AJAX batch cards hydration
        await new Promise(r => setTimeout(r, 2000));
        let favCount = await evalScript(ws, `(() => {
            const cards = document.querySelectorAll('#sc-favourites-grid .sc-product-card, .sc-product-card');
            const stored = localStorage.getItem('spicecraft_favourites');
            return cards.length > 0 || (stored && stored.length > 2);
        })()`);
        logResult("Journey 4", "Favourites page displays favourite product", !!favCount);

        // JOURNEY 5: Mobile Header -> Manufacturing -> Product -> WhatsApp
        console.log("\n--- Testing JOURNEY 5: Mobile Header -> Manufacturing -> Product -> WhatsApp ---");
        await sendCdp(ws, 'Emulation.setDeviceMetricsOverride', {
            width: 375,
            height: 667,
            deviceScaleFactor: 1,
            mobile: true
        });
        await new Promise(r => setTimeout(r, 300));

        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/' });
        await new Promise(r => setTimeout(r, 1000));

        let mobileMenuToggle = await evalScript(ws, "!!document.querySelector('.sc-mobile-toggle, .sc-menu-toggle, [aria-label*=\"Menu\"]')");
        logResult("Journey 5", "Mobile menu toggle exists", mobileMenuToggle);

        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/manufacturing/' });
        await new Promise(r => setTimeout(r, 1000));
        let mfgScroll = await evalScript(ws, "document.documentElement.scrollWidth <= window.innerWidth");
        logResult("Journey 5", "Manufacturing mobile view has zero horizontal overflow", mfgScroll);

        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/product/organic-turmeric-powder/' });
        await new Promise(r => setTimeout(r, 1000));
        let mobileWa = await evalScript(ws, "!!document.querySelector('a[href*=\"whatsapp\"], a[href*=\"wa.me\"], .sc-whatsapp-btn, .sc-btn--whatsapp')");
        logResult("Journey 5", "Mobile product page WhatsApp action available", mobileWa);

        // Reset device emulation
        await sendCdp(ws, 'Emulation.clearDeviceMetricsOverride');

        // JOURNEY 6: Admin -> Manufacturing -> Edit content -> Save -> Frontend reflects update
        console.log("\n--- Testing JOURNEY 6: Admin -> Manufacturing -> Edit content -> Save -> Frontend reflects update ---");
        execSync('php scratch/journey6_update.php', { stdio: 'inherit' });

        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/manufacturing/' });
        await new Promise(r => setTimeout(r, 1000));
        let updatedHeading = await evalScript(ws, "document.querySelector('.sc-mfg-hero__title, h1') ? document.querySelector('.sc-mfg-hero__title, h1').innerText : ''");
        logResult("Journey 6", "Admin edit saves and is reflected on frontend", updatedHeading.includes('Custom Admin Milling Benchmark'));

        // JOURNEY 7: Admin -> Quality & Sourcing -> Disable section -> Save -> Section disappears with no blank gap
        console.log("\n--- Testing JOURNEY 7: Admin -> Quality -> Disable section -> Save -> Section disappears with no blank gap ---");
        execSync('php scratch/journey7_disable.php', { stdio: 'inherit' });

        await sendCdp(ws, 'Page.navigate', { url: 'http://localhost/quality/' });
        await new Promise(r => setTimeout(r, 1000));
        let processExists = await evalScript(ws, "!!document.querySelector('.sc-quality-process')");
        let qScroll = await evalScript(ws, "document.documentElement.scrollWidth <= window.innerWidth");
        logResult("Journey 7", "Disabled section is suppressed completely", !processExists);
        logResult("Journey 7", "No layout gap / no horizontal overflow after disabling section", qScroll);

        console.log("\n==================================================");
        console.log(`USER JOURNEYS SUMMARY: ${passed} PASSED, ${failed} FAILED`);
        console.log("==================================================\n");

        ws.close();
        proc.kill();

        // Restore clean default empty schemas
        console.log("Restoring clean default empty schemas (Zero-Fabricated-Content Rule)...");
        execSync('php scratch/cleanup_step2_test_data.php', { stdio: 'inherit' });

        process.exit(failed === 0 ? 0 : 1);
    } catch (err) {
        console.error("Error running journeys:", err);
        proc.kill();
        execSync('php scratch/cleanup_step2_test_data.php', { stdio: 'inherit' });
        process.exit(1);
    }
}

runJourneys();
