<template>

    <div>
        <portal to="live-preview-fields" :disabled="!previewing">
            <provider :variables="provides">
                <slot name="default" />
            </provider>
        </portal>

        <portal v-if="previewing" to="live-preview">
            <div class="live-preview fixed flex flex-col">

                <div class="live-preview-header relative bg-grey-lighter border-b px-3 py-2 shadow flex items-center justify-between">
                    <div class="text-lg font-medium mr-2">Live Preview</div>
                    <div>
                        <button class="font-bold mx-1">Desktop</button>
                        <button class="text-blue mx-1">Mobile</button>
                    </div>
                    <button class="text-grey" @click="$emit('closed')">&times;</button>
                </div>

                <div class="live-preview-main flex flex-1">

                    <div class="live-preview-editor relative bg-white shadow-lg h-full" :style="{ width: '350px' }">
                        <div class="live-preview-fields flex-1 h-full overflow-scroll">
                            <portal-target name="live-preview-fields" />
                        </div>

                        <div class="live-preview-resizer h-full absolute pin-t" />
                    </div>

                    <div class="live-preview-contents relative bg-white flex-1 flex flex-col">
                        <iframe ref="iframe" frameborder="0" class="w-full h-full" />
                    </div>

                </div>

            </div>
        </portal>

    </div>

</template>

<script>
import axios from 'axios';
import Provider from './Provider.vue';

export default {

    components: {
        Provider
    },

    props: {
        url: String,
        previewing: Boolean,
        values: Object,
        name: String,
    },

    data() {
        return {
            provides: {
                storeName: this.name
            }
        }
    },

    watch: {

        previewing(enabled) {
            if (enabled) this.update();

            let state = this.$store.state.statamic.livePreview;
            state.enabled = enabled;
            this.$store.commit('statamic/livePreview', state);
        },

        values: {
            deep: true,
            handler(values) {
                if (this.previewing) this.update();
            }
        }

    },

    methods: {

        update() {
            const payload = {
                preview: this.values
            };

            axios.post(this.url, payload).then(response => {
                this.updateIframeContents(response.data);
            })
        },

        updateIframeContents(contents) {
            const iframe = this.$refs.iframe;
            const scrollX = $(iframe.contentWindow.document).scrollLeft();
            const scrollY = $(iframe.contentWindow.document).scrollTop();

            contents += '<script type="text/javascript">window.scrollTo('+scrollX+', '+scrollY+');\x3c/script>';

            iframe.contentWindow.document.open();
            iframe.contentWindow.document.write(contents);
            iframe.contentWindow.document.close();
        }

    }

}
</script>
