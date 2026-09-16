const { spawn } = require('child_process');
const http = require('http');

async function testDropdown() {
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9228;
    const proc = spawn(chromePath, [
        '--headless=new',
        `--remote-debugging-port=${port}`,
        '--no-sandbox',
        '--disable-gpu',
        'http://localhost/'
    ]);
    await new Promise(r => setTimeout(r, 2000));
    const listData = await new Promise((res, rej) => {
        http.get(`http://127.0.0.1:${port}/json`, r => {
            let d = '';
            r.on('data', c => d += c);
            r.on('end', () => res(JSON.parse(d)));
        }).on('error', rej);
    });
    const page = listData.find(x => x.type === 'page');
    const ws = new WebSocket(page.webSocketDebuggerUrl);
    await new Promise(r => ws.onopen = r);
    let id = 1;
    function send(method, params = {}) {
        return new Promise(res => {
            const msgId = id++;
            const h = e => {
                const d = JSON.parse(e.data);
                if (d.id === msgId) { ws.removeEventListener('message', h); res(d.result); }
            };
            ws.addEventListener('message', h);
            ws.send(JSON.stringify({ id: msgId, method, params }));
        });
    }

    // Set desktop viewport so primary nav is visible
    await send('Emulation.setDeviceMetricsOverride', {
        width: 1440,
        height: 900,
        deviceScaleFactor: 1,
        mobile: false
    });
    await send('Page.navigate', { url: 'http://localhost/' });
    await new Promise(r => setTimeout(r, 1500));

    // Scroll to 150px
    await send('Runtime.evaluate', { expression: 'window.scrollTo(0, 150);' });
    await new Promise(r => setTimeout(r, 400));

    // Test dropdown focusin & visibility
    const dropRes = await send('Runtime.evaluate', {
        expression: `(function() {
            var item = document.querySelector('.sc-nav-menu .menu-item-has-children');
            if (!item) return JSON.stringify({ error: 'No dropdown item' });
            var subMenu = item.querySelector('.sub-menu');
            var link = item.querySelector(':scope > a');
            
            // Add focus-visible class as main.js does on focusin
            item.classList.add('focus-visible');
            var style = window.getComputedStyle(subMenu);
            var isVisible = style.visibility === 'visible' && style.opacity === '1';
            item.classList.remove('focus-visible');

            // Test search drawer while sticky
            var searchBtn = document.querySelector('.sc-search-toggle');
            searchBtn.click();
            var searchDrawer = document.getElementById('header-search-drawer');
            var searchOpen = searchDrawer.classList.contains('is-active');
            var searchRect = searchDrawer.getBoundingClientRect();
            var headerMain = document.querySelector('.sc-header-main');
            var hmRect = headerMain.getBoundingClientRect();
            searchBtn.click(); // close

            return JSON.stringify({
                dropdownVisibleOnFocus: isVisible,
                subMenuStyle: { opacity: style.opacity, visibility: style.visibility, display: style.display },
                searchDrawerOpen: searchOpen,
                searchTop: searchRect.top,
                hmBottom: hmRect.bottom,
                searchAttachedUnderHeader: Math.abs(searchRect.top - hmRect.bottom) <= 5
            });
        })()`
    });

    console.log('Dropdown & Search test result:', dropRes.result.value);
    ws.close();
    proc.kill();
}
testDropdown();
