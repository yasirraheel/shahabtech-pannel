async function inspect() {
  const resp = await fetch('http://localhost:9222/json');
  const pages = await resp.json();
  const flowPage = pages.find(p => p.url && p.url.includes('flow'));
  const ws = new WebSocket(flowPage.webSocketDebuggerUrl);
  ws.onopen = () => {
    const js = `
      (function() {
        return JSON.stringify({
          nextData: window.__NEXT_DATA__,
          location: location.href
        }, null, 2);
      })()
    `;
    ws.send(JSON.stringify({
      id: 1,
      method: 'Runtime.evaluate',
      params: { expression: js, returnByValue: true }
    }));
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
