import { test, expect, vi, beforeEach, afterEach } from 'vitest';
import useCopy from '../composables/copy';

beforeEach(() => {
    global.Statamic = { $toast: { success: vi.fn(), error: vi.fn() } };
    global.__ = (str) => str;

    Object.assign(navigator, {
        clipboard: { writeText: vi.fn(() => Promise.resolve()) },
    });
});

afterEach(() => {
    vi.restoreAllMocks();
});

test('it reports clipboard as supported when available', () => {
    const { copySupported } = useCopy();
    expect(copySupported.value).toBe(true);
});

test('it copies a value to the clipboard', async () => {
    const { copy, copied } = useCopy();

    await copy('hello');

    expect(navigator.clipboard.writeText).toHaveBeenCalledWith('hello');
    expect(copied.value).toBe(true);
    expect(Statamic.$toast.success).toHaveBeenCalledWith('Copied to clipboard');
});

test('it resets copied state after timeout', async () => {
    vi.useFakeTimers();

    const { copy, copied } = useCopy();

    await copy('hello');
    expect(copied.value).toBe(true);

    vi.advanceTimersByTime(1000);
    expect(copied.value).toBe(false);

    vi.useRealTimers();
});

test('it does not copy when value is empty', async () => {
    const { copy, copied } = useCopy();

    await copy('');
    await copy(null);
    await copy(undefined);

    expect(navigator.clipboard.writeText).not.toHaveBeenCalled();
    expect(copied.value).toBe(false);
});

test('it shows error toast when clipboard write fails', async () => {
    const rejection = Promise.reject();
    navigator.clipboard.writeText = vi.fn(() => rejection);

    const { copy, copied } = useCopy();

    copy('hello');
    await rejection.catch(() => {});
    await vi.dynamicImportSettled();

    expect(copied.value).toBe(false);
    expect(Statamic.$toast.error).toHaveBeenCalledWith('Unable to copy to clipboard');
});

test('it tracks which value was copied via isCopied', async () => {
    const { copy, isCopied } = useCopy();

    await copy('first');

    expect(isCopied('first')).toBe(true);
    expect(isCopied('second')).toBe(false);

    await copy('second');

    expect(isCopied('first')).toBe(false);
    expect(isCopied('second')).toBe(true);
});

test('isCopied resets after timeout', async () => {
    vi.useFakeTimers();

    const { copy, isCopied } = useCopy();

    await copy('hello');
    expect(isCopied('hello')).toBe(true);

    vi.advanceTimersByTime(1000);
    expect(isCopied('hello')).toBe(false);

    vi.useRealTimers();
});
