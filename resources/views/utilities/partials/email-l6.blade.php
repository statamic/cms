<tr>
    <th class="pl-2 py-1 w-1/4">{{ __('Driver') }}</th>
    <td><code>{{ config('mail.driver') }}</code></td>
</tr>
<tr>
    <th class="pl-2 py-1 w-1/4">{{ __('Host') }}</th>
    <td><code>{{ config('mail.host') }}</code></td>
</tr>
<tr>
    <th class="pl-2 py-1 w-1/4">{{ __('Port') }}</th>
    <td><code>{{ config('mail.port') }}</code></td>
</tr>
<tr>
    <th class="pl-2 py-1 w-1/4">{{ __('Encryption') }}</th>
    <td>
        @if (config('mail.encryption'))
            <code>{{ config('mail.encryption') }}</code>
        @endif
    </td>
</tr>
<tr>
    <th class="pl-2 py-1 w-1/4">{{ __('Username') }}</th>
    <td>
        @if (config('mail.username'))
            <code>{{ config('mail.username') }}</code>
        @endif
    </td>
</tr>
<tr>
    <th class="pl-2 py-1 w-1/4">{{ __('Password') }}</th>
    <td>
        @if (config('mail.password'))
            <code>{{ config('mail.password') }}</code>
        @endif
    </td>
</tr>
<tr>
    <th class="pl-2 py-1 w-1/4">{{ __('Sendmail') }}</th>
    <td><code>{{ config('mail.sendmail') }}</code></td>
</tr>
