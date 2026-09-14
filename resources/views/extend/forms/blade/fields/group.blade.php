<div
@if (isset($js_driver)) {!! $js_attributes !!} @endif
>
    @foreach ($fields as $__field)
        {!! $slot?->addContext($__field) !!}
    @endforeach
</div>
