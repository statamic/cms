// Prototype: made-up results for the image skills other than Make variations (see mock-image-variations.js), as an AI image model would
// make them. Nothing goes to an AI. The cut-outs were made once on a Mac, with Apple's own subject lifting (Vision), and are kept on the
// statamic-cp demo site in public/ai-mock/cutouts. The rest are drawn in the browser from the image itself.
//
// Each result has an image to show (src), CSS for it (style), a short name (label), the filename it would be saved as, next to the original
// (file), and, when it isn't the original's shape, its shape (ratio). A cut-out is see-through (transparent). An upscale is compared before
// and after (compare).

const checkerboard = { backgroundColor: 'var(--checkerboard-light-color)', backgroundImage: 'var(--checkerboard-light)' };

const nameOf = (asset) => asset.basename.replace(/\.[^.]+$/, '');

// The demo site's photos that have a cut-out
const CUTOUTS = [
    'aj-robsin-buq1rzckyw4-unsplash',
    'david-heiling-qd5hssfgnom-unsplash',
    'hu-chen-3yd8oxgolqm-unsplash',
    'mylon-ollila-j4ocwyap_cs-unsplash',
    'sergi-ferrete-yxwt-vj3sza-unsplash',
    'wolfgang-hasselmann-ldav4eljbt0-unsplash',
    'david-clode-nyvr6wbu1ho-unsplash',
    'nam-anh-qjbyg6o0ick-unsplash',
];

// Remove background: the subject on a see-through background (saved as a PNG), and on white. An image without a cut-out gets a soft oval
// round its middle instead.
export function cutoutsOf(asset) {
    const name = nameOf(asset);
    const src = CUTOUTS.includes(name) ? `/ai-mock/cutouts/${name}.png` : asset.preview;
    const mask = CUTOUTS.includes(name) ? {} : { maskImage: 'radial-gradient(closest-side, black 70%, transparent)' };

    return [
        { src, style: { ...checkerboard, ...mask }, label: __('Transparent'), file: `${name}-cutout.png`, transparent: true },
        { src, style: { backgroundColor: 'white', ...mask }, label: __('On white'), file: `${name}-on-white.${asset.extension}` },
    ];
}

// Loads an image to draw with, at most 1600px wide, so drawing stays quick
function load(url) {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.onload = () => resolve(image);
        image.onerror = reject;
        image.src = url;
    });
}

function canvasFor(width, height) {
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(width);
    canvas.height = Math.round(height);
    return [canvas, canvas.getContext('2d')];
}

async function source(asset) {
    const image = await load(asset.url ?? asset.preview);
    const scale = Math.min(1, 1600 / image.naturalWidth);
    return { image, width: image.naturalWidth * scale, height: image.naturalHeight * scale };
}

// Extend: the image widened or heightened to three common shapes, with the AI filling in the new edges. Here the edges are the image
// itself, stretched and blurred, and the original fades into them.
export async function extensionsOf(asset) {
    const { image, width, height } = await source(asset);
    const shapes = [
        { label: __('Wide'), ratio: 16 / 9, suffix: 'wide' },
        { label: __('Square'), ratio: 1, suffix: 'square' },
        { label: __('Portrait'), ratio: 4 / 5, suffix: 'portrait' },
    ];

    return shapes.map(({ label, ratio, suffix }) => {
        const wider = ratio > width / height;
        const [w, h] = wider ? [height * ratio, height] : [width, width / ratio];
        const [canvas, context] = canvasFor(w, h);

        // The new edges: the image covering the whole shape, blurred
        const cover = Math.max(w / width, h / height);
        context.filter = 'blur(28px) saturate(1.1)';
        context.drawImage(image, (w - width * cover) / 2, (h - height * cover) / 2, width * cover, height * cover);
        context.filter = 'none';

        // The original in the middle, fading out over its last 6% into the new edges
        const [piece, pieceContext] = canvasFor(width, height);
        pieceContext.drawImage(image, 0, 0, width, height);
        pieceContext.globalCompositeOperation = 'destination-in';
        const fade = wider ? pieceContext.createLinearGradient(0, 0, width, 0) : pieceContext.createLinearGradient(0, 0, 0, height);
        fade.addColorStop(0, 'transparent');
        fade.addColorStop(0.06, 'black');
        fade.addColorStop(0.94, 'black');
        fade.addColorStop(1, 'transparent');
        pieceContext.fillStyle = fade;
        pieceContext.fillRect(0, 0, width, height);
        context.drawImage(piece, (w - width) / 2, (h - height) / 2);

        return { src: canvas.toDataURL('image/jpeg', 0.88), style: {}, label, file: `${nameOf(asset)}-${suffix}.${asset.extension}`, ratio: `${w} / ${h}` };
    });
}

