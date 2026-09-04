import { test, expect, vi } from 'vitest';
import ChunkedUpload from '../components/assets/ChunkedUpload';

function fakeFile(size, name = 'movie.mp4') {
    return {
        size,
        name,
        slice: (start, end) => new Blob([new Uint8Array(end - start)]),
    };
}

function makeUpload(overrides = {}) {
    return new ChunkedUpload({
        url: '/cp/assets/chunks',
        file: fakeFile(25),
        data: { container: 'main', folder: 'path', _token: 'csrf' },
        chunkSize: 10,
        wait: () => Promise.resolve(),
        ...overrides,
    });
}

test('it uploads the file in sequential chunks', async () => {
    const indexes = [];
    const http = {
        post: vi.fn((url, form) => {
            indexes.push(form.get('chunkIndex'));
            return Promise.resolve({ status: 200, data: { data: { id: 'main::path/movie.mp4' } } });
        }),
    };

    const upload = makeUpload({ http });
    const response = await upload.upload();

    expect(http.post).toHaveBeenCalledTimes(3); // ceil(25 / 10)
    expect(indexes).toEqual(['0', '1', '2']);
    expect(upload.state).toBe('finished');
    expect(response.status).toBe(200);
    expect(JSON.parse(response.data)).toEqual({ data: { id: 'main::path/movie.mp4' } });
});

test('it sends a stable upload id and forwards the upload params', async () => {
    const forms = [];
    const http = {
        post: vi.fn((url, form) => {
            forms.push(form);
            return Promise.resolve({ status: 200, data: {} });
        }),
    };

    await makeUpload({ http }).upload();

    expect(new Set(forms.map((f) => f.get('uploadId'))).size).toBe(1);
    expect(forms[0].get('totalChunks')).toBe('3');
    expect(forms[0].get('container')).toBe('main');
    expect(forms[0].get('folder')).toBe('path');
    expect(forms[0].get('_token')).toBe('csrf');
    expect(forms[2].get('chunkIndex')).toBe('2');
});

test('it aggregates progress and only completes after the final response', async () => {
    const progress = [];
    const http = {
        post: vi.fn((url, form, config) => {
            config.onUploadProgress({ loaded: form.get('chunk').size });
            return Promise.resolve({ status: 200, data: {} });
        }),
    };

    const upload = makeUpload({ http });
    upload.on('progress', (value) => progress.push(value));
    await upload.upload();

    expect(progress).toEqual([...progress].sort((a, b) => a - b)); // non-decreasing
    expect(progress[progress.length - 1]).toBe(1);
});

test('it retries a transient failure before succeeding', async () => {
    let attempt = 0;
    const wait = vi.fn(() => Promise.resolve());
    const http = {
        post: vi.fn(() => {
            attempt++;

            return attempt === 1
                ? Promise.reject({ response: { status: 503 } })
                : Promise.resolve({ status: 200, data: {} });
        }),
    };

    const response = await makeUpload({ http, wait, file: fakeFile(5) }).upload();

    expect(http.post).toHaveBeenCalledTimes(2);
    expect(wait).toHaveBeenCalledTimes(1);
    expect(response.status).toBe(200);
});

test('it does not retry a validation error and surfaces the status', async () => {
    const http = {
        post: vi.fn(() => Promise.reject({ response: { status: 422, data: { errors: { file: ['Too big'] } } } })),
    };

    const upload = makeUpload({ http, file: fakeFile(5) });
    const response = await upload.upload();

    expect(http.post).toHaveBeenCalledTimes(1);
    expect(upload.state).toBe('failed');
    expect(response.status).toBe(422);
    expect(JSON.parse(response.data)).toEqual({ errors: { file: ['Too big'] } });
});
