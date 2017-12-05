module.exports = {
    props: ['entry', 'type', 'primary'],

    template: '' +
        '<a :href="entry.publish_url" v-if="primary === type">{{ entry[type] }}</a>' +
        '<template v-if="primary !== type">{{ entry[type] }}</template>'

};