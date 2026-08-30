async function test() {
  const resp = await fetch('http://localhost:9222/json');
  const pages = await resp.json();
  const flowPage = pages.find(p => p.url && p.url.includes('flow'));
  const ws = new WebSocket(flowPage.webSocketDebuggerUrl);
  ws.onopen = () => {
    const js = `
      (function() {
        if (!window.AndroidBridge) return 'NO ANDROID BRIDGE';
        var props = [];
        for (var k in window.AndroidBridge) {
          props.push(k);
        }
        return JSON.stringify(props);
      })()
    `;
    ws.send(JSON.stringify({ id: 1, method: 'Runtime.evaluate', params: { expression: js, returnByValue: true } }));
  };

  ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    if (data.id === 1) {
      console.log('BRIDGE PROPS:', data.result.result.value);
      ws.close();
    }
  };
}
test().catch(console.error);
