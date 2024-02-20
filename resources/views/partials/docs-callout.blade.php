@if (config('statamic.cp.link_to_docs'))
    <div class="flex justify-center text-center mt-16">
        <div class="bg-white rounded-full px-6 py-2 shadow-sm text-sm text-gray-700">{{ $text ?? __('Learn more about') }} <a href="{{ $url }}" target="_blank" rel="noopener noopener" class="text-blue hover:text-blue-700">{{ $topic }}</a></div>
    </div>
@endif
