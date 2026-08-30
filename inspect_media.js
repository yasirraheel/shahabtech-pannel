async function inspect() {
  const resp = await fetch('http://localhost:9222/json');
  const pages = await resp.json();
  const flowPage = pages.find(p => p.url && p.url.includes('flow/project'));
  const ws = new WebSocket(flowPage.webSocketDebuggerUrl);
  ws.onopen = () => {
    const js = `
      (function() {
        // Find all images and videos
        var media = Array.from(document.querySelectorAll('img, video')).map(m => ({
          tag: m.tagName,
          src: m.src.substring(0, 80),
          w: m.width,
          h: m.height
        }));

        // Find "more_vert" button
        var moreBtn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('more_vert'));

        return JSON.stringify({
          media: media,
          hasMoreBtn: !!moreBtn
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
