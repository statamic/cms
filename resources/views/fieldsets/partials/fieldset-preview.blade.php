<div class="row fieldset-preview" ui-sortable="sortableOptions" ng-model="fields">

    <div ng-repeat="field in fields track by $index"
         class="col-md-@{{ field.width | cols }} fieldset-field"
         ng-class="{'editing': selectedField == $index}"
         ng-click="selectField(this)">

        <div class="actions">
            <div class="actions-inner">
                Drag to reorder, click to edit, or
                <a href="" ng-confirm-click="deleteField(this)"
                           ng-confirm-msg="Are you sure you want to delete this field?">remove this field</a>.
            </div>
        </div>

        <div ng-bind-html="field.html" class="form-group"></div>
    </div>

</div>