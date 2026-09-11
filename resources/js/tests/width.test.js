import { describe, it, expect } from 'vitest';
import { widthToColumnSpan, widthToPercentage } from '../util/width.js';

describe('widthToColumnSpan', () => {
    it('passes spans through', () => {
        for (let span = 1; span <= 12; span++) {
            expect(widthToColumnSpan(span)).toBe(span);
        }
    });

    it('resolves the percentages that shipped before spans', () => {
        expect(widthToColumnSpan(25)).toBe(3);
        expect(widthToColumnSpan(33)).toBe(4);
        expect(widthToColumnSpan(50)).toBe(6);
        expect(widthToColumnSpan(66)).toBe(8);
        expect(widthToColumnSpan(75)).toBe(9);
        expect(widthToColumnSpan(100)).toBe(12);
    });

    it('does not resolve the widgets-only size keywords', () => {
        expect(widthToColumnSpan('sm')).toBe(12);
        expect(widthToColumnSpan('md')).toBe(12);
        expect(widthToColumnSpan('lg')).toBe(12);
        expect(widthToColumnSpan('full')).toBe(12);
    });

    it('treats numbers above twelve as percentages', () => {
        expect(widthToColumnSpan(13)).toBe(2);
        expect(widthToColumnSpan(40)).toBe(5);
        expect(widthToColumnSpan(90)).toBe(11);
    });

    it('clamps percentages beyond a full row', () => {
        expect(widthToColumnSpan(200)).toBe(12);
    });

    it('handles numeric strings', () => {
        expect(widthToColumnSpan('50')).toBe(6);
        expect(widthToColumnSpan('6')).toBe(6);
    });

    it('falls back to full width for anything it does not recognise', () => {
        expect(widthToColumnSpan(undefined)).toBe(12);
        expect(widthToColumnSpan(null)).toBe(12);
        expect(widthToColumnSpan('')).toBe(12);
        expect(widthToColumnSpan('huge')).toBe(12);
        expect(widthToColumnSpan(0)).toBe(12);
        expect(widthToColumnSpan(-5)).toBe(12);
        expect(widthToColumnSpan(7.5)).toBe(12);
    });
});

describe('widthToPercentage', () => {
    it('leaves percentages alone', () => {
        expect(widthToPercentage(25)).toBe(25);
        expect(widthToPercentage(33)).toBe(33);
        expect(widthToPercentage(66)).toBe(66);
        expect(widthToPercentage(100)).toBe(100);
    });

    it('derives a percentage from a span', () => {
        expect(widthToPercentage(3)).toBe(25);
        expect(widthToPercentage(4)).toBe(33);
        expect(widthToPercentage(6)).toBe(50);
        expect(widthToPercentage(9)).toBe(75);
        expect(widthToPercentage(12)).toBe(100);
    });

    it('reports an unrecognised width as full width', () => {
        expect(widthToPercentage(undefined)).toBe(100);
    });
});
