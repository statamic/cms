<div class="form-group">
    <label>{{ t('field_name') }}</label>
    <input type="text" class="form-control"
           ng-model="fieldset.fields[selectedField].name"
           ng-blur="save()" />
    <small class="help-block">{{ t('field_name_instructions') }}</small>
</div>

<div class="form-group">
    <label>{{ t('display_text') }}</label>
    <input type="text" class="form-control"
           ng-model="fieldset.fields[selectedField].display"
           ng-blur="save()" />
    <small class="help-block">{{ t('display_text_instructions') }}</small>
</div>

<div class="form-group">
    <label>{{ t('field_required') }}</label>
    <div class="checkbox">
        <label>
            <input type="checkbox"
                   ng-model="fieldset.fields[selectedField].required"
                   ng-change="save()" />
            {{ t('field_required_instructions') }}
        </label>
    </div>
</div>

<div class="form-group">
    <label>{{ t('width') }}</label>
    <select class="form-control"
            ng-model="fieldset.fields[selectedField].width"
            ng-change="save()"
            ng-integer>
        <option value="100">{{ t('full_width') }}</option>
        <option value="50">{{ t('half') }}</option>
        <option value="25">{{ t('one_quarter') }}</option>
        <option value="75">{{ t('three_quarters') }}</option>
        <option value="33">{{ t('one_third') }}</option>
        <option value="66">{{ t('two_thirds') }}</option>
    </select>
    <small class="help-block">{{ t('field_width_instructions') }}</small>
</div>

<div class="form-group">
    <label>{{ t('instructions') }}</label>
    <textarea class="form-control" rows="2"
              ng-model="fieldset.fields[selectedField].instructions"
              ng-blur="save()"></textarea>
    <small class="help-block">{{ t('field_instructions_instructions') }}</small>
</div>
