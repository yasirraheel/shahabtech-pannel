async function inspect() {
  const resp = await fetch('http://localhost:9222/json');
  const pages = await resp.json();
  const flowPage = pages.find(p => p.url && p.url.includes('flow/project'));
  const ws = new WebSocket(flowPage.webSocketDebuggerUrl);
  ws.onopen = () => {
    // Click the image
    const js = `
      (function() {
        var img = Array.from(document.querySelectorAll('img')).find(i => i.src.includes('getMediaUrlRedirect'));
        if (img) {
          img.click();
          return 'CLICKED IMAGE';
        }
        return 'NO MEDIA IMAGE FOUND';
      })()
    `;
    ws.send(JSON.stringify({ id: 1, method: 'Runtime.evaluate', params: { expression: js, returnByValue: true } }));
  };

  ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    if (data.id === 1) {
      console.log(data.result.result.value);
      setTimeout(() => {
        const js2 = `
          (function() {
            var btns = Array.from(document.querySelectorAll('button, a, [role="button"]')).map(b => ({
              text: (b.innerText || '').trim().replace(/\\n/g, ' '),
              aria: b.getAttribute('aria-label'),
              tag: b.tagName
            }));
            return JSON.stringify(btns.filter(b => b.text || b.aria), null, 2);
          })()
        `;
        ws.send(JSON.stringify({ id: 2, method: 'Runtime.evaluate', params: { expression: js2, returnByValue: true } }));
      }, 500);
    }
    if (data.id === 2) {
      console.log(data.result.result.value);
      ws.close();
    }
  };
}
inspect().catch(console.error);
