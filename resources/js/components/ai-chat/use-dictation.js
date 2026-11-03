import { reactive } from 'vue';

// Prototype: dictating a request into the AI chat. Nothing listens to the microphone: it plays out a scripted request, word by word, into
// the box as you'd see it with the browser's own speech recognition, with a made-up voice level for the equaliser to follow.

// What you "say" when you press the mic yourself
export const spokenRequest = 'Find all the entries that mention outdoor furniture';

export default function useDictation({ onWords, onDone }) {
    // phase: null, or listening. bands: a level for each of the equaliser's bars, each moving a little on its own.
    const state = reactive({ phase: null, heard: '', level: 0, bands: Array(3).fill(0) });

    let timers = [];
    let frame;
    const later = (fn, ms) => timers.push(setTimeout(fn, ms));
    const clear = () => {
        timers.forEach(clearTimeout);
        timers = [];
        cancelAnimationFrame(frame);
    };

    function start(text, { quietFor = 1600 } = {}) {
        clear();
        Object.assign(state, { phase: 'listening', heard: '', level: 0, bands: Array(3).fill(0) });

        // Each word is heard once it's been said. It takes longer for longer words, with a breath after the first few.
        const words = text.split(' ');
        const speaking = [];
        let at = 600;
        words.forEach((word, i) => {
            const length = 180 + word.length * 45 + Math.random() * 60;
            const from = at + 40 + Math.random() * 60 + (i === 4 ? 450 : 0);
            at = from + length;
            speaking.push([from, at]);
            later(() => {
                state.heard = words.slice(0, i + 1).join(' ');
                onWords(state.heard);
            }, at);
        });
        // It stops by itself after a moment of quiet
        later(stop, at + quietFor);

        const startedAt = performance.now();
        const tick = (now) => {
            const t = now - startedAt;
            const talking = speaking.some(([from, to]) => t >= from && t <= to);
            const target = talking ? 0.35 + Math.random() * 0.65 : 0.03 + Math.random() * 0.04;
            state.level += (target - state.level) * 0.3;
            state.bands = state.bands.map((band) => band + (state.level * (talking ? 0.4 + Math.random() * 0.6 : 1) - band) * 0.35);
            frame = requestAnimationFrame(tick);
        };
        frame = requestAnimationFrame(tick);
    }

    // Stop listening and keep what was heard
    function stop() {
        if (state.phase !== 'listening') return;
        clear();
        state.phase = null;
        state.level = 0;
        onDone(state.heard);
    }

    // Stop listening and throw it away
    function cancel() {
        clear();
        state.phase = null;
        state.level = 0;
    }

    return { state, start, stop, cancel };
}
