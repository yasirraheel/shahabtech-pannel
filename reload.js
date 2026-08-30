async function test() {
  const resp = await fetch('http://localhost:9222/json');
  const pages = await resp.json();
  const flowPage = pages.find(p => p.url && p.url.includes('flow'));
  const ws = new WebSocket(flowPage.webSocketDebuggerUrl);
  ws.onopen = () => {
    ws.send(JSON.stringify({ id: 1, method: 'Page.reload' }));
    setTimeout(() => {
      ws.close();
    }, 1000);
  };
}
test().catch(console.error);
