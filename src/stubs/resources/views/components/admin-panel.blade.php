<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
	<div class="grid auto-rows-min gap-4 md:grid-cols-2">
		@if ($header || $desc)
			<div>
				@isset($header)
					<h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ $header }}</h2>
				@endisset

				@isset($desc)
					<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $desc }}</p>
				@endisset
			</div>
		@endif
		@if ($button)
			<div>
				@isset($button)
					{{ $button }}
				@endisset
			</div>
		@endif
	</div>

	<flux:separator variant="subtle" />

	<div class="relative h-full flex-1 overflow-hidden">
		<div class="mt-8 flow-root">
			<div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
				<div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-10">
					{{ $slot }}
				</div>
			</div>
		</div>
	</div>
</div>
