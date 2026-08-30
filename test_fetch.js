async function test() {
  const resp = await fetch('http://localhost:9222/json');
  const pages = await resp.json();
  const flowPage = pages.find(p => p.url && p.url.includes('flow'));
  const ws = new WebSocket(flowPage.webSocketDebuggerUrl);
  ws.onopen = () => {
    const js = `
      (async function() {
        var img = document.querySelector('img[src*="getMediaUrlRedirect"]');
        if (!img) return 'NO IMG FOUND';
        var url = img.src;
        var r = await fetch(url);
        var blob = await r.blob();
        return JSON.stringify({
          url: url,
          status: r.status,
          blobSize: blob.size,
          blobType: blob.type
        });
      })()
    `;
    ws.send(JSON.stringify({ id: 1, method: 'Runtime.evaluate', params: { expression: js, returnByValue: true, awaitPromise: true } }));
  };

  ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    if (data.id === 1) {
      console.log('FETCH RESULT:', data.result.result.value);
      ws.close();
    }
  };
}
test().catch(console.error);
