async function inspect() {
  const resp = await fetch('http://localhost:9222/json');
  const pages = await resp.json();
  const flowPage = pages.find(p => p.url && p.url.includes('flow'));
  const ws = new WebSocket(flowPage.webSocketDebuggerUrl);
  ws.onopen = () => {
    const js = `
      (function() {
        var all = Array.from(document.querySelectorAll('*')).filter(el => {
          var t = (el.innerText || '').trim();
          return t.includes('1K') || t.includes('Original size') || t.includes('2K') || t.includes('4K');
        }).map(el => ({
          tag: el.tagName,
          cls: el.className,
          role: el.getAttribute('role'),
          text: (el.innerText || '').trim(),
          childCount: el.childElementCount
        }));

        var imgs = Array.from(document.querySelectorAll('img, video')).map(m => ({
          tag: m.tagName,
          src: m.src,
          w: m.width,
          h: m.height
        }));

        return JSON.stringify({
          url: location.href,
          menuItems: all,
          media: imgs
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
