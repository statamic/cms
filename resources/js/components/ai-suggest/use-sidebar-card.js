import { onBeforeUnmount, ref, watch } from 'vue';

// Prototype: lays a card over the entry's sidebar column (the one with Published and the slug), for suggestions too big for a dropdown
// (see AiSuggestLarge.vue). The card scrolls with the page natively, so it never lags behind it. A wrapper is put in the page's scrolling area,
// from the column's top right down to the end of the page, and the card sticks to its top as you scroll. As the wrapper runs to the end of the
// page (not the end of the column, which pushed the card up off screen), the card is never pushed up, and its height fits the screen (see fill).
// Only the wrapper is placed by hand, when the column moves or resizes, never on scroll.
//
// Only one field's card is open at a time. Opening another field's swaps it in, closing the first in the same moment, so there's never a gap
// with no card. Clicking another field's suggest button (marked data-ai-suggest-button) doesn't close a card itself: opening the new one does.
let closeOpenCard = null;

// The classes for the card's entrance: it fades and zooms in, unless it took over from another field's card ("switched"). Then it just appears
// in its place, so it reads as the same card showing another field, not something new arriving.
export const cardEntrance = (switched) => (switched ? '' : 'animate-in fade-in-0 zoom-in-95 duration-100');

// Whether a click landed on a suggest button, which opens its own card (and so closes this one) itself
export const onSuggestButton = (event) => !!event.target.closest?.('[data-ai-suggest-button]');

// showing: whether the card is open. anchor: an element in the field, to find the column from. close: closes this card.
// Returns place (the wrapper's style, null when there's no column), fit (the card's), scroller (where to teleport the wrapper) and switched
// (whether it took over from another field's card).
export default function useSidebarCard(showing, anchor, close) {
    const place = ref(null);
    const switched = ref(false);
    const fit = ref(null);
    const scroller = ref(null);
    let aside = null;
    let resizes = null;

    function sidebarOf(element) {
        for (let parent = element?.parentElement; parent; parent = parent.parentElement) {
            const found = parent.querySelector(':scope > aside');
            if (found) return found;
        }
        return null;
    }

    function position() {
        if (!aside || !scroller.value) return;
        const area = scroller.value;
        const box = area.getBoundingClientRect();
        const column = aside.getBoundingClientRect();
        // The page's content card, which runs to the end of what scrolls
        const page = (area.firstElementChild ?? aside).getBoundingClientRect();
        const top = column.top - box.top + area.scrollTop;
        const height = Math.max(page.bottom, column.bottom) - box.top + area.scrollTop - top;
        place.value = { top: `${top}px`, left: `${column.left - box.left + area.scrollLeft}px`, width: `${column.width}px`, height: `${height}px` };
        fill();
    }

    // The card's height is what fits below wherever it is now, down to 16px above the bottom of the screen. It's only worked out when you stop
    // scrolling (and when the column moves), and eases to it quickly: changing it on every scroll made it chug. So while you scroll it keeps its height.
    function fill() {
        if (!aside || !scroller.value) return;
        const box = scroller.value.getBoundingClientRect();
        const cardTop = Math.max(box.top + 16, aside.getBoundingClientRect().top);
        fit.value = { maxHeight: `calc(100dvh - ${cardTop + 16}px)`, transition: 'max-height 120ms ease-out' };
    }

    function stop() {
        window.removeEventListener('resize', position);
        scroller.value?.removeEventListener('scrollend', fill);
        resizes?.disconnect();
        aside = resizes = null;
        scroller.value = place.value = null;
    }

    watch(showing, (isShowing) => {
        stop();
        if (!isShowing) {
            if (closeOpenCard === close) closeOpenCard = null;
            return;
        }
        switched.value = !!closeOpenCard && closeOpenCard !== close;
        if (switched.value) closeOpenCard();
        closeOpenCard = close;
        aside = sidebarOf(anchor.value);
        scroller.value = aside?.closest('[scroll-region]');
        if (!scroller.value) return;
        position();
        // The column moves when the window resizes or the nav or a panel opens, and the page gets longer or shorter as the form changes
        window.addEventListener('resize', position);
        scroller.value.addEventListener('scrollend', fill);
        resizes = new ResizeObserver(position);
        resizes.observe(aside);
        resizes.observe(scroller.value.firstElementChild ?? scroller.value);
    });

    onBeforeUnmount(() => {
        stop();
        if (closeOpenCard === close) closeOpenCard = null;
    });

    return { place, fit, scroller, switched };
}
