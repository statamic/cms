export default function shortcutLabel(binding) {
    const platform = typeof navigator !== 'undefined'
        ? (navigator.userAgentData?.platform || navigator.platform || '')
        : '';
    const mod = /Mac|iPhone|iPad|iPod/i.test(platform) ? 'Cmd' : 'Ctrl';

    return binding
        .split('+')
        .map((key) => (key === 'mod' ? mod : key.charAt(0).toUpperCase() + key.slice(1)))
        .join('+');
}
