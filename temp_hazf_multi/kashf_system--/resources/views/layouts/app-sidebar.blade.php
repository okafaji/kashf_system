<!DOCTYPE html>
<html lang="ar" dir="rtl">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="csrf-token" content="{{ csrf_token() }}">

		<title>{{ config('app.name', 'Laravel') }}</title>

		@vite(['resources/css/app.css', 'resources/js/app.js'])
	</head>
	<body class="font-sans antialiased bg-gray-100">
		<div class="min-h-screen">
			@include('layouts.sidebar')

			<div class="md:mr-72">
				@isset($header)
					<header class="bg-white shadow">
						<div class="mx-auto flex items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
							<button id="sidebarToggle" type="button" class="md:hidden inline-flex items-center rounded bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">
								القائمة
							</button>
							<div class="flex-1 text-right">
								{{ $header }}
							</div>
						</div>
					</header>
				@endisset

				<main class="px-4 py-6 sm:px-6 lg:px-8">
					{{ $slot }}
				</main>
			</div>
		</div>
	</body>
</html>
