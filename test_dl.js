async function inspect() {
  const resp = await fetch('http://localhost:9222/json');
  const pages = await resp.json();
  const flowPage = pages.find(p => p.url && p.url.includes('flow/project'));
  const ws = new WebSocket(flowPage.webSocketDebuggerUrl);
  ws.onopen = () => {
    const js = `
      (function() {
        var dlBtn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('downloadDownload') || b.textContent.includes('Download'));
        if (!dlBtn) return 'NO BTN';
        
        var fiberKey = Object.keys(dlBtn).find(k => k.startsWith('__reactFiber$') || k.startsWith('__reactProps$'));
        var props = fiberKey ? dlBtn[fiberKey] : null;
        var fnStr = props && props.onClick ? props.onClick.toString() : (props && props.memoizedProps && props.memoizedProps.onClick ? props.memoizedProps.onClick.toString() : 'NO ONCLICK FOUND');
        
        return JSON.stringify({
          fiberKey: fiberKey,
          fnStr: fnStr.substring(0, 500)
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
