// Mixins
import Vue from 'vue'
import Fieldtype from '../components/fieldtypes/Fieldtype.vue'
import IndexFieldtype from '../components/fieldtypes/IndexFieldtype.vue'
import BardToolbarButton from '../components/fieldtypes/bard/ToolbarButton.vue'
import Listing from '../components/Listing.vue'
import * as FieldConditions from '../components/field-conditions/FieldConditions.js';

window.Fieldtype = Fieldtype;
window.IndexFieldtype = IndexFieldtype;
window.BardToolbarButton = BardToolbarButton;
window.Listing = Listing;
window.FieldConditions = FieldConditions;
