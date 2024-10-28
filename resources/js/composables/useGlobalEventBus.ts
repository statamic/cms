import emitter from 'tiny-emitter/instance';

const eventBus = emitter

export default function useGlobalEventBus() {
    return {
        $on: (...args) => eventBus.on(...args),
        $once: (...args) => eventBus.once(...args),
        $off: (...args) => eventBus.off(...args),
        $emit: (...args) => eventBus.emit(...args)
    }
}