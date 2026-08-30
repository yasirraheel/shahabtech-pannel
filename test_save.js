async function test() {
  const resp = await fetch('http://localhost:9222/json');
  const pages = await resp.json();
  const flowPage = pages.find(p => p.url && p.url.includes('flow'));
  const ws = new WebSocket(flowPage.webSocketDebuggerUrl);
  ws.onopen = () => {
    const js = `
      (function() {
        var img = document.querySelector('img[src*="getMediaUrlRedirect"]');
        if (!img) return 'NO IMG FOUND';
        
        fetch(img.src)
          .then(r => r.blob())
          .then(blob => {
            var reader = new FileReader();
            reader.onloadend = function() {
              var base64 = reader.result.split(',')[1];
              if (window.AndroidBridge && window.AndroidBridge.saveMediaBase64) {
                window.AndroidBridge.saveMediaBase64(base64, 'test_car_image.jpg', blob.type || 'image/jpeg');
              }
            };
            reader.readAsDataURL(blob);
          });
        return 'TRIGGERED_TEST_DOWNLOAD';
      })()
    `;
    ws.send(JSON.stringify({ id: 1, method: 'Runtime.evaluate', params: { expression: js, returnByValue: true } }));
  };

  ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    if (data.id === 1) {
      console.log('TEST RESULT:', data.result.result.value);
      ws.close();
    }
  };
}
test().catch(console.error);
