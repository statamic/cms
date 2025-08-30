import { existsSync, readFileSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

export default function() {
    return {
        name: 'statamic-tailwind-exclusions',

        load(id) {
            if (!id.endsWith('.css') || !existsSync(id)) {
                return null;
            }

            const css = readFileSync(id, 'utf8');

            return css.replace('@source not statamic;', readTailwindExclusions());
        }
    };
}

function readTailwindExclusions() {
    const __dirname = dirname(fileURLToPath(import.meta.url));
    const path = join(__dirname, 'tailwind-exclusions.css');

    if (!existsSync(path)) {
        console.log(`\x1b[33m[Statamic] Tailwind exclusions file not found: ${path}\x1b[0m`);
        return '';
    }

    return readFileSync(path, 'utf8');
}
