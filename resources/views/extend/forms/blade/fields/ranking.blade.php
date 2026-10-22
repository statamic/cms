@once
    <style>
        [data-ranking] li { cursor: grab; }
        [data-ranking] li[data-dragging] { cursor: grabbing; opacity: 0.5; }
        [data-ranking] li::before { content: '☰ '; opacity: 0.4; }
    </style>

    <script>
        (function() {
            document.addEventListener('dragstart', (e) => {
                var listItem = e.target.closest('[data-ranking] li');
                if (listItem) listItem.setAttribute('data-dragging', '');
            });

            document.addEventListener('dragend', (e) => {
                let listItem = e.target.closest('[data-ranking] li');
                if (listItem) listItem.removeAttribute('data-dragging');
            });

            document.addEventListener('dragover', (e) => {
                let list = e.target.closest('[data-ranking]');
                let listItem = e.target.closest('li');
                let dragged = list ? list.querySelector('[data-dragging]') : null;

                if (list && listItem && dragged && listItem !== dragged) {
                    e.preventDefault();
                    let rect = listItem.getBoundingClientRect();
                    if (e.clientY < rect.top + rect.height / 2) {
                        list.insertBefore(dragged, listItem);
                    } else {
                        list.insertBefore(dragged, listItem.nextSibling);
                    }
                }
            });
        })();
    </script>
@endonce

<ol
    id="{{ $id }}"
    data-ranking
    @if ($error)
        aria-invalid="true" aria-describedby="{{ $id }}-error"
    @elseif ($instructions)
        aria-describedby="{{ $id }}-instructions"
    @endif
>
    @if (is_array($value ?? null) && count($value))
        @foreach ($value as $val)
            <li draggable="true">
                {{ $options[$val] ?? $val }}
                <input type="hidden" name="{{ $name }}[]" value="{{ $val }}">
            </li>
        @endforeach
    @else
        @foreach ($options as $option => $label)
            <li draggable="true">
                {{ $label ?? $option }}
                <input type="hidden" name="{{ $name }}[]" value="{{ $option }}">
            </li>
        @endforeach
    @endif
</ol>
