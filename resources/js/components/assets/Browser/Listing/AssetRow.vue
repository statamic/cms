<template>

    <tr @click="toggle" @dblclick="doubleClicked" :class="{ 'selected': isSelected }">

        <td class="thumbnail-col" @dragstart="assetDragStart">
            <div v-if="canShowSvg"
                 class="img svg-img"
                 :style="svgBackgroundStyle">
            </div>
            <div class="img" v-else>
                <img v-if="asset.is_image" :src="asset.thumbnail" />
                <file-icon v-else :extension="asset.extension"></file-icon>
            </div>
        </td>

        <td class="title-col">
            <span v-if="asset.title !== asset.filename" :title="asset.basename">{{ asset.title }}</span>
            <span v-else>{{ asset.basename }}</span>
        </td>

        <td class="size-col extra-col">{{ asset.size_formatted }}</td>
        <td class="modifed-col extra-col">{{ asset.last_modified_formatted }}</td>

        <td class="column-actions">

            <div class="btn-group action-more" :class="{ open: showActionsDropdown }">
                <button type="button" class="btn-more dropdown-toggle" @click.prevent.stop="toggleActions">
                    <i class="icon icon-dots-three-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="" @click.prevent="closeDropdownAndEditAsset">{{ translate('cp.edit') }}</a></li>
                    <li class="divider"></li>
                    <li class="warning"><a href="" @click.prevent="closeDropdownAndDeleteAsset">{{ translate('cp.delete') }}</a></li>
                </ul>
            </div>

        </td>

    </tr>

</template>


<script>
import Asset from './Asset';
import Row from './Row';

export default {

    mixins: [Asset, Row],

    methods: {

        closeDropdownAndEditAsset() {
            this.showActionsDropdown = false;
            this.editAsset();
        },

        closeDropdownAndDeleteAsset() {
            this.showActionsDropdown = false;
            this.deleteAsset();
        }

    }

}
</script>
