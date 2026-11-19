import { test, expect, vi, beforeEach, afterEach } from 'vitest';
import throttle from '../util/throttle';

beforeEach(() => {
    vi.useFakeTimers();
});

afterEach(() => {
    vi.useRealTimers();
});

test('it calls the function immediately on first call', () => {
    const fn = vi.fn();
    const throttled = throttle(fn, 200);

    throttled('a');

    expect(fn).toHaveBeenCalledTimes(1);
    expect(fn).toHaveBeenCalledWith('a');
});

test('it suppresses calls within the time frame', () => {
    const fn = vi.fn();
    const throttled = throttle(fn, 200);

    throttled();
    throttled();
    throttled();

    expect(fn).toHaveBeenCalledTimes(1);
});

test('it fires a trailing call after the time frame', () => {
    const fn = vi.fn();
    const throttled = throttle(fn, 200);

    throttled('first');
    throttled('second');

    expect(fn).toHaveBeenCalledTimes(1);
    expect(fn).toHaveBeenCalledWith('first');

    vi.advanceTimersByTime(200);

    expect(fn).toHaveBeenCalledTimes(2);
    expect(fn).toHaveBeenLastCalledWith('second');
});

test('the trailing call uses the most recent arguments', () => {
    const fn = vi.fn();
    const throttled = throttle(fn, 200);

    throttled('first');
    throttled('second');
    throttled('third');

    vi.advanceTimersByTime(200);

    expect(fn).toHaveBeenCalledTimes(2);
    expect(fn).toHaveBeenLastCalledWith('third');
});

test('it allows another immediate call after the time frame elapses', () => {
    const fn = vi.fn();
    const throttled = throttle(fn, 200);

    throttled();
    expect(fn).toHaveBeenCalledTimes(1);

    vi.advanceTimersByTime(200);

    throttled();
    expect(fn).toHaveBeenCalledTimes(2);
});

test('cancel prevents the trailing call from firing', () => {
    const fn = vi.fn();
    const throttled = throttle(fn, 200);

    throttled('first');
    throttled('second');

    expect(fn).toHaveBeenCalledTimes(1);

    throttled.cancel();
    vi.advanceTimersByTime(200);

    expect(fn).toHaveBeenCalledTimes(1);
});

test('it works normally after cancel is called', () => {
    const fn = vi.fn();
    const throttled = throttle(fn, 200);

    throttled('first');
    throttled('second');
    throttled.cancel();

    vi.advanceTimersByTime(200);

    throttled('third');

    expect(fn).toHaveBeenCalledTimes(2);
    expect(fn).toHaveBeenLastCalledWith('third');
});

test('the trailing call fires after the remaining time in the window', () => {
    const fn = vi.fn();
    const throttled = throttle(fn, 200);

    throttled();

    vi.advanceTimersByTime(100);
    throttled('trailing');

    // Should not have fired yet — only 100ms remaining
    vi.advanceTimersByTime(99);
    expect(fn).toHaveBeenCalledTimes(1);

    // Now it should fire
    vi.advanceTimersByTime(1);
    expect(fn).toHaveBeenCalledTimes(2);
    expect(fn).toHaveBeenLastCalledWith('trailing');
});
