@if (config('statamic.cp.link_to_docs'))
    <div class="flex justify-center text-center mt-16___REPLACED">
        <div class="bg-white rounded-full px-6___REPLACED py-2___REPLACED shadow-sm text-sm text-grey-70">{{ $text ?? __('Learn more about') }} <a href="{{ $url }}" target="_blank" rel="noopener noopener" class="text-blue hover:text-blue-dark">{{ $topic }}</a></div>
    </div>
@endif
