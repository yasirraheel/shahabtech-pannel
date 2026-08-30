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
        
        var fiberKey = Object.keys(dlBtn).find(k => k.startsWith('__reactFiber$'));
        var fiber = dlBtn[fiberKey];
        var curr = fiber;
        var handlers = [];
        while (curr) {
          if (curr.memoizedProps) {
            if (curr.memoizedProps.onClick) handlers.push({ name: 'memoizedProps.onClick', fn: curr.memoizedProps.onClick.toString() });
            if (curr.memoizedProps.onSelect) handlers.push({ name: 'memoizedProps.onSelect', fn: curr.memoizedProps.onSelect.toString() });
          }
          curr = curr.return;
        }
        return JSON.stringify(handlers.slice(0, 5), null, 2);
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
