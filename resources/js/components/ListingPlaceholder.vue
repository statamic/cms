<template>

    <div class="card">
        <h3 class="mb-3">Temporary Listing Placeholder</h3>
        <div v-for="item in data" class="mt-1 pt-1 border-t">
            <a :href="item.edit_url || item.url">{{ item.title }}</a>
        </div>
        <pre class="mt-4 whitespace-pre-wrap text-sm bg-grey-lightest">{{ json }}</pre>
    </div>

</template>


<script>
import axios from 'axios';

export default {

    props: ['url'],

    data() {
        return {
            data: null
        }
    },

    computed: {

        json() {
            return JSON.stringify(this.data, null, 2);
        }

    },

    mounted() {
        axios.get(this.url).then(response => this.data = response.data);
    }

}
</script>
