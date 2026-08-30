async function inspect() {
  const resp = await fetch('http://localhost:9222/json');
  const pages = await resp.json();
  const flowPage = pages.find(p => p.url && p.url.includes('flow'));
  const ws = new WebSocket(flowPage.webSocketDebuggerUrl);
  ws.onopen = () => {
    ws.send(JSON.stringify({ id: 1, method: 'Network.enable' }));
    ws.send(JSON.stringify({ id: 2, method: 'Runtime.enable' }));

    setTimeout(() => {
      const js = `
        (function() {
          var btn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('1K') && b.textContent.includes('Original'));
          if (!btn) return 'NO 1K BTN';
          btn.click();
          return 'CLICKED 1K';
        })()
      `;
      ws.send(JSON.stringify({ id: 3, method: 'Runtime.evaluate', params: { expression: js, returnByValue: true } }));
    }, 500);
  };

  const requests = [];
  const logs = [];
  ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    if (data.method === 'Network.requestWillBeSent') {
      requests.push({ url: data.params.request.url, method: data.params.request.method });
    }
    if (data.method === 'Runtime.consoleAPICalled') {
      logs.push(data.params.args.map(a => a.value || a.description).join(' '));
    }
    if (data.method === 'Runtime.exceptionThrown') {
      logs.push('EXCEPTION: ' + JSON.stringify(data.params.exceptionDetails));
    }
    if (data.id === 3) {
      console.log('Eval:', data.result.result.value);
      setTimeout(() => {
        console.log('REQUESTS:', JSON.stringify(requests, null, 2));
        console.log('LOGS:', JSON.stringify(logs, null, 2));
        ws.close();
      }, 3000);
    }
  };
}
inspect().catch(console.error);
