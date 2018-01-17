<template>
    <div class="bard-fieldtype-wrapper replicator" :class="{'bard-fullscreen': fullScreenMode, 'no-sets': !hasSets }">

        <div class="bard-blocks" v-if="isReady" v-el:blocks>
            <component
                :is="block.type === 'text' ? 'BardText' : 'BardSet'"
                v-for="(index, block) in data"
                v-ref:set
                :class="{ 'divider-at-start': canShowDividerAtStart(index), 'divider-at-end': canShowDividerAtEnd(index) }"
                :key="index"
                :data="block"
                :index="index"
                :parent-name="name"
                :config="setConfig(block.type)"
                :show-source="showSource"
                @set-inserted="setInserted"
                @deleted="deleteSet"
                @source-toggled="toggleSource"
                @deleted-at-end="deleteNextSet"
                @backspaced-at-start="deletePreviousSet"
                @arrow-up-at-start="goToPreviousTextField"
                @arrow-down-at-end="goToNextTextField"
                @text-updated="updateText"
            >
                <template slot="divider-start">
                    <div v-show="canShowDividerAtStart(index)" class="bard-divider bard-divider-start" @click="addTextBlock(index-1)"></div>
                </template>
                <template slot="divider-end">
                    <div v-show="canShowDividerAtEnd(index)" class="bard-divider bard-divider-end" @click="addTextBlock(index)"></div>
                </template>
                <template slot="expand-collapse">
                    <li><a @click="collapseAll">{{ translate('cp.collapse_all') }}</a></li>
                    <li><a @click="expandAll">{{ translate('cp.expand_all') }}</a></li>
                </template>
            </component>
        </div>

        <div class="bard-field-title" v-text="config.display"></div>

        <div class="bard-field-options no-select">
            <a @click="toggleSource" :class="{ active: showSource }" v-if="allowSource"><i class="icon icon-code"></i></a>
            <a @click="toggleFullscreen"><i class="icon" :class="{ 'icon-resize-full-screen' : ! fullScreenMode, 'icon-resize-100' : fullScreenMode }"></i></a>
        </div>
    </div>
</template>

<script>
import Replicator from '../replicator/Replicator';
import { Draggable } from '@shopify/draggable';

