<tr>
    <th class="pl-2 py-1 w-1/4">{{ __('Default Mailer') }}</th>
    <td><code>{{ config('mail.default') }}</code></td>
</tr>
@if (config('mail.default') == 'smtp')
<tr>
    <th class="pl-2 py-1 w-1/4">{{ __('Host') }}</th>
    <td><code>{{ config('mail.mailers.smtp.host') }}</code></td>
</tr>
<tr>
    <th class="pl-2 py-1 w-1/4">{{ __('Port') }}</th>
    <td><code>{{ config('mail.mailers.smtp.port') }}</code></td>
</tr>
<tr>
    <th class="pl-2 py-1 w-1/4">{{ __('Encryption') }}</th>
    <td>
        @if (config('mail.mailers.smtp.encryption'))
            <code>{{ config('mail.mailers.smtp.encryption') }}</code>
        @endif
    </td>
</tr>
<tr>
    <th class="pl-2 py-1 w-1/4">{{ __('Username') }}</th>
    <td>
        @if (config('mail.mailers.smtp.username'))
            <code>{{ config('mail.mailers.smtp.username') }}</code>
        @endif
    </td>
</tr>
<tr>
    <th class="pl-2 py-1 w-1/4">{{ __('Password') }}</th>
    <td>
        @if (config('mail.mailers.smtp.password'))
            <code>{{ config('mail.mailers.smtp.password') }}</code>
        @endif
    </td>
</tr>
@endif
@if (config('mail.default') == 'sendmail')
<tr>
    <th class="pl-2 py-1 w-1/4">{{ __('Sendmail') }}</th>
    <td><code>{{ config('mail.mailers.sendmail.path') }}</code></td>
</tr>
@endif
