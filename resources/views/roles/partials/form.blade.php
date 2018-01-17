<div class="card">
    <div class="form-group">
        <label for="">{{ trans_choice('cp.titles', 1) }}</label>
        <input type="text" name="title" value="{{ $role_title }}" class="form-control" autofocus="autofocus" />
    </div>
    <div class="form-group">
        <label for="">{{ trans_choice('cp.slug', 1) }}</label>
        <input type="text" name="slug" value="{{ $role_slug }}" class="form-control" />
    </div>
</div>

<roles inline-template v-cloak
       :permissions='{{ json_encode($permissions) }}'
       :selected='{{ json_encode($selected) }}'
       :titles='{{ json_encode($content_titles) }}'>
    <div class="card roles">
        <div>
            <ul>
                <li>
                    <input type="checkbox" id="super" name="permissions[]" v-model="selected" value="super" />
                    <label for="super">{{ translate('permissions.super') }}</label>
                </li>
            </ul>
        </div>
        <template v-if="!superSelected">
            <hr>
            <div v-for="(group, p) in permissions">
                <h3>@{{ label(group) }}</h3>
                <ul>
                    <li v-for="(key, value) in p">
                        <permission :key="key" :value="value" :selected-permissions="selected"></permission>
                    </li>
                </ul>
            </div>
        </template>
    </div>
</roles>
