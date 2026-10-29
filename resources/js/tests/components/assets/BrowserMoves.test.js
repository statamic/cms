import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import { reactive } from 'vue';
import Browser from '@/components/assets/Browser/Browser.vue';

window.__n = (key, count, replacements = {}) => {
    const message = key.split('|')[count === 1 ? 0 : 1].replace(':count', count);

    return Object.entries(replacements).reduce((message, [name, value]) => message.replace(`:${name}`, value), message);
};

const action = { handle: 'move_asset', context: { container: 'assets' } };
const callback = ['replaceInSelections', { 'assets::a.jpg': 'assets::photos/a.jpg' }];

beforeEach(() => {
    vi.useFakeTimers();

    window.Statamic = {
        $toast: { success: vi.fn(), error: vi.fn() },
        $callbacks: { call: vi.fn() },
    };
});

afterEach(() => {
    vi.useRealTimers();
});

function deferred() {
    let resolve;
    let reject;
    const promise = new Promise((res, rej) => {
        resolve = res;
        reject = rej;
    });

    return { promise, resolve, reject };
}

function browser({ isSearching = false, post } = {}) {
    const component = reactive({
        assets: [
            { id: 'assets::a.jpg', thumbnail: '/a.jpg' },
            { id: 'assets::b.pdf', thumbnail: null },
        ],
        isSearching,
        loading: false,
        moving: [],
        moved: [],
        removed: [],
        drops: [],
        settlingMoves: [],
        reflowing: false,
        $axios: { post: post ?? vi.fn(() => Promise.resolve({ data: { callback } })) },
        $refs: { listing: { refresh: vi.fn() } },
    });

    Object.entries(Browser.methods).forEach(([name, method]) => (component[name] = method.bind(component)));

    return component;
}

function move(component, items = ['assets::a.jpg', 'assets::b.pdf'], destination = 'photos') {
    component.moveItems({ url: '/asset-actions', action, items, destination });
}

test('dropping dims the items and starts the request straight away', () => {
    const component = browser();

    move(component);

    expect(component.$axios.post).toHaveBeenCalledWith('/asset-actions', {
        action: 'move_asset',
        context: { container: 'assets' },
        selections: ['assets::a.jpg', 'assets::b.pdf'],
        values: { folder: 'photos' },
    });
    expect(component.moving).toEqual(['assets::a.jpg', 'assets::b.pdf']);
    expect(component.loading).toBe(true);
    expect(component.drops).toEqual([]);
});

test('the folder opens and the items fade out after a short delay, then leave the layout', async () => {
    const component = browser({ post: vi.fn(() => deferred().promise) });

    move(component);
    await vi.advanceTimersByTimeAsync(250);

    expect(component.drops).toMatchObject([
        { path: 'photos', files: [{ thumbnail: '/a.jpg' }, { thumbnail: null }], closing: false },
    ]);
    expect(component.moving).toEqual([]);
    expect(component.moved).toEqual(['assets::a.jpg', 'assets::b.pdf']);
    expect(component.removed).toEqual([]);

    await vi.advanceTimersByTimeAsync(300);

    expect(component.removed).toEqual(['assets::a.jpg', 'assets::b.pdf']);
    expect(component.reflowing).toBe(true);
});

test('the bundle shows at most three files', async () => {
    const component = browser({ post: vi.fn(() => deferred().promise) });

    move(component, ['assets::a.jpg', 'assets::b.pdf', 'assets::c.jpg', 'assets::d.jpg']);
    await vi.advanceTimersByTimeAsync(250);

    expect(component.drops[0].files).toHaveLength(3);
});

test('the folder stays open until the move finishes', async () => {
    const request = deferred();
    const component = browser({ post: vi.fn(() => request.promise) });

    move(component);
    await vi.advanceTimersByTimeAsync(5000);

    expect(component.drops[0].closing).toBe(false);

    request.resolve({ data: { callback } });
    await vi.advanceTimersByTimeAsync(0);

    expect(component.drops[0].closing).toBe(true);
    expect(Statamic.$toast.success).toHaveBeenCalledWith('Moved 2 items to photos');

    await vi.advanceTimersByTimeAsync(300);

    expect(component.drops).toEqual([]);
});

