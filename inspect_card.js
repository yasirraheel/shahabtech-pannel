async function inspect() {
  const resp = await fetch('http://localhost:9222/json');
  const pages = await resp.json();
  const flowPage = pages.find(p => p.url && p.url.includes('flow'));
  const ws = new WebSocket(flowPage.webSocketDebuggerUrl);
  ws.onopen = () => {
    const js = `
      (function() {
        var btn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('New project'));
        var virtuoso = document.querySelector('[data-virtuoso-scroller="true"]');
        var itemList = document.querySelector('[data-testid="virtuoso-item-list"]');
        return JSON.stringify({
          virtuosoContainsBtn: virtuoso ? virtuoso.contains(btn) : false,
          itemListContainsBtn: itemList ? itemList.contains(btn) : false,
          btnParentTag: btn ? btn.parentElement.tagName + '.' + btn.parentElement.className : null
        });
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
