<div class="flashdance" v-cloak></div>

@if (session('error') || count($errors) > 0)
    <div class="page-wrapper">
        <div class="alert alert-danger">
            @if (session('error'))
                <p>{{ session('error') }}</p>
            @else
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            @endif
        </div>
    </div>
@endif
