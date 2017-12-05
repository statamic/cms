<template>
<div class="grid-field grid-mode-{{ (stacked) ? 'stacked' : 'table' }}">
	<table v-if="hasData && !stacked" class="grid-table bordered-table">
		<thead>
			<tr>
				<th v-for="field in config.fields" :style="{ width: gridColWidth(field.width) }">
					<div class="flexy">
						<label class="block fill">
							<template v-if="field.display">{{ field.display }}</template>
							<template v-if="!field.display">{{ field.name | capitalize }}</template>
							<i class="required" v-if="field.required">*</i>
						</label>
						<i class="icon icon-help-with-circle o5 fs-12" v-if="field.instructions" v-tip :tip-text="field.instructions | markdown"></i>
					</div>
				</th>
                <th class="row-controls"></th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="(rowIndex, row) in data" :class="{excess: isExcessive(rowIndex)}">
				<td v-for="field in config.fields">
					<div class="{{ field.type }}-fieldtype">
						<component :is="field.type + '-fieldtype'"
						           :name="name + '.' + rowIndex + '.' + field.name"
						           :data.sync="row[field.name]"
						           :config="field">
						</component>
					</div>
				</td>
                <td class="row-controls">
                    <span class="icon icon-menu move drag-handle"></span>
                    <span class="icon icon-cross delete" v-on:click="deleteRow(rowIndex)"></span>
                </td>
			</tr>
		</tbody>
	</table>

	<div v-if="hasData && stacked" class="grid-stacked">
		<div class="list-group" v-for="(rowIndex, row) in data">
			<div class="list-group-item group-header drag-handle">
				<div class="flexy">
					<label class="fill">{{ rowIndex + 1 }}</label>
					<i class="icon icon-cross" v-on:click="deleteRow(rowIndex)"></i>
				</div>
			</div>
			<div class="list-group-item">
				<div class="row">
					<div v-for="field in config.fields" class="{{ colClass(field.width )}}">
						<div class="form-group {{ field.type }}-fieldtype">
							<label class="block">
								<template v-if="field.display">{{ field.display }}</template>
								<template v-if="!field.display">{{ field.name | capitalize }}</template>
								<i class="required" v-if="field.required">*</i>
							</label>

							<small class="help-block" v-if="field.instructions" v-html="field.instructions | markdown"></small>

							<component :is="field.type + '-fieldtype'"
							           :name="name + '.' + rowIndex + '.' + field.name"
							           :data.sync="row[field.name]"
							           :config="field">
							</component>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<template v-if="canAddRows">
		<button type="button" class="btn btn-default add-row" @click="addRow" v-el:add-row-button>
			{{ addRowButton }} <i class="icon icon-plus icon-right"></i>
		</button>
	</template>
</div>

</template>

<script>
var Vue = require('vue');

module.exports = {

    mixins: [Fieldtype],

    data: function() {
        return {
            blank: {},
            sortableOptions: {},
            min_rows: this.config.min_rows || 0,
            max_rows: this.config.max_rows || false,
            autoBindChangeWatcher: false,
            changeWatcherWatchDeep: false
        };
    },

    computed: {
        stacked: function() {
            return this.config.mode === 'stacked' || this.$root.isPreviewing;
        },

        hasData: function() {
            return this.data && this.data.length;
        },

        isNested: function() {
             return this.$parent.$options.name === 'grid-fieldtype';
        },

        canAddRows: function() {
            if (this.max_rows && this.data) {
                return (this.data.length < this.max_rows);
            }

            return true;
        },

        addRowButton: function() {
            return this.config.add_row || translate_choice('cp.rows', 1);
        }
    },

    ready: function() {
        // Initialize with an empty array if there's no data.
        if (! this.data) {
            this.data = [];
        }

        // Prepare the blank row
        this.prepareBlankRow();

        // Add minumum number of rows
        if (this.min_rows) {
            var rows_to_add = this.min_rows - this.data.length;
            for (var i = 1; i <= rows_to_add; i++) this.addRow();
        }


        this.initSortable();
        this.bindChangeWatcher();

        // Re-initialize sortable when the stacking mode changes
        // For instance, when toggling sneak peek.
        this.$watch('stacked', function() {
            this.initSortable();
        });
    },

    methods: {
        prepareBlankRow: function() {
            var blank = {};
            var fields = JSON.parse(JSON.stringify(this.config.fields));

            _.each(fields, function(field) {
                blank[field.name] = field.default || Statamic.fieldtypeDefaults[field.type] || null;
            });

            this.blank = blank;
        },

        addRow: function() {
            // We need to clone is so we don't end up modifying by reference.
            var blank = _.clone(this.blank);

            this.data.push(blank);

            this.$nextTick(function() {
                this.getSortable().sortable(this.getSortableOptions());

                // Focus the first field in the last row.
                const child = this.$children.length - this.$children.length / this.data.length;
                this.$children[child].focus();
            });
        },

        deleteRow: function(index) {
            var self = this;

            swal({
                type: 'warning',
                title: translate('cp.are_you_sure'),
                confirmButtonText: translate('cp.yes_im_sure'),
                cancelButtonText: translate('cp.cancel'),
                showCancelButton: true
            }, function() {
                self.data.splice(index, 1);
            });
        },

        isExcessive: function(index) {
            if (this.max_rows) {
                return (index + 1) > this.max_rows;
            }

            return false;
        },

        initSortable: function() {
            this.getSortable().sortable(this.getSortableOptions());
        },

        getSortable: function() {
            return (this.stacked)
                ? $(this.$el).find('.grid-stacked')
                : $(this.$el).find('tbody');
        },

        getSortableOptions: function() {
            var self = this;
            var start = '';

            if (this.stacked) {
                var placeholder = 'stacked-placeholder';
            } else {
                var placeholder = 'table-row-placeholder';
            }

            return {
                axis: "y",
                revert: 175,
                handle: '.drag-handle',
                placeholder: placeholder,
                forcePlaceholderSize: true,

                start: function(e, ui) {
                    start = ui.item.index();
                    ui.placeholder.height(ui.item.height());
                },

                update: function(e, ui) {
                    var end  = ui.item.index(),
                        swap = self.data.splice(start, 1)[0];

                    self.data.splice(end, 0, swap);
                }
            }
        },

        /**
         * Bootstrap Column Width class
         * Takes a percentage based integer and converts it to a bootstrap column number
         * eg. 100 => 12, 50 => 6, etc.
         */
        colClass: function(width) {
            if (this.$root.isPreviewing) {
                return 'col-md-12';
            }

            width = width || 100;
            return 'col-md-' + Math.round(width / 8.333);
        },

        gridColWidth: function(width) {
            return (width === 100) ? '' :  width + '%';
        },

        getReplicatorPreviewText() {
            return _.map(this.$children, (fieldtype) => {
                return (typeof fieldtype.getReplicatorPreviewText !== 'undefined')
                    ? fieldtype.getReplicatorPreviewText()
                    : JSON.stringify(fieldtype.data);
            }).join(', ');
        },

        focus() {
            if (this.hasData) {
                this.$children[0].focus();
            } else {
                this.$els.addRowButton.focus();
            }
        }
    }
};
</script>
