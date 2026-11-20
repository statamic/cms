// Prototype: made-up alt text, as an AI vision model would write it. The statamic-cp demo site's elephant photos each have their own;
// any other image gets one made from its filename.
const ALT = {
    'aj-robsin-buq1rzckyw4-unsplash.jpg': 'A lone elephant bull walking through tall golden grass',
    'david-heiling-qd5hssfgnom-unsplash.jpg': 'An elephant and her calf standing close together on a dry plain',
    'mylon-ollila-j4ocwyap_cs-unsplash.jpg': 'Two elephants on green grassland under a pink sunset sky',
    'wolfgang-hasselmann-ldav4eljbt0-unsplash.jpg': 'An elephant drinking at a watering hole',
    'hu-chen-3yd8oxgolqm-unsplash.jpg': 'An elephant and her calf in tall golden grass',
    'sergi-ferrete-yxwt-vj3sza-unsplash.jpg': 'A herd of elephants walking in a line across the savannah',
};

export function altFor(asset) {
    return ALT[asset.basename] ?? `${asset.basename.replace(/\.[^.]+$/, '').replace(/[-_]+/g, ' ')}`;
}

// The AI "thinks" for a moment before the alt text comes back
export const altDelay = 900;
