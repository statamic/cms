<div class="flashdance" v-cloak></div>

@if (count($errors) > 0)
    <div class="page-wrapper">
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    </div>
@endif
