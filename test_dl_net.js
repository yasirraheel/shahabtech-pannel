async function inspect() {
  const resp = await fetch('http://localhost:9222/json');
  const pages = await resp.json();
  const flowPage = pages.find(p => p.url && p.url.includes('flow/project'));
  const ws = new WebSocket(flowPage.webSocketDebuggerUrl);
  ws.onopen = () => {
    ws.send(JSON.stringify({ id: 1, method: 'Runtime.enable' }));
    ws.send(JSON.stringify({ id: 2, method: 'Log.enable' }));

    setTimeout(() => {
      const js = `
        (function() {
          var dlBtn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('Download'));
          if (dlBtn) {
            try {
              dlBtn.click();
              return 'CLICKED_SUCCESS';
            } catch(err) {
              return 'ERROR: ' + err.stack;
            }
          }
          return 'NO BTN';
        })()
      `;
      ws.send(JSON.stringify({ id: 3, method: 'Runtime.evaluate', params: { expression: js, returnByValue: true } }));
    }, 500);
  };

  const logs = [];
  ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    if (data.method === 'Runtime.consoleAPICalled') {
      logs.push({
        type: data.params.type,
        args: data.params.args.map(a => a.value || a.description)
      });
    }
    if (data.method === 'Runtime.exceptionThrown') {
      logs.push({
        type: 'EXCEPTION',
        details: data.params.exceptionDetails
      });
    }
    if (data.id === 3) {
      console.log('Evaluate:', data.result.result.value);
      setTimeout(() => {
        console.log('LOGS:', JSON.stringify(logs, null, 2));
        ws.close();
      }, 2000);
    }
  };
}
inspect().catch(console.error);
