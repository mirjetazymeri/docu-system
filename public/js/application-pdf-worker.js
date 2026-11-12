/* PDF-Lib is bundled locally: document contents never go to a third party. */
importScripts('../vendors/pdf-lib/pdf-lib-1.17.1.min.js');

self.onmessage = async ({ data: config }) => {
    let label = '';
    const messages = config.messages;
    const userError = message => Object.assign(new Error(message), { userFacing: true });
    try {
        const merged = await PDFLib.PDFDocument.create();
        merged.setTitle(config.manifest.title);
        merged.setCreator('Application Documentation');
        const parts = config.manifest.parts;
        for (let index = 0; index < parts.length; index++) {
            label = parts[index].label;
            self.postMessage({ type: 'progress', completed: index, total: parts.length, label });
            let response;
            for (let attempt = 0; attempt < 4; attempt++) {
                response = await fetch(parts[index].url, {
                    credentials: 'same-origin', cache: 'no-store',
                    headers: { Accept: 'application/pdf, application/json' },
                });
                if (response.status !== 503 || attempt === 3) break;
                await new Promise(resolve => setTimeout(resolve, 3000));
            }
            if (response.status === 409) throw userError(messages.changed);
            if (response.status === 401 || response.status === 403 || response.redirected) {
                throw userError(messages.access);
            }
            if (!response.ok || !(response.headers.get('content-type') || '').includes('application/pdf')) {
                throw userError(messages.http_error.replace(':status', response.status));
            }
            const source = await PDFLib.PDFDocument.load(await response.arrayBuffer());
            const pages = await merged.copyPages(source, source.getPageIndices());
            pages.forEach(page => merged.addPage(page));
            self.postMessage({ type: 'progress', completed: index + 1, total: parts.length, label });
        }

        // Never deliver a mixture of versions if content changed during a long export.
        label = '';
        const check = await fetch(config.manifestUrl, {
            credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' },
        });
        if (!check.ok || check.redirected) throw userError(messages.revision_error);
        const latest = await check.json();
        if (latest.revision !== config.manifest.revision) {
            throw userError(messages.changed_during_export);
        }
        self.postMessage({ type: 'merging' });
        const bytes = await merged.save();
        self.postMessage({ type: 'done', bytes: bytes.buffer, pages: merged.getPageCount() }, [bytes.buffer]);
    } catch (error) {
        self.postMessage({ type: 'error', message: (label ? label + ': ' : '') + (error.userFacing ? error.message : messages.generic) });
    }
};
