{{--
    The <script> tag injected into full-measure statically cached pages for the
    CSRF and nocache helpers. Publish this view to customise the tag - for
    example, to add a `nonce` attribute for a strict Content Security Policy:

        <script nonce="{{ request()->attributes->get('csp_nonce') }}">...

    Available data:
      $inline   - whether the script body is embedded (true) or loaded via src
      $src      - URL of the external script (when not inline)
      $contents - the script body (when inline)
--}}
@if ($inline)<script>{!! $contents !!}</script>@else<script src="{{ $src }}"></script>@endif
