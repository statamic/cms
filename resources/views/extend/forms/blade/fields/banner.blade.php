<div>
    @if (isset($icon))
        <s:svg :src="$icon" style="width: 30px; height: 30px" />
    @endif
    {!! Statamic::modify($content ?? '')->markdown() !!}
</div>
