<div ng-repeat="field in selectedFieldFieldtypeConfig()">

    <div class="form-group">
        <label>@{{ field.display || field.name|ucfirst }}</label>

        <div ng-if="field.type == 'text'">
            <input type="text" class="form-control"
                   ng-model="fieldset.fields[selectedField][field.name]"
                   ng-blur="save()" />
        </div>

        <div ng-if="field.type == 'select'">
            <select class="form-control"
                    ng-model="fieldset.fields[selectedField][field.name]"
                    ng-options="key as value for (key, value) in field.options"
                    ng-blur="save()">
                <option value=""></option>
            </select>
        </div>

        <small class="help-block">@{{ field.instructions }}</small>
    </div>

</div>