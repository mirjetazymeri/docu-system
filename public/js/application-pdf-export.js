(() => {
    'use strict';
    const config = JSON.parse(document.getElementById('pdf-export-config').textContent);
    const messages = config.messages;
    const status = document.getElementById('pdf-status');
    const progress = document.getElementById('pdf-progress');
    const error = document.getElementById('pdf-error');
    const retry = document.getElementById('pdf-retry');
    const open = document.getElementById('pdf-open');
    const download = document.getElementById('pdf-download');
    let worker;
    let blobUrl;

    function fail(message) {
        if (worker) worker.terminate();
        status.textContent = messages.stopped;
        error.textContent = message;
        error.hidden = false;
        retry.hidden = false;
    }

    function start() {
        error.hidden = true;
        retry.hidden = true;
        progress.value = 0;
        status.textContent = messages.preparing;
        try {
            worker = new Worker(config.workerUrl);
            worker.onerror = () => fail(messages.browser_error);
            worker.onmessage = ({ data }) => {
                if (data.type === 'progress') {
                    progress.value = data.completed;
                    status.textContent = `${data.completed} / ${data.total} — ${data.label}`;
                } else if (data.type === 'merging') {
                    status.textContent = messages.merging;
                } else if (data.type === 'error') {
                    fail(data.message);
                } else if (data.type === 'done') {
                    blobUrl = URL.createObjectURL(new Blob([data.bytes], { type: 'application/pdf' }));
                    open.href = download.href = blobUrl;
                    download.download = config.filename;
                    open.hidden = download.hidden = false;
                    progress.value = progress.max;
                    status.textContent = messages.ready.replace(':pages', data.pages);
                    document.getElementById('pdf-hint').textContent = messages.download_hint;
                    worker.terminate();
                }
            };
            worker.postMessage(config);
        } catch (_) {
            fail(messages.unsupported_browser);
        }
    }
    retry.addEventListener('click', () => window.location.reload());
    window.addEventListener('pagehide', () => {
        if (worker) worker.terminate();
        if (blobUrl) URL.revokeObjectURL(blobUrl);
    });
    start();
})();
