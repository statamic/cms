import { inject, nextTick, onBeforeUnmount, ref, watch } from 'vue';

// Prototype: lays a card over the entry's sidebar column (the one with Published and the slug), for suggestions too big for a dropdown
// (see AiSuggestLarge.vue), level with the field it's for. The card scrolls with the page natively, so it never lags behind it. A wrapper is
// put in the page's scrolling area beside the field, and the card sticks to its top while the field is in view (see position). Its height
// fits the screen (see fill).
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

// The publish form hands down its sidebar column (see Tabs.vue). Without one (a narrow window, a blueprint with no sidebar, or a form
// that isn't in one), or without a scrolling area the card can be placed in, place stays null and the card opens under the field instead.

// The nearest scrolling area above the column: the page's, or a stack's. The card is placed in it, so it needs to be positioned.
function scrollerOf(element) {
    for (let parent = element?.parentElement; parent; parent = parent.parentElement) {
        const css = getComputedStyle(parent);
        if (/(auto|scroll)/.test(css.overflowY)) return css.position === 'static' ? null : parent;
    }
    return null;
}

// Fitting the card's height waits for the scroll to end. Browsers without scrollend get the same a moment after the last scroll.
const hasScrollEnd = typeof window !== 'undefined' && 'onscrollend' in window;

// While the card is open, the sidebar's panels near it (beside it, or within 24px above or below it) fade, so they don't crowd its edges.
// It chains: a panel within 40px of one that's fading fades too, so a run of panels fades together rather than stopping half way. Panels
// further away stay as they are.
const NEAR = 24;
const CHAIN = 40;

// The panels fade more the nearer they are to the card: the nearest to 30%, the next along the chain to 40%, and any further to 50%
const FADES = [0.3, 0.4, 0.5];

