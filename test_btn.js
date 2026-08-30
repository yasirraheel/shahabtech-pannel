async function test() {
  const resp = await fetch('http://localhost:9222/json');
  const pages = await resp.json();
  const flowPage = pages.find(p => p.url && p.url.includes('flow'));
  const ws = new WebSocket(flowPage.webSocketDebuggerUrl);
  ws.onopen = () => {
    const js = `
      (function() {
        var btn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('New project'));
        var p = btn;
        var list = [];
        while (p) {
          var cs = window.getComputedStyle(p);
          list.push({
            tag: p.tagName,
            cls: p.className,
            disp: cs.display,
            vis: cs.visibility,
            op: cs.opacity,
            h: cs.height,
            w: cs.width
          });
          p = p.parentElement;
        }
        return JSON.stringify(list, null, 2);
      })()
    `;
    ws.send(JSON.stringify({ id: 1, method: 'Runtime.evaluate', params: { expression: js, returnByValue: true } }));
  };

  ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    if (data.id === 1) {
      console.log(data.result.result.value);
      ws.close();
    }
  };
}
test().catch(console.error);
