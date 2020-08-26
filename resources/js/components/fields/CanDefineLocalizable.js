export default {

    props: {
        canDefineLocalizable: {
            type: Boolean,
            default: () => {
                return Statamic.$config.get('sites').length > 1;
            }
        }
    }

}
