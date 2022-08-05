import { data_get } from '../../bootstrap/globals';

export default function resolvePath(path, values) {
    let replaced = path.replace(/(.*?)\.({.*?})/, function (chunk, parent, placeholder) {
        return chunk.replace(/{(\w+):(.*)}/, function (placeholder, fieldtype, key) {
            let val = data_get(values, parent);

            if (fieldtype === 'bard') {
                return val.findIndex(item => item.type == 'set' && item.attrs.id === key) + '.attrs.values';
            }

            return val.findIndex(item => item._id === key);
        });
    });

    if (replaced.includes('{')) {
        replaced = resolvePath(replaced, values);
    }

    return replaced;
}
