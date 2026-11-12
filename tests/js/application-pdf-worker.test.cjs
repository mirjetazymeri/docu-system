const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const PDFLib = require('../../public/vendors/pdf-lib/pdf-lib-1.17.1.min.js');
const workerCode = fs.readFileSync(path.join(__dirname, '../../public/js/application-pdf-worker.js'), 'utf8');

const englishMessages = {
    changed: 'The documentation has changed.',
    access: 'Check your login and permission for this project.',
    http_error: 'The document could not be prepared (HTTP :status).',
    revision_error: 'The documentation version could not be verified.',
    changed_during_export: 'The documentation changed during preparation.',
    generic: 'The document could not be prepared.',
};

async function run(parts, fetchPart, latestRevision = 'v1', translations = englishMessages) {
    const messages = [];
    const config = { manifest: { parts, revision: 'v1', title: 'TestDocu' }, manifestUrl: '/manifest', messages: translations };
    const context = vm.createContext({
        PDFLib,
        importScripts: () => {},
        setTimeout: fn => { fn(); },
        fetch: async url => url === '/manifest'
            ? { ok: true, json: async () => ({ revision: latestRevision }) }
            : fetchPart(url),
        self: { postMessage: message => messages.push(message) },
    });
    vm.runInContext(workerCode, context);
    await context.self.onmessage({ data: config });
    return messages;
}

function response(bytes) {
    return { ok: true, status: 200, headers: { get: () => 'application/pdf' }, arrayBuffer: async () => bytes };
}

test('203 documents merge into one PDF with every page in source order', async () => {
    const first = await PDFLib.PDFDocument.create();
    first.addPage([300, 400]);
    const second = await PDFLib.PDFDocument.create();
    second.addPage([400, 500]);
    second.addPage([500, 600]);
    const bytes = [await first.save(), await second.save()];
    const parts = Array.from({ length: 203 }, (_, id) => ({ url: String(id), label: `Document ${id}` }));
    const messages = await run(parts, async url => response(bytes[Number(url) % 2]));
    assert.equal(messages.at(-1).type, 'done');
    const output = await PDFLib.PDFDocument.load(messages.at(-1).bytes);
    const expected = parts.flatMap((_, id) => id % 2 ? [400, 500] : [300]);
    assert.equal(output.getTitle(), 'TestDocu');
    assert.equal(output.getPageCount(), 304);
    assert.deepEqual(output.getPages().map(page => page.getWidth()), expected);
});

test('a failed document is identified and no incomplete PDF is delivered', async () => {
    const messages = await run([{ url: '/bad', label: 'Orders / API' }], async () => ({ ok: false, status: 422 }));
    assert.equal(messages.at(-1).type, 'error');
    assert.match(messages.at(-1).message, /Orders \/ API/);
    assert.equal(messages.some(message => message.type === 'done'), false);
});

test('content changed during generation prevents delivery of mixed versions', async () => {
    const pdf = await PDFLib.PDFDocument.create();
    pdf.addPage();
    const bytes = await pdf.save();
    const messages = await run([{ url: '/part', label: 'Auth' }], async () => response(bytes), 'v2');
    assert.equal(messages.at(-1).type, 'error');
    assert.equal(messages.some(message => message.type === 'done'), false);
});

test('busy part retries are bounded', async () => {
    let attempts = 0;
    const messages = await run([{ url: '/busy', label: 'Auth' }], async () => {
        attempts++;
        return { ok: false, status: 503 };
    });
    assert.equal(attempts, 4);
    assert.equal(messages.at(-1).type, 'error');
});

test('worker errors use the selected page language and retain document names', async () => {
    const albanian = { ...englishMessages, http_error: 'Dokumenti nuk u përgatit (HTTP :status).' };
    for (const messages of [englishMessages, albanian]) {
        const result = await run([{ url: '/bad', label: 'My document' }], async () => ({ ok: false, status: 422 }), 'v1', messages);
        assert.equal(result.at(-1).message, 'My document: ' + messages.http_error.replace(':status', 422));
    }
});

test('unexpected errors display localized feedback instead of untranslated library messages', async () => {
    const messages = { ...englishMessages, generic: 'Dokumenti nuk u përgatit.' };
    const result = await run([{ url: '/bad', label: 'My document' }], async () => { throw new Error('Failed to fetch'); }, 'v1', messages);
    assert.equal(result.at(-1).message, 'My document: Dokumenti nuk u përgatit.');
});
