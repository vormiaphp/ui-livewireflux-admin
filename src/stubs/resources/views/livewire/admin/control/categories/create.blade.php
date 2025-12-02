<?php

use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Facades\Vrm\MediaForge;
use Livewire\Attributes\Validate;
use App\Traits\Vrm\Livewire\WithNotifications;

new class extends Component {
    use WithFileUploads;
    use WithNotifications;

    // Path to upload the file
    public $uploadedPath = 'video-img-categories';

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('nullable|string|max:1000')]
    public $description = '';

    #[Validate('nullable|image|mimes:jpg,jpeg,png,webp|max:2048')]
    public $picture;

    // Todo: Save the Taxonomy
    public function save()
    {
        // Validate the form
        $this->validate();

        try {
            $_name = $this->name;
            $_description = $this->description;

            // Handle file upload if picture is provided
            if ($this->picture) {
                try {
                    // Upload
                    $_picture = $path = MediaForge::upload($this->picture)->useYearFolder(true)->randomizeFileName(true)->to($this->uploadedPath)->resize(95, 95)->run();
                } catch (\Exception $e) {
                    $this->notifyError(__('Failed to upload picture. Please try again: ' . $e->getMessage()));
                }
            }

            // Create the Taxonomy
            $_taxonomy = new \App\Models\Vrm\Taxonomy();
            $_taxonomy->name = $_name;
            $_taxonomy->type = 'category';
            $_taxonomy->group = 'video';
            $_taxonomy->save();

            // Meta
            $_taxonomy->setMeta('description', $_description);
            $_taxonomy->setMeta('picture', $_picture ?? null);

            // Reset form
            $this->reset(['name', 'description', 'picture']);

            // Flash success message
            $this->notifySuccess(__('Video category created successfully!'));
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to create video category. Please try again.'));
        }
    }

    public function cancel()
    {
        $this->reset(['name', 'description', 'picture']);
        $this->notifyInfo(__('Video category creation cancelled!'));
    }
}; ?>

<div>
	<x-admin-panel>
		<x-slot name="header">{{ __('Add New Video Category') }}</x-slot>
		<x-slot name="desc">
			{{ __('Add a new video category to the mobile app.') }}
		</x-slot>

		<x-slot name="button">
			<a href="{{ route('admin.categories.index') }}"
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
							<h2 class="text-base/7 font-semibold text-gray-900">Video Category</h2>
							<p class="mt-1 text-sm/6 text-gray-600">This is the name of the video category that will be displayed in the
								mobile
								app.</p>
						</div>

						<div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
							<div class="col-span-full">
								<label for="name" class="block text-sm/6 font-medium text-gray-900 required">Name</label>
								<div class="mt-2">
									<div
										class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
										<input type="text" id="name" wire:model="name" placeholder="e.g. Ebony"
											class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
									</div>
									<span class="text-red-500 text-sm italic "> {{ $errors->first('name') }}</span>
								</div>
							</div>

							<div class="col-span-full">
								<label for="description" class="block text-sm/6 font-medium text-gray-900">Description</label>
								<div class="mt-2">
									<textarea id="description" wire:model="description" rows="3"
									 class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"></textarea>
									<span class="text-red-500 text-sm italic "> {{ $errors->first('description') }} </span>
								</div>
								<p class="mt-3 text-sm/6 text-gray-600">Write a description for the video category.</p>
							</div>

							<div class="col-span-full">
								<label for="photo" class="block text-sm/6 font-medium text-gray-900">Photo</label>
								<div class="mt-2 flex items-center gap-x-3">
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
										stroke="currentColor" class="size-6">
										<path stroke-linecap="round" stroke-linejoin="round"
											d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
									</svg>
									<div>
										<input type="file" wire:model="picture" accept=".jpg,.jpeg,.png,.webp"
											class="block w-full cursor-pointer px-3 py-2 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-gray-200 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-gray-900 hover:file:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
										<span class="text-red-500 text-sm italic "> {{ $errors->first('picture') }} </span>
									</div>
								</div>

								{{-- Preview uploaded image --}}
								@if ($picture)
									<div class="mt-2">
										<img src="{{ $picture->temporaryUrl() }}" alt="Preview" class="h-20 w-20 object-cover rounded-md">
									</div>
								@endif
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
