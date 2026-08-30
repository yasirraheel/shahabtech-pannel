import urllib.request
import json
import asyncio
import websockets

async def inspect():
    req = urllib.request.urlopen('http://localhost:9222/json')
    pages = json.loads(req.read().decode())
    flow_page = [p for p in pages if 'flow' in p.get('url', '')][0]
    ws_url = flow_page['webSocketDebuggerUrl']
    
    async with websockets.connect(ws_url) as ws:
        js = """
        (function() {
            var buttons = Array.from(document.querySelectorAll('button, a[role="button"], [role="button"]')).map(function(b) {
                return {
                    tag: b.tagName,
                    text: (b.innerText || b.textContent || '').trim().replace(/\\n/g, ' '),
                    aria: b.getAttribute('aria-label'),
                    visible: b.offsetParent !== null,
                    outer: b.outerHTML.substring(0, 180)
                };
            });
            var header = document.querySelector('header');
            return JSON.stringify({
                url: location.href,
                headerHtml: header ? header.outerHTML : null,
                buttons: buttons
            }, null, 2);
        })()
        """
        msg = {'id': 1, 'method': 'Runtime.evaluate', 'params': {'expression': js, 'returnByValue': True}}
        await ws.send(json.dumps(msg))
        resp = await ws.recv()
        data = json.loads(resp)
        print(data['result']['result']['value'])

asyncio.run(inspect())
