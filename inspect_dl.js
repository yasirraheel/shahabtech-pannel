async function inspect() {
  const resp = await fetch('http://localhost:9222/json');
  const pages = await resp.json();
  const flowPage = pages.find(p => p.url && p.url.includes('flow'));
  const ws = new WebSocket(flowPage.webSocketDebuggerUrl);
  ws.onopen = () => {
    const js = `
      (function() {
        var buttons = Array.from(document.querySelectorAll('button, a, [role="button"]')).map(el => ({
          tag: el.tagName,
          text: (el.innerText || '').trim().replace(/\\n/g, ' '),
          aria: el.getAttribute('aria-label'),
          href: el.getAttribute('href'),
          download: el.getAttribute('download'),
          cls: el.className
        }));
        
        // Find elements with "download" in aria-label, text, or title
        var dlElements = buttons.filter(b => 
          (b.text && b.text.toLowerCase().includes('download')) ||
          (b.aria && b.aria.toLowerCase().includes('download')) ||
          (b.download !== null)
        );

        return JSON.stringify({
          url: location.href,
          dlElements: dlElements,
          allButtonsSample: buttons.map(b => b.text || b.aria).filter(Boolean)
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
inspect().catch(console.error);
