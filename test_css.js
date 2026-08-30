async function test() {
  const resp = await fetch('http://localhost:9222/json');
  const pages = await resp.json();
  const flowPage = pages.find(p => p.url && p.url.includes('flow'));
  const ws = new WebSocket(flowPage.webSocketDebuggerUrl);
  ws.onopen = () => {
    const js = `
      (function() {
        var style = document.createElement('style');
        style.textContent = \`
          [data-testid="virtuoso-item-list"] {
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
          }
          /* Also hide headings like Recent projects */
          h1, h2, h3, h4, h5 {
            display: none !important;
          }
        \`;
        document.head.appendChild(style);
        return 'STYLES APPLIED';
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
test().catch(console.error);
