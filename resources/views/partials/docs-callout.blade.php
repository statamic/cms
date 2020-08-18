@if (config('statamic.cp.link_to_docs'))
    <div class="flex justify-center text-center mt-6">
        <div class="bg-white rounded-full px-3 py-1 shadow-sm text-sm text-grey-70">{{ $text ?? __('Learn more about') }} <a href="{{ $url }}" target="_blank" rel="noopener noopener" class="text-blue hover:text-blue-dark">{{ $topic }}</a></div>
    </div>
@endif
