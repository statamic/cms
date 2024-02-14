<tr>
    <th class="pl-4 py-2 w-1/4">{{ Statamic\trans('Driver') }}</th>
    <td><code>{{ config('mail.driver') }}</code></td>
</tr>
<tr>
    <th class="pl-4 py-2 w-1/4">{{ Statamic\trans('Host') }}</th>
    <td><code>{{ config('mail.host') }}</code></td>
</tr>
<tr>
    <th class="pl-4 py-2 w-1/4">{{ Statamic\trans('Port') }}</th>
    <td><code>{{ config('mail.port') }}</code></td>
</tr>
<tr>
    <th class="pl-4 py-2 w-1/4">{{ Statamic\trans('Encryption') }}</th>
    <td>
        @if (config('mail.encryption'))
            <code>{{ config('mail.encryption') }}</code>
        @endif
    </td>
</tr>
<tr>
    <th class="pl-4 py-2 w-1/4">{{ Statamic\trans('Username') }}</th>
    <td>
        @if (config('mail.username'))
            <code>{{ config('mail.username') }}</code>
        @endif
    </td>
</tr>
<tr>
    <th class="pl-4 py-2 w-1/4">{{ Statamic\trans('Password') }}</th>
    <td>
        @if (config('mail.password'))
            <code>{{ config('mail.password') }}</code>
        @endif
    </td>
</tr>
<tr>
    <th class="pl-4 py-2 w-1/4">{{ Statamic\trans('Sendmail') }}</th>
    <td><code>{{ config('mail.sendmail') }}</code></td>
</tr>
