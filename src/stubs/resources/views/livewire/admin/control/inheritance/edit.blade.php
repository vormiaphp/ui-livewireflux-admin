<?php

use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Facades\Vrm\MediaForge;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use App\Traits\Vrm\Livewire\WithNotifications;

new class extends Component {
    use WithFileUploads;
    use WithNotifications;

    // The inheritance being edited
    public $inheritance;

    // Form properties
    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('nullable|integer')]
    public $parent = 0;

    #[Validate('nullable|string|max:1000')]
    public $description = '';

    // Load the inheritance data when component mounts
    public function mount($id)
    {
        $this->inheritance = \App\Models\Vrm\Taxonomy::findOrFail($id);

        // Populate form with existing data
        $this->name = $this->inheritance->name;
        $this->parent = $this->inheritance->parent_id ?? 0;
        $this->description = $this->inheritance->getMeta('description', '');
    }

    // Load all inheritance for parent selection
    #[Computed]
    public function inheritance_list()
    {
        $_inheritance_list = \App\Models\Vrm\Taxonomy::where('is_active', true)
            ->where('group', 'inheritance')
            ->where('id', '!=', $this->inheritance->id) // Exclude current inheritance from parent options
            ->get();
        return $_inheritance_list;
    }

    // Update the inheritance
    public function update()
    {
        // Validate the form
        $this->validate();

        try {
            // Update the inheritance
            $this->inheritance->name = $this->name;
            $this->inheritance->parent_id = $this->parent == 0 ? null : $this->parent;
            $this->inheritance->save();

            // Update meta
            $this->inheritance->setMeta('description', $this->description);

            // Load all inheritance for select2
            $this->loadAllInheritance();

            // Flash success message
            $this->notifySuccess(__('Inheritance was updated successfully!'));
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to update inheritance. Please try again.'));
        }
    }

    public function cancel()
    {
        return redirect()->route('admin.inheritance.index');
    }

    // Load all inheritance for select2
    private function loadAllInheritance()
    {
        // Check if $value is true
        $this->dispatch('reinitialize-select2');
    }
}; ?>

<div>
	<x-admin-panel>
		<x-slot name="header">{{ __('Edit Inheritance') }}</x-slot>
		<x-slot name="desc">
			{{ __('Edit the inheritance information for the mobile app.') }}
		</x-slot>

		<x-slot name="button">
			<a href="{{ route('admin.inheritance.index') }}"
				class="bg-black dark:bg-gray-700 text-white hover:bg-gray-800 dark:hover:bg-gray-600 px-3 py-2 rounded-md float-right text-sm font-bold">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4 inline-block">
					<path fill-rule="evenodd"
						d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-4.28 9.22a.75.75 0 0 0 0 1.06l3 3a.75.75 0 1 0 1.06-1.06l-1.72-1.72h5.69a.75.75 0 0 0 0-1.5h-5.69l1.72-1.72a.75.75 0 0 0-1.06-1.06l-3 3Z"
						clip-rule="evenodd" />
				</svg>
				Go Back
			</a>
		</x-slot>

		{{-- Edit Form --}}
		<div class="overflow-hidden shadow-sm ring-1 ring-black/5 dark:ring-white/10 sm:rounded-lg px-4 py-5 mb-5 sm:p-6">
			{{-- Display notifications --}}
			{!! $this->renderNotification() !!}

			<form wire:submit="update">
				<div class="space-y-12">
					<div class="grid grid-cols-1 gap-x-8 gap-y-10 pb-12 md:grid-cols-3">
						<div>
							<h2 class="text-base/7 font-semibold text-gray-900 dark:text-gray-100">Inheritance</h2>
							<p class="mt-1 text-sm/6 text-gray-600 dark:text-gray-300">This is the name of the inheritance that will be displayed in the
								mobile
								app.</p>
						</div>

						<div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
							<div class="col-span-full">
								<label for="name" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100 required">Name</label>
								<div class="mt-2">
									<div
										class="flex items-center rounded-md bg-white dark:bg-gray-700 pl-3 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
										<input type="text" id="name" wire:model="name" placeholder="e.g. Nairobi"
											class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none sm:text-sm/6" />
									</div>
									<span class="text-red-500 text-sm italic "> {{ $errors->first('name') }}</span>
								</div>
							</div>

							<div class="col-span-full">
								<label for="parent" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">Parent Inheritance</label>
								<div class="mt-2">
									<div wire:ignore>
										<select wire:model="parent" id="parent_inheritance_select"
											class="w-full inheritance-select select2 py-2.5 px-3 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500">
											<option value="0">-- No Parent --</option>
											@foreach ($this->inheritance_list as $_index => $_list)
												<option value="{{ $_list->id }}" @selected($this->parent == $_list->id)>
													{{ $_list->name }}
												</option>
											@endforeach
										</select>
									</div>
									<span class="text-red-500 text-sm italic "> {{ $errors->first('parent') }}</span>
								</div>
							</div>

							<div class="col-span-full">
								<label for="description" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">Description</label>
								<div class="mt-2">
									<textarea id="description" wire:model="description" rows="3"
									 class="block w-full rounded-md bg-white dark:bg-gray-700 px-3 py-1.5 text-base text-gray-900 dark:text-gray-100 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"></textarea>
									<span class="text-red-500 text-sm italic "> {{ $errors->first('description') }} </span>
								</div>
								<p class="mt-3 text-sm/6 text-gray-600 dark:text-gray-300">Write a description for the inheritance.</p>
							</div>

							<div class="col-span-full">
								<div class="flex items-center justify-end gap-x-3 border-t border-gray-900/10 dark:border-gray-100/10 pt-4">
									<button type="button" wire:click="cancel" class="text-sm font-semibold text-gray-900 dark:text-gray-100">Cancel</button>

									<button type="submit" wire:loading.attr="disabled"
										class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
											stroke="currentColor" class="size-6 inline-block">
											<path stroke-linecap="round" stroke-linejoin="round"
												d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
										</svg>
										<span wire:loading.remove>Update</span>
										<span wire:loading>Updating...</span>
									</button>
								</div>
							</div>
						</div>
					</div>
			</form>
		</div>
	</x-admin-panel>
</div>
