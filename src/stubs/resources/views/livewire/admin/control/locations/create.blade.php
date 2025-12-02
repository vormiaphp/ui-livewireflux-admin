<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use App\Traits\Vrm\Livewire\WithNotifications;

new class extends Component {
    use WithNotifications;

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('nullable|string|max:1000')]
    public $description = '';

    #[Validate('nullable|string|max:10')]
    public $dial_code = '';

    #[Validate('nullable|string|max:2')]
    public $code = '';

    #[Validate('required|string|in:country,city,area,zone')]
    public $group = 'country';

    #[Validate('nullable|integer')]
    public $parent = 0;

    public function mount(): void
    {
        // Detect group from route name
        $routeName = request()->route()->getName();

        if (str_contains($routeName, 'countries')) {
            $this->group = 'country';
        } elseif (str_contains($routeName, 'cities')) {
            $this->group = 'city';
        } elseif (str_contains($routeName, 'areas')) {
            $this->group = 'area';
        } elseif (str_contains($routeName, 'zones')) {
            $this->group = 'zone';
        }
    }

    #[Computed]
    public function parent_list()
    {
        $query = \App\Models\Vrm\Taxonomy::where('is_active', true)->where('type', 'location');

        // Filter parent options based on selected group
        // Country has no parent
        // City parent must be country
        // Area parent must be city
        // Zone parent must be area
        if ($this->group === 'city') {
            $query->where('group', 'country');
        } elseif ($this->group === 'area') {
            $query->where('group', 'city');
        } elseif ($this->group === 'zone') {
            $query->where('group', 'area');
        } else {
            // For country, return empty list
            return collect();
        }

        return $query->get();
    }

    public function updatedGroup()
    {
        // Reset parent when group changes
        $this->parent = 0;
    }

    public function save(): void
    {
        // Validate the form
        $this->validate();

        // Validate parent requirement based on group
        if ($this->group !== 'country' && $this->parent == 0) {
            $this->notifyError(__('Parent is required for ' . $this->group . '.'));
            return;
        }

        try {
            // Create the Taxonomy
            $_taxonomy = new \App\Models\Vrm\Taxonomy();
            $_taxonomy->name = $this->name;
            $_taxonomy->type = 'location';
            $_taxonomy->group = $this->group;
            $_taxonomy->parent_id = $this->parent == 0 ? null : $this->parent;
            $_taxonomy->save();

            // Meta
            if ($this->description) {
                $_taxonomy->setMeta('description', $this->description);
            }

            // Save dial_code and code meta (only for countries)
            if ($this->group === 'country') {
                if ($this->dial_code) {
                    $_taxonomy->setMeta('dial_code', $this->dial_code);
                }
                if ($this->code) {
                    $_taxonomy->setMeta('code', $this->code);
                }
            }

            // Reset form
            $this->reset(['name', 'description', 'parent', 'dial_code', 'code']);
            // Keep group so user can add more of same type

            // Flash success message
            $this->notifySuccess(__('Location created successfully!'));
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to create location. Please try again: ' . $e->getMessage()));
        }
    }

    public function cancel(): void
    {
        $this->reset(['name', 'description', 'parent', 'dial_code', 'code']);
        $this->notifyInfo(__('Location creation cancelled!'));
    }
}; ?>

