// Prototype: made-up variations of an image, as an AI image model would make them. Nothing is generated: the statamic-cp demo site has a
// set of elephant photos (in the Assets container, mostly in carousel/), so asking for more like one of them gives four of the others.
// Any other image gets four takes on itself, done with CSS (closer, mirrored, warmer, cooler).
const ELEPHANTS = [
    { file: 'aj-robsin-buq1rzckyw4-unsplash.jpg', url: '/assets/carousel/aj-robsin-buq1rzckyw4-unsplash.jpg', label: 'Lone bull' },
    { file: 'david-heiling-qd5hssfgnom-unsplash.jpg', url: '/assets/carousel/david-heiling-qd5hssfgnom-unsplash.jpg', label: 'With calf' },
    { file: 'mylon-ollila-j4ocwyap_cs-unsplash.jpg', url: '/assets/carousel/mylon-ollila-j4ocwyap_cs-unsplash.jpg', label: 'At sunset' },
    { file: 'wolfgang-hasselmann-ldav4eljbt0-unsplash.jpg', url: '/assets/carousel/wolfgang-hasselmann-ldav4eljbt0-unsplash.jpg', label: 'Watering hole' },
    { file: 'hu-chen-3yd8oxgolqm-unsplash.jpg', url: '/assets/carousel/hu-chen-3yd8oxgolqm-unsplash.jpg', label: 'Golden hour' },
    { file: 'sergi-ferrete-yxwt-vj3sza-unsplash.jpg', url: '/assets/carousel/sergi-ferrete-yxwt-vj3sza-unsplash.jpg', label: 'Herd' },
    { file: 'david-clode-nyvr6wbu1ho-unsplash.jpg', url: '/assets/david-clode-nyvr6wbu1ho-unsplash.jpg', label: 'Close up' },
    { file: 'nam-anh-qjbyg6o0ick-unsplash.jpg', url: '/assets/nam-anh-qjbyg6o0ick-unsplash.jpg', label: 'Head on' },
];

const TAKES = [
    { label: 'Closer', style: { transform: 'scale(1.35)' } },
    { label: 'Mirrored', style: { transform: 'scaleX(-1)' } },
    { label: 'Warmer', style: { filter: 'sepia(0.35) saturate(1.3)' } },
    { label: 'Cooler', style: { filter: 'hue-rotate(-20deg) saturate(0.85) brightness(1.05)' } },
];

// Four variations of the asset: each with an image to show (src), CSS for it (style), a short name (label), and the filename it would be
// kept as, next to the original (file)
export function variationsOf(asset) {
    const [name, extension] = [asset.basename.replace(/\.[^.]+$/, ''), asset.extension];
    const fileFor = (i) => `${name}-${i + 1}.${extension}`;
    const self = ELEPHANTS.findIndex(({ file }) => file === asset.basename);

    if (self !== -1) {
        return [...ELEPHANTS.slice(self + 1), ...ELEPHANTS.slice(0, self)]
            .slice(0, 4)
            .map(({ url, label }, i) => ({ src: url, style: {}, label, file: fileFor(i) }));
    }

    return TAKES.map(({ label, style }, i) => ({ src: asset.preview, style, label, file: fileFor(i) }));
}
