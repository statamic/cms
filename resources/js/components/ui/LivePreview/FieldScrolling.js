function scrollContainersOf(doc, element) {
    const win = doc.defaultView;
    const containers = [];

    for (let el = element; el && el !== doc.body; el = el.parentElement) {
        if (el.scrollHeight > el.clientHeight && /auto|scroll|hidden/.test(win.getComputedStyle(el).overflowY)) {
            containers.push(el);
        }
    }

    return containers;
}

function isInView(doc, entry, rect) {
    const win = doc.defaultView;

    if (rect.top < 0 || rect.bottom > win.innerHeight || rect.left < 0 || rect.right > win.innerWidth) return false;

    return scrollContainersOf(doc, entry.element).every(container => {
        const bounds = container.getBoundingClientRect();

        return rect.top >= bounds.top && rect.bottom <= bounds.bottom;
    });
}

function scrollEntryIntoView(doc, entry) {
    const win = doc.defaultView;

    for (const container of scrollContainersOf(doc, entry.element)) {
        const rect = entry.rects()[0];
        const bounds = container.getBoundingClientRect();

        if (rect.top < bounds.top || rect.bottom > bounds.bottom) {
            container.scrollTop += rect.top - bounds.top + rect.height / 2 - container.clientHeight / 2;
        }
    }

    entry.element.scrollIntoView({ block: 'center', inline: 'nearest' });

    const rect = entry.rects()[0];

    if (rect && (rect.top < 0 || rect.bottom > win.innerHeight)) {
        win.scrollBy(0, rect.top + rect.height / 2 - win.innerHeight / 2);
    }
}

export function scrollFieldIntoView(doc, entries) {
    const win = doc.defaultView;
    const occurrences = entries.flatMap(entry => {
        return entry.rects()
            .filter(rect => rect.width && rect.height)
            .map(rect => ({ entry, rect }));
    });

    if (!occurrences.length || occurrences.some(({ entry, rect }) => isInView(doc, entry, rect))) return;

    const distanceFromCenter = ({ rect }) => Math.abs(rect.top - win.innerHeight / 2);
    const nearest = occurrences.sort((a, b) => distanceFromCenter(a) - distanceFromCenter(b))[0];

    scrollEntryIntoView(doc, nearest.entry);
}