<div>
	<x-admin-panel>
		<x-slot name="header">{{ __('Add New ' . ucfirst($this->group)) }}</x-slot>
		<x-slot name="desc">
			{{ __('Add a new ' . $this->group . ' location to the system.') }}
		</x-slot>

		<x-slot name="button">
			@php
				$indexRoute = match ($this->group) {
				    'country' => 'admin.countries.index',
				    'city' => 'admin.cities.index',
				    'area' => 'admin.areas.index',
				    'zone' => 'admin.zones.index',
				    default => 'admin.countries.index',
				};
			@endphp
			<a href="{{ route($indexRoute) }}"
				class="bg-black text-white hover:bg-gray-800 px-3 py-2 rounded-md float-right text-sm font-bold">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4 inline-block">
					<path fill-rule="evenodd"
						d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-4.28 9.22a.75.75 0 0 0 0 1.06l3 3a.75.75 0 1 0 1.06-1.06l-1.72-1.72h5.69a.75.75 0 0 0 0-1.5h-5.69l1.72-1.72a.75.75 0 0 0-1.06-1.06l-3 3Z"
						clip-rule="evenodd" />
				</svg>
				Go Back
			</a>
		</x-slot>

		{{-- New Form --}}
		<div class="overflow-hidden shadow-sm ring-1 ring-black/5 sm:rounded-lg px-4 py-5 mb-5 sm:p-6">
			{{-- Display notifications --}}
			{!! $this->renderNotification() !!}

			<form wire:submit="save">
				<div class="space-y-12">
					<div class="grid grid-cols-1 gap-x-8 gap-y-10 pb-12 md:grid-cols-3">
						<div>
							<h2 class="text-base/7 font-semibold text-gray-900">{{ ucfirst($this->group) }} Details</h2>
							<p class="mt-1 text-sm/6 text-gray-600">This is the name of the {{ $this->group }} that will be displayed in the
								application.</p>
						</div>

						<div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
							@if ($this->group !== 'country')
								<div class="col-span-full">
									<label for="parent" class="block text-sm/6 font-medium text-gray-900 required">Parent
										{{ ucfirst($this->group === 'city' ? 'Country' : ($this->group === 'area' ? 'City' : 'Area')) }}</label>
									<div class="mt-2">
										<select wire:model="parent" id="parent"
											class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
											<option value="0">-- Select Parent --</option>
											@foreach ($this->parent_list as $_parent)
												<option value="{{ $_parent->id }}">{{ $_parent->name }}</option>
											@endforeach
										</select>
										<span class="text-red-500 text-sm italic "> {{ $errors->first('parent') }}</span>
									</div>
									<p class="mt-3 text-sm/6 text-gray-600">Select the parent
										{{ $this->group === 'city' ? 'country' : ($this->group === 'area' ? 'city' : 'area') }}.</p>
								</div>
							@endif

							<div class="col-span-full">
								<label for="name" class="block text-sm/6 font-medium text-gray-900 required">Name</label>
								<div class="mt-2">
									<div
										class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
										<input type="text" id="name" wire:model="name"
											placeholder="e.g. {{ $this->group === 'country' ? 'United States' : ($this->group === 'city' ? 'New York' : ($this->group === 'area' ? 'Manhattan' : 'Upper East Side')) }}"
											class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
									</div>
									<span class="text-red-500 text-sm italic "> {{ $errors->first('name') }}</span>
								</div>
							</div>

							@if ($this->group === 'country')
								<div class="sm:col-span-3">
									<label for="dial_code" class="block text-sm/6 font-medium text-gray-900">Dial Code</label>
									<div class="mt-2">
										<div
											class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
											<input type="text" id="dial_code" wire:model="dial_code" placeholder="e.g. +1, +234, +93"
												class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
										</div>
										<span class="text-red-500 text-sm italic "> {{ $errors->first('dial_code') }}</span>
									</div>
									<p class="mt-3 text-sm/6 text-gray-600">Enter the country dial code (e.g., +1, +234).</p>
								</div>

								<div class="sm:col-span-3">
									<label for="code" class="block text-sm/6 font-medium text-gray-900">Country Code</label>
									<div class="mt-2">
										<div
											class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
											<input type="text" id="code" wire:model="code" maxlength="2" placeholder="e.g. US, NG, AF"
												class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6 uppercase" />
										</div>
										<span class="text-red-500 text-sm italic "> {{ $errors->first('code') }}</span>
									</div>
									<p class="mt-3 text-sm/6 text-gray-600">Enter the ISO 3166-1 alpha-2 country code (2 letters).</p>
								</div>
							@endif

							<div class="col-span-full">
								<label for="description" class="block text-sm/6 font-medium text-gray-900">Description</label>
								<div class="mt-2">
									<textarea id="description" wire:model="description" rows="3"
									 class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"></textarea>
									<span class="text-red-500 text-sm italic "> {{ $errors->first('description') }} </span>
								</div>
								<p class="mt-3 text-sm/6 text-gray-600">Write a description for the {{ $this->group }}.</p>
							</div>

							<div class="col-span-full">
								<div class="flex items-center justify-end gap-x-3 border-t border-gray-900/10 pt-4">
									<button type="button" wire:click="cancel" class="text-sm font-semibold text-gray-900">Cancel</button>

									<button type="submit" wire:loading.attr="disabled"
										class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
											stroke="currentColor" class="size-6 inline-block">
											<path stroke-linecap="round" stroke-linejoin="round"
												d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
										</svg>
										<span wire:loading.remove>Save</span>
										<span wire:loading>Saving...</span>
									</button>
								</div>
							</div>
						</div>
					</div>
			</form>
		</div>
	</x-admin-panel>
</div>
