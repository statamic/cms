<tr>
    <th class="pl-4 py-2 w-1/4">{{ Statamic\trans('Default Mailer') }}</th>
    <td><code>{{ config('mail.default') }}</code></td>
</tr>
@if (config('mail.default') == 'smtp')
<tr>
    <th class="pl-4 py-2 w-1/4">{{ Statamic\trans('Host') }}</th>
    <td><code>{{ config('mail.mailers.smtp.host') }}</code></td>
</tr>
<tr>
    <th class="pl-4 py-2 w-1/4">{{ Statamic\trans('Port') }}</th>
    <td><code>{{ config('mail.mailers.smtp.port') }}</code></td>
</tr>
<tr>
    <th class="pl-4 py-2 w-1/4">{{ Statamic\trans('Encryption') }}</th>
    <td>
        @if (config('mail.mailers.smtp.encryption'))
            <code>{{ config('mail.mailers.smtp.encryption') }}</code>
        @endif
    </td>
</tr>
<tr>
    <th class="pl-4 py-2 w-1/4">{{ Statamic\trans('Username') }}</th>
    <td>
        @if (config('mail.mailers.smtp.username'))
            <code>{{ config('mail.mailers.smtp.username') }}</code>
        @endif
    </td>
</tr>
<tr>
    <th class="pl-4 py-2 w-1/4">{{ Statamic\trans('Password') }}</th>
    <td>
        @if (config('mail.mailers.smtp.password'))
            <code>{{ config('mail.mailers.smtp.password') }}</code>
        @endif
    </td>
</tr>
@endif
@if (config('mail.default') == 'sendmail')
<tr>
    <th class="pl-4 py-2 w-1/4">{{ Statamic\trans('Sendmail') }}</th>
    <td><code>{{ config('mail.mailers.sendmail.path') }}</code></td>
</tr>
@endif
