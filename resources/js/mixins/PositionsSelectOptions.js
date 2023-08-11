import { computePosition, offset, flip } from '@floating-ui/dom';

export default {

    methods: {

        positionOptions(dropdownList, component, { width }) {
            dropdownList.style.width = width

            computePosition(component.$refs.toggle, dropdownList, {
                placement: 'bottom',
                middleware: [
                    offset({ mainAxis: 0, crossAxis: -1 }),
                    flip(),
                ]
            }).then(({ x, y }) => {
                Object.assign(dropdownList.style, {
                    // Round to avoid blurry text
                    left: `${Math.round(x)}px`,
                    top: `${Math.round(y)}px`,
                });
            });
        }

    }

}