export default {

    mixins: [Replicator, Fieldtype],

    components: {
        BardSet: require('./BardSet.vue'),
        BardText: require('./BardText.vue')
    },

    computed: {

        textBlocks() {
            return this.$refs.set.filter(set => set.data.type === 'text');
        },

        allowSource() {
            if (this.config.markdown) return false;

            return this.config.allow_source === undefined ? true : this.config.allow_source
        }

    },

    data: function() {
        return {
            isReady: false,
            setBeingDragged: null,
            lastDraggedOverElement: null,
            hasSets: this.config.sets !== undefined,
            showSource: false,
            fullScreenMode: false,
            autoBindChangeWatcher: false,
            changeWatcherWatchDeep: false,
            previousScrollPosition: null
        };
    },

    ready() {
        if (! this.data) {
            this.data = [{type: 'text', text: '<p><br></p>'}];
        }

        this.combineConsecutiveTextBlocks();

        this.isReady = true;

        this.$nextTick(() => {
            this.draggable();
            if (this.accordionMode) this.collapseAll();
            this.bindChangeWatcher();
        });
    },

    watch: {

        data(data) {
            if (data.length === 0) {
                this.data = [{type: 'text', text: '<p><br></p>'}];
                this.$nextTick(() => this.getBlock(0).focus());
            }
        }

    },

    methods: {

        addTextBlock(index, text) {
            text = text || '<p><br></p>';
            index = index + 1;
            this.data.splice(index, 0, { type: 'text', text });
            this.$nextTick(() => {
                const block = this.getBlock(index);
                if (text) {
                    block.focusAt(0);
                } else {
                    block.focus();
                }
            });
        },

        addBlock: function(type, index) {
            var newSet = { type: type };

            // Get nulls for all the set's fields so Vue can track them more reliably.
            var set = this.setConfig(type);
            _.each(set.fields, function(field) {
                newSet[field.name] = field.default || Statamic.fieldtypeDefaults[field.type] || null;
            });

            if (index === undefined) {
                index = this.data.length;
            }

            this.data.splice(index, 0, newSet);

            this.$nextTick(() => this.getBlock(index).focus());
        },

        setSelected(type, index) {
            var newSet = { type: type };

            // Get nulls for all the set's fields so Vue can track them more reliably.
            var set = this.setConfig(type);
            _.each(set.fields, function(field) {
                newSet[field.name] = field.default || Statamic.fieldtypeDefaults[field.type] || null;
            });

            this.data.splice(index, 1, newSet);

            this.$nextTick(() => this.getBlock(index).focus());
        },

        setInserted(type, index, before, after) {
            const newSet = this.getBlankSet(type);
            const beforeSet = { type: 'text', text: before };
            const afterSet = { type: 'text', text: after };

            let newItems = [beforeSet, newSet, afterSet].filter(set => {
                if (set.type !== 'text') return true;
                return set.text !== '';
            });

            this.data.splice(index, 1, ...newItems);
        },

        getBlankSet(type) {
            let newSet = { type: type };

            // Get nulls for all the set's fields so Vue can track them more reliably.
            var set = this.setConfig(type);
            _.each(set.fields, function(field) {
                newSet[field.name] = field.default || Statamic.fieldtypeDefaults[field.type] || null;
            });

            return newSet;
        },

        getBlock(index) {
            return this.$refs.set[index];
        },

        /**
         * Whether a divider / insertion point can be displayed before a given block.
         * We don't want the UI to get clogged with multiple empty blocks.
         */
        canShowDividerAtStart(index) {
            return index === 0;
        },

        /**
         * Whether a divider / insertion point can be displayed after a given block.
         * We don't want the UI to get clogged with multiple empty blocks.
         */
        canShowDividerAtEnd(index) {
            if (index === this.data.length - 1) {
                return true;
            }

            return this.getBlock(index + 1).data.type !== 'text';
        },

        draggable() {
            const draggable = new Draggable(this.$els.blocks, {
                draggable: '.bard-block',
                handle: '.drag-handle',
                mirror: {
                    xAxis: false,
                    constrainDimensions: true
                },
                delay: 200
            });

            draggable.on('drag:start', (e, a) => {
                let doc = document.documentElement;
                this.previousScrollPosition = (window.pageYOffset || doc.scrollTop)  - (doc.clientTop || 0);

                this.setBeingDragged = e.originalSource.__vue__.index;
                this.textBlocks.forEach(block => block.addDropAreas());
            });

            draggable.on('drag:move', (e) => {
                const target = e.originalEvent.target;

                if (target.classList.contains('bard-drop-area-inner') || target.classList.contains('bard-divider')) {
                    this.lastDraggedOverElement = target;
                }
            });

            draggable.on('drag:stop', (e) => {
                // Prevent the div from actually being moved. Vue will do that for us.
                e.cancel();

                if (this.lastDraggedOverElement) {
                    this.moveSetToNewLocation();
                } else {
                    this.removeDropAreas();
                }

                this.$nextTick(() => {
                    window.scrollTo(0, this.previousScrollPosition);
                    this.previousScrollPosition = null;
                });
            });
        },

        moveSetToNewLocation() {
            // Get the block this was dragged over.
            // There's obviously a better way to do this. Or is there?
            let block;
            if (this.lastDraggedOverElement.classList.contains('bard-divider')) {
                block = this.lastDraggedOverElement.parentNode.__vue__;
            } else {
                block = this.lastDraggedOverElement // .bard-drop-area-inner
                    .parentNode // .bard-drop-area
                    .parentNode // .bard-editor
                    .parentNode // .bard-block
                    .__vue__;
            }

            this.removeDropAreas();

            if (! block) return;

            this.moveSet(block);

            this.lastDraggedOverElement = null;
        },

        moveSet(block) {
            if (block.data.type === 'text') {
                return this.moveSetIntoText(block);
            }

            const start = this.setBeingDragged;
            let end = block.index + (start > block.index ? 1 : 0);

            // The only place a start divider exists is right at the beginning. In this case, we
            // want to move the set to the beginning of everything, instead of *after* some other set.
            if (this.lastDraggedOverElement.classList.contains('bard-divider-start')) {
                end = 0;
            }

            this.data.splice(end, 0, this.data.splice(start, 1)[0]);

            this.combineConsecutiveTextBlocks();
        },

        moveSetIntoText(block) {
            block.insertParagraph();

            const [before, after] = block.getBeforeAndAfterHtml();
            const beforeSet = { type: 'text', text: before };
            const afterSet = { type: 'text', text: after };
            const set = this.data[this.setBeingDragged];

            this.data.splice(this.setBeingDragged, 1);

            let newItems = [beforeSet, set, afterSet].filter(set => {
                if (set.type !== 'text') return true;
                return set.text !== '';
            });

            const index = this.getInsertIndex(this.setBeingDragged, block.index);
            this.data.splice(index, 1, ...newItems);

            this.setBeingDragged = null;

            this.combineConsecutiveTextBlocks();
        },

        removeDropAreas() {
            this.textBlocks.forEach(block => block.removeDropAreas());
        },

        getInsertIndex(from, to) {
            if (from === 0) return 0;

            if (from < to) return to - 1;

            return to;
        },

        combineConsecutiveTextBlocks() {
            let data = [];
            let previousBlockWasText = false

            this.data.forEach((block, i) => {
                if (block.type !== 'text') {
                    data.push(block)
                    previousBlockWasText = false;
                    return;
                }

                if (! previousBlockWasText) {
                    data.push(block);
                    previousBlockWasText = true;
                    return;
                }

                data[data.length-1].text += block.text;
            });

            this.data = data;
        },

        toggleSource() {
            this.showSource = !this.showSource;
        },

        toggleFullscreen() {
            this.fullScreenMode = !this.fullScreenMode;
            this.$root.hideOverflow = ! this.$root.hideOverflow;
        },

        deleteSet(index) {
            const block = this.getBlock(index - 1);
            const focus = (block && block.data.type === 'text') ? block.plainText().length : null;

            this.data.splice(index, 1);
            this.combineConsecutiveTextBlocks();

            if (block) {
                this.$nextTick(() => this.getBlock(index - 1).focusAt(focus));
            }
        },

        deleteNextSet(index) {
            if (index === this.data.length-1) return;

            this.getBlock(index + 1).delete();
        },

        deletePreviousSet(index) {
            if (index === 0) return;

            this.getBlock(index - 1).delete();
        },

        goToPreviousTextField(index) {
            if (index === 0) return;

            while (index > 0) {
                index--;
                const block = this.getBlock(index);
                if (block.data.type === 'text') {
                    setTimeout(() => { block.focusAt('end') }, 10);
                    return;
                }
            }
        },

        goToNextTextField(index) {
            const totalBlocks = this.$refs.set.length - 1;

            if (index === totalBlocks) return;

            while (index < totalBlocks) {
                index++;
                const block = this.getBlock(index);
                if (block.data.type === 'text') {
                    setTimeout(() => { block.focusAt('start') }, 10);
                    return;
                }
            }
        },

        updateText(i, text) {
            this.data[i].text = text;
        },

        getReplicatorPreviewText() {
            return _.map(this.$refs.set, (set) => {
                return (set.data.type === 'text') ? set.plainText() : set.getCollapsedPreview();
            }).join(', ');
        },
    }
};
</script>
