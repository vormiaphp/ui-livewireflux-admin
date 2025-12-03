{{-- Add this code to resources/views/components/layouts/app/sidebar.blade.php --}}
{{-- Place it just after the closing </flux:navlist.group> tag of the Platform group --}}

@if (auth()->user()?->isAdminOrSuperAdmin())
	<hr />

	<flux:navlist.item icon="tag" :href="route('admin.categories.index')"
		:current="request()->routeIs('admin.categories.*')" wire:navigate>
		{{ __('Categories') }}
	</flux:navlist.item>

	<flux:navlist.item icon="map-pin" :href="route('admin.countries.index')"
		:current="request()->routeIs('admin.countries.*')" wire:navigate>
		{{ __('Countries') }}
	</flux:navlist.item>

	<flux:navlist.item icon="building-office" :href="route('admin.cities.index')"
		:current="request()->routeIs('admin.cities.*')" wire:navigate>
		{{ __('Cities') }}
	</flux:navlist.item>
	<hr />

	<flux:navlist.item icon="check-circle" :href="route('admin.availabilities.index')"
		:current="request()->routeIs('admin.availabilities.*')" wire:navigate>
		{{ __('Availability') }}
	</flux:navlist.item>

	<flux:navlist.item icon="folder-git-2" :href="route('admin.inheritance.index')"
		:current="request()->routeIs('admin.inheritance.index')" wire:navigate>
		{{ __('Inheritance') }}
	</flux:navlist.item>
@endif

@if (auth()->user()?->isSuperAdmin())
	<hr />
	<flux:navlist.group :heading="__('Admin')" class="grid">
		<flux:navlist.item icon="shield-check" :href="route('admin.admins.index')"
			:current="request()->routeIs('admin.admins.*')" wire:navigate>
			{{ __('Admins') }}
		</flux:navlist.item>
	</flux:navlist.group>
@endif
