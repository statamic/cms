<div class="card p-0  content">
    <div class="p-3">
        <h1>Getting started with Statamic</h1>
        <p>To begin building your new Statamic site, we recommend starting with these steps.</p>
    </div>
    <div class="p-3 border-t flex items-center">
        <div class="h-8 w-8 mr-2 text-blue">
            @svg('book-pages')
        </div>
        <div class="flex-1 mr-3">
            <h3 class="mb-0">Read the Documentation</h3>
            <p>Get to know Statamic understand its capabilities the right way.</p>
        </div>
        <a href="{{ cp_route('collections.create') }}" class="btn btn-primary min-w-xs block">
            Visit the Docs
        </a>
    </div>
    <div class="p-3 border-t flex items-center">
        <div class="h-8 w-8 mr-2 text-blue">
            @svg('content-writing')
        </div>
        <div class="flex-1 mr-3">
            <h3 class="mb-0">Collections</h3>
            <p>Collections contain the different types of content in your site.</p>
        </div>
        <a href="{{ cp_route('collections.create') }}" class="btn btn-primary min-w-xs block">
            Create Collection
        </a>
    </div>
    <div class="p-3 border-t flex items-center">
        <div class="h-8 w-8 mr-2 text-blue">
            @svg('blueprints')
        </div>
        <div class="flex-1 mr-3">
            <h3 class="mb-0">Blueprints</h3>
            <p>Blueprints define the custom fields used to create and store your content.</p>
        </div>
        <a href="{{ cp_route('blueprints.create') }}" class="btn btn-primary min-w-xs block">
            Create Blueprint
        </a>
    </div>
    <div class="p-3 border-t flex items-center">
        <div class="h-8 w-8 mr-2 text-blue">
            @svg('addons')
        </div>
        <div class="flex-1 mr-3">
            <h3 class="mb-0">Explore the Addon Marketplace</h3>
            <p>Addons extend Statamic's core capabilities. Go see what's possible!</p>
        </div>
        <a href="{{ cp_route('addons.index') }}" class="btn btn-primary min-w-xs block">
            Explore Addons
        </a>
    </div>
</div>
