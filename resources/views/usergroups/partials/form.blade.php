<div class="form-group">
    <label for="">{{ trans_choice('cp.titles', 1) }}</label>
    <input type="text" name="title" value="{{ $group_title }}" class="form-control" autofocus="autofocus" />
</div>

<div class="form-group">
    <label for="">{{ trans_choice('cp.slugs', 1) }}</label>
    <input type="text" name="slug" value="{{ $group_slug }}" class="form-control" />
</div>

<div class="form-group">
    <label class="block">{{ trans_choice('cp.roles', 2) }}</label>
    <user_roles-fieldtype name="roles" :data='{{ json_encode($roles) }}'></user_roles-fieldtype>
</div>

<div class="form-group">
    <label class="block">{{ trans_choice('cp.users', 2) }}</label>
    <users-fieldtype name="users" :data='{{ json_encode($users) }}' :config="{'mode': 'panes'}"></users-fieldtype>
</div>
