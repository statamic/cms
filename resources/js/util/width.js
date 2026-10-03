export const GRID_COLUMNS = 12;

/*
 * Resolve a field width to a span of the twelve column grid. Mirrors the
 * .field-w- rules in resources/css/components/widths.css, which is what actually
 * renders. Widgets don't come through here; their widths are resolved entirely by
 * the stylesheet, which is also the only place the widgets-only size keywords are
 * understood.
 *
 * Note the discontinuity: 12 is a span, and so full width, while 13 is read as a
 * percentage and comes out at a sixth. Nothing meaningful lived in 13-24.
 */
export function widthToColumnSpan(width) {
    const number = Number(width);

    if (!Number.isFinite(number)) return GRID_COLUMNS;

    if (Number.isInteger(number) && number >= 1 && number <= GRID_COLUMNS) return number;

    if (number > GRID_COLUMNS) {
        return Math.min(GRID_COLUMNS, Math.max(1, Math.round((number / 100) * GRID_COLUMNS)));
    }

    return GRID_COLUMNS;
}

/*
 * The width as a percentage, for display. Values that are already percentages
 * are left alone so that 66 doesn't read back as 67.
 */
export function widthToPercentage(width) {
    const number = Number(width);

    if (Number.isFinite(number) && number > GRID_COLUMNS) return Math.min(100, number);

    return Math.round((widthToColumnSpan(width) / GRID_COLUMNS) * 100);
}
