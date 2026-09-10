<div>
    @if($icon)
        <s:svg :src="$icon" style="width: 30px; height: 30px" />
    @endif
    <h2>{{ $heading }}</h2>
    <p>{{ $text }}</p>
</div>
