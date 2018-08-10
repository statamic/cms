<nav class="navbar navbar-main">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#top-nav">
				<span class="sr-only">{{ t('toggle_navigation') }}</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="">
				{{ $title }}
			</a>
		</div>
		<div class="collapse navbar-collapse" id="top-nav">
			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ t('account') }} <span class="caret"></span></a>
				<ul class="dropdown-menu" role="menu">
					<li><a href="{{ route('logout') }}">{{ t('logout') }}</a></li>
				</ul>
				</li>
			</ul>
		</div><!-- /.navbar-collapse -->
	</div><!-- /.container-fluid -->
</nav>
