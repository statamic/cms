export default function useGlobalEventBus(): {
    $on: (...args: any[]) => any;
    $once: (...args: any[]) => any;
    $off: (...args: any[]) => any;
    $emit: (...args: any[]) => any;
};
