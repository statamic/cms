<div>
    @if($icon)
        <s:svg :src="$icon" style="width: 50px; height: 50px" />
    @endif
    <h2>{{ $heading }}</h2>
    <p>{{ $text }}</p>
</div>
