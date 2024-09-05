<session-expiry
    email="{{ $email }}"
    :warn-at="{{ $warnAt }}"
    :lifetime="{{ $lifetime }}"
    :oauth-provider="{{ json_encode($oauth) }}"
    :auth="{{ json_encode($auth) }}"
></session-expiry>
