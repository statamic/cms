<div class="flashdance" v-cloak></div>

@if (session('error'))
    <div class="page-wrapper">
        <div class="alert alert-danger">
            <p>{{ session('error') }}</p>
        </div>
    </div>
@endif
