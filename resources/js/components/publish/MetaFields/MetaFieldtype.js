export default {

    props: ['config'],

    computed: {

        formData() {
            return this.$parent.$parent.$parent.$parent.formData; // could really do with vue2+vuex right about now.
        }

    }

}