test('quick moves keep the folder open long enough to see the files land', async () => {
    const component = browser();

    move(component);
    await vi.advanceTimersByTimeAsync(250 + 519);

    expect(component.drops[0].closing).toBe(false);

    await vi.advanceTimersByTimeAsync(1);

    expect(component.drops[0].closing).toBe(true);
});

test('quick moves wait for the items to fade out before refreshing', async () => {
    const component = browser();

    move(component);
    await vi.advanceTimersByTimeAsync(549);

    expect(component.$refs.listing.refresh).not.toHaveBeenCalled();

    await vi.advanceTimersByTimeAsync(1);

    expect(component.$refs.listing.refresh).toHaveBeenCalled();
});

test('a failed move puts the items back and closes the folder', async () => {
    const request = deferred();
    const component = browser({ post: vi.fn(() => request.promise) });

    move(component);
    await vi.advanceTimersByTimeAsync(1000);

    request.reject({ response: { data: { message: 'Nope' } } });
    await vi.advanceTimersByTimeAsync(0);

    expect(component.moving).toEqual([]);
    expect(component.moved).toEqual([]);
    expect(component.removed).toEqual([]);
    expect(component.drops[0].closing).toBe(true);
    expect(Statamic.$toast.error).toHaveBeenCalledWith('Nope');
    expect(Statamic.$toast.success).not.toHaveBeenCalled();
});

test('a move that fails before the animation starts skips it', async () => {
    const component = browser({ post: vi.fn(() => Promise.reject({})) });

    move(component);
    await vi.advanceTimersByTimeAsync(250);

    expect(component.drops).toEqual([]);
    expect(component.moving).toEqual([]);
    expect(component.moved).toEqual([]);
});

test('dropping into a folder that is already open keeps it open until every move finishes', async () => {
    const first = deferred();
    const second = deferred();
    const post = vi.fn().mockReturnValueOnce(first.promise).mockReturnValueOnce(second.promise);
    const component = browser({ post });

    move(component, ['assets::a.jpg']);
    await vi.advanceTimersByTimeAsync(250);
    move(component, ['assets::b.pdf']);
    await vi.advanceTimersByTimeAsync(1000);

    expect(component.drops).toHaveLength(1);
    expect(component.drops[0].files).toEqual([{ thumbnail: null }]);

    first.resolve({ data: {} });
    await vi.advanceTimersByTimeAsync(0);

    expect(component.drops[0].closing).toBe(false);

    second.resolve({ data: {} });
    await vi.advanceTimersByTimeAsync(0);

    expect(component.drops[0].closing).toBe(true);
});

test('moves into different folders open and close independently', async () => {
    const first = deferred();
    const second = deferred();
    const post = vi.fn().mockReturnValueOnce(first.promise).mockReturnValueOnce(second.promise);
    const component = browser({ post });

    move(component, ['assets::a.jpg'], 'photos');
    move(component, ['assets::b.pdf'], 'docs');
    await vi.advanceTimersByTimeAsync(1000);

    first.resolve({ data: {} });
    await vi.advanceTimersByTimeAsync(0);

    expect(component.drops.find((drop) => drop.path === 'photos').closing).toBe(true);
    expect(component.drops.find((drop) => drop.path === 'docs').closing).toBe(false);
});

test('search results stay dimmed in the listing instead of leaving it', async () => {
    const component = browser({ isSearching: true, post: vi.fn(() => deferred().promise) });

    move(component);
    await vi.advanceTimersByTimeAsync(1000);

    expect(component.drops).toHaveLength(1);
    expect(component.moving).toEqual(['assets::a.jpg', 'assets::b.pdf']);
    expect(component.moved).toEqual([]);
    expect(component.removed).toEqual([]);
});

test('settling a move forgets the items and keeps them selected under their new IDs', async () => {
    const component = browser();

    move(component);
    await vi.advanceTimersByTimeAsync(600);

    component.settleMoves();

    expect(Statamic.$callbacks.call).toHaveBeenCalledWith(...callback);
    expect(component.moving).toEqual([]);
    expect(component.moved).toEqual([]);
    expect(component.removed).toEqual([]);
    expect(component.settlingMoves).toEqual([]);
    expect(component.loading).toBe(false);
});