// showing: whether the card is open. anchor: an element in the field, to find the field from. close: closes this card. card: an element in
// the card, to see which of the sidebar's panels it's near.
// Returns place (the wrapper's style, null when it opens under the field), fit (the card's), scroller (where to teleport the wrapper) and
// switched (whether it took over from another field's card).
export default function useSidebarCard(showing, anchor, close, card = null) {
    const place = ref(null);
    const switched = ref(false);
    const fit = ref(null);
    const scroller = ref(null);
    const sidebar = inject('publishSidebar', null);
    let aside = null;
    let resizes = null;
    let settle = null;
    let faded = [];

    function onScroll() {
        clearTimeout(settle);
        settle = setTimeout(fill, 120);
    }

    // Where the wrapper starts, in the scrolling area, for fill
    let wrapperTop = 0;

    // The wrapper sits beside the field, from its top (or the column's, if the field is above it) to its bottom. The card sticks inside it,
    // so beside a tall field (like Bard) it stays in view while the field does, and once you scroll past the field it goes with it, rather
    // than following you down the page. Beside a field shorter than the card, it just sits level with the field's top.
    function position() {
        if (!aside || !scroller.value) return;
        const area = scroller.value;
        const box = area.getBoundingClientRect();
        const column = aside.getBoundingClientRect();
        const field = anchor.value?.parentElement?.getBoundingClientRect();
        // The page's content card, which runs to the end of what scrolls (for when there's no field to go by)
        const page = (area.firstElementChild ?? aside).getBoundingClientRect();
        const columnTop = column.top - box.top + area.scrollTop;
        const top = field ? Math.max(columnTop, field.top - box.top + area.scrollTop) : columnTop;
        const bottom = field ? field.bottom - box.top + area.scrollTop : Math.max(page.bottom, column.bottom) - box.top + area.scrollTop;
        const height = Math.max(0, bottom - top);
        wrapperTop = top;
        place.value = { top: `${top}px`, left: `${column.left - box.left + area.scrollLeft}px`, width: `${column.width}px`, height: `${height}px` };
        fill();
        nextTick(() => requestAnimationFrame(fadeNearby));
    }

    // The card's height is what fits below wherever it is now, down to 16px above the bottom of the screen. It's only worked out when you stop
    // scrolling (and when the column moves), and eases to it quickly: changing it on every scroll made it chug. So while you scroll it keeps its height.
    function fill() {
        if (!aside || !scroller.value) return;
        const box = scroller.value.getBoundingClientRect();
        const cardTop = Math.max(box.top + 16, box.top + wrapperTop - scroller.value.scrollTop);
        fit.value = { maxHeight: `calc(100dvh - ${cardTop + 16}px)`, transition: 'max-height 120ms ease-out' };
        // A card sticking beside a tall field has moved along the sidebar as you scrolled
        requestAnimationFrame(fadeNearby);
    }

    function stop() {
        faded.forEach(restore);
        faded = [];
        window.removeEventListener('resize', position);
        scroller.value?.removeEventListener('scrollend', fill);
        scroller.value?.removeEventListener('scroll', onScroll);
        clearTimeout(settle);
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
        start();
    });

    function start() {
        aside = sidebar?.value ?? null;
        scroller.value = scrollerOf(aside);
        if (!aside || !scroller.value) return (aside = scroller.value = null);
        position();
        // The column moves when the window resizes or the nav or a panel opens, and the page gets longer or shorter as the form changes
        window.addEventListener('resize', position);
        if (hasScrollEnd) scroller.value.addEventListener('scrollend', fill);
        else scroller.value.addEventListener('scroll', onScroll, { passive: true });
        resizes = new ResizeObserver(position);
        resizes.observe(aside);
        resizes.observe(scroller.value.firstElementChild ?? scroller.value);
        // The card grows as its suggestions come in
        nextTick(() => {
            const cardEl = card?.value?.$el ?? card?.value;
            if (cardEl && resizes) resizes.observe(cardEl);
        });
    }

    function restore(panel) {
        panel.style.opacity = '';
    }

    // Fades the sidebar's panels near the card, and brings back any it's moved away from
    function fadeNearby() {
        const cardEl = card?.value?.$el ?? card?.value;
        if (!aside || !cardEl?.getBoundingClientRect) return;
        const near = cardEl.closest('.rounded-xl')?.getBoundingClientRect() ?? cardEl.getBoundingClientRect();
        const panels = [...aside.querySelectorAll('[class*="@container/panel"]')];
        const boxes = (panels.length ? panels : [...aside.children]).map((panel) => ({ panel, box: panel.getBoundingClientRect() }));
        const within = (a, b, gap) => a.bottom > b.top - gap && a.top < b.bottom + gap;
        // The ones near the card, then any near those, and so on
        // with how many steps along the chain each is from the card
        const chained = boxes.filter(({ box }) => within(box, near, NEAR)).map((entry) => ({ ...entry, step: 0 }));
        for (let i = 0; i < chained.length; i++) {
            boxes.forEach(
                (other) =>
                    !chained.some(({ panel }) => panel === other.panel) &&
                    within(other.box, chained[i].box, CHAIN) &&
                    chained.push({ ...other, step: chained[i].step + 1 }),
            );
        }
        const close = chained.map(({ panel }) => panel);
        faded.filter((panel) => !close.includes(panel)).forEach(restore);
        const steps = new Map(chained.map(({ panel, step }) => [panel, step]));
        close.forEach((panel) => {
            panel.style.transition = 'opacity 150ms ease-out';
            panel.style.opacity = String(FADES[Math.min(steps.get(panel) ?? 0, 2)]);
        });
        faded = close;
    }


    // The sidebar comes and goes as the window crosses 920px wide, so an open card moves over it, or under the field, to match
    watch(
        () => sidebar?.value,
        () => {
            if (!showing.value) return;
            stop();
            start();
        },
    );

    onBeforeUnmount(() => {
        stop();
        if (closeOpenCard === close) closeOpenCard = null;
    });

    return { place, fit, scroller, switched };
}
