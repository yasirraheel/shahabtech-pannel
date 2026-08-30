async function test() {
  const resp = await fetch('http://localhost:9222/json');
  const pages = await resp.json();
  const flowPage = pages.find(p => p.url && p.url.includes('flow'));
  const ws = new WebSocket(flowPage.webSocketDebuggerUrl);
  ws.onopen = () => {
    const js = `
      (function() {
        // Test hiding project cards
        var cards = document.querySelectorAll('div:has(> a[href*="/project/"])');
        cards.forEach(c => {
          c.style.setProperty('visibility', 'hidden', 'important');
          c.style.setProperty('opacity', '0', 'important');
          c.style.setProperty('pointer-events', 'none', 'important');
        });
        var btn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('New project'));
        var btnRect = btn ? btn.getBoundingClientRect() : null;
        return JSON.stringify({
          cardsCount: cards.length,
          btnVisible: btn ? btn.offsetParent !== null : false,
          btnRect: btnRect
        }, null, 2);
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