// Upscale: twice and four times the size. They're shown as the original, compared with how it looked before: a close-up of its middle,
// soft on the left (the original, enlarged) and sharp on the right (the upscale).
export async function upscalesOf(asset) {
    const { image, width, height } = await source(asset);
    // "Before": the image at a quarter of the size, enlarged again, so it's soft
    const [small, smallContext] = canvasFor(width / 4, height / 4);
    smallContext.drawImage(image, 0, 0, width / 4, height / 4);
    const before = small.toDataURL('image/jpeg', 0.8);
    const size = (times) => `${(asset.width ?? width) * times} × ${(asset.height ?? height) * times}`;

    return [2, 4].map((times) => ({
        src: asset.url ?? asset.preview,
        style: {},
        label: __(':times× · :size', { times, size: size(times) }),
        file: `${nameOf(asset)}-${times}x.${asset.extension}`,
        compare: { before, after: asset.url ?? asset.preview },
    }));
}

// Erase something: the painted area (mask, a canvas the image's size, drawn on where to erase) filled in from around it, in two takes.
// Here the gap is filled with what's beside it: the strip of image just to its left (or right, if it's near the left edge), stretched across
// the gap in one take and mirrored across it in the other, blended in at the edges. Without a mask (it was described instead), it takes out
// the middle of the image's left half.
export async function erasuresOf(asset, mask = null) {
    const { image, width, height } = await source(asset);

    if (!mask) {
        [mask] = canvasFor(width, height);
        const maskContext = mask.getContext('2d');
        maskContext.fillStyle = 'black';
        maskContext.beginPath();
        maskContext.ellipse(width * 0.3, height * 0.6, width * 0.12, height * 0.2, 0, 0, Math.PI * 2);
        maskContext.fill();
    }

    // The mask at the image's size, and the painted area's left and right edges
    const [sized, sizedContext] = canvasFor(width, height);
    sizedContext.drawImage(mask, 0, 0, width, height);
    const pixels = sizedContext.getImageData(0, 0, sized.width, sized.height).data;
    let left = sized.width;
    let right = 0;
    for (let i = 3; i < pixels.length; i += 4) {
        if (!pixels[i]) continue;
        const x = ((i - 3) / 4) % sized.width;
        left = Math.min(left, x);
        right = Math.max(right, x);
    }
    const gap = Math.max(1, right - left);

    // The strip beside the gap, up to as wide as it, in the image's own pixels
    const scale = image.naturalWidth / sized.width;
    const fromLeft = left >= sized.width * 0.1;
    const stripWidth = Math.min(gap, fromLeft ? left : sized.width - right);
    const stripX = fromLeft ? left - stripWidth : right;

    return [false, true].map((mirrored, i) => {
        const [canvas, context] = canvasFor(width, height);
        context.drawImage(image, 0, 0, width, height);

        // The fill: the strip across the gap, kept only where it was painted, with a soft edge
        const [fill, fillContext] = canvasFor(width, height);
        if (mirrored) {
            fillContext.translate(left + gap, 0);
            fillContext.scale(-1, 1);
            fillContext.drawImage(image, stripX * scale, 0, stripWidth * scale, image.naturalHeight, 0, 0, gap, height);
            fillContext.setTransform(1, 0, 0, 1, 0, 0);
        } else {
            fillContext.drawImage(image, stripX * scale, 0, stripWidth * scale, image.naturalHeight, left, 0, gap, height);
        }
        fillContext.filter = 'blur(10px)';
        fillContext.globalCompositeOperation = 'destination-in';
        fillContext.drawImage(sized, 0, 0);
        context.drawImage(fill, 0, 0);

        return { src: canvas.toDataURL('image/jpeg', 0.88), style: {}, label: __('Take :number', { number: i + 1 }), file: `${nameOf(asset)}-erased-${i + 1}.${asset.extension}` };
    });
}

// A filename that says what's in the image, for Rename (see Field.vue): the demo site's photos have their own, and anything else keeps
// its name, tidied up
const FILENAMES = {
    'aj-robsin-buq1rzckyw4-unsplash': 'elephant-bull-in-golden-grass',
    'david-heiling-qd5hssfgnom-unsplash': 'elephant-and-calf-on-dry-plain',
    'hu-chen-3yd8oxgolqm-unsplash': 'elephant-and-calf-in-golden-grass',
    'mylon-ollila-j4ocwyap_cs-unsplash': 'two-elephants-at-pink-sunset',
    'sergi-ferrete-yxwt-vj3sza-unsplash': 'elephant-herd-crossing-savannah',
    'wolfgang-hasselmann-ldav4eljbt0-unsplash': 'elephant-drinking-at-watering-hole',
    'david-clode-nyvr6wbu1ho-unsplash': 'elephant-close-up',
    'nam-anh-qjbyg6o0ick-unsplash': 'elephant-head-on',
};

export function filenameFor(asset) {
    const name = nameOf(asset);
    return FILENAMES[name] ?? name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}
