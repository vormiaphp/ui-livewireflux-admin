<?php

use App\Actions\Fortify\PasswordValidationRules;
use App\Facades\Vrm\MediaForge;
use App\Models\User;
use App\Traits\Vrm\Livewire\WithNotifications;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use PasswordValidationRules;
    use WithFileUploads;
    use WithNotifications;

    // Path to upload the photo
    public $uploadedPath = 'admin-users';

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('required|string|email|max:255')]
    public $email = '';

    #[Validate('nullable|string|max:20')]
    public $phone = '';

    #[Validate('nullable|string|max:20')]
    public $whatsapp_number = '';

    #[Validate('required|string')]
    public $password = '';

    #[Validate('required|string')]
    public $password_confirmation = '';

    #[Validate('nullable|image|mimes:jpg,jpeg,png,webp|max:2048')]
    public $photo;

    /**
				 * Create a new admin user.
				 */
    public function create(): void
    {
        // Validate the form using the same rules as CreateNewUser
        $validated = Validator::make(
            [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'whatsapp_number' => $this->whatsapp_number,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'photo' => $this->photo,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
                'phone' => ['nullable', 'string', 'max:20'],
                'whatsapp_number' => ['nullable', 'string', 'max:20'],
                'password' => $this->passwordRules(),
                'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ],
        )->validate();

        try {
            // Handle photo upload if provided
            $_photo = null;
            if ($this->photo) {
                try {
                    // Upload photo
                    $_photo = MediaForge::upload($this->photo)->useYearFolder(true)->randomizeFileName(true)->to($this->uploadedPath)->resize(400, 400)->run();
                } catch (\Exception $e) {
                    $this->notifyError(__('Failed to upload photo. Please try again: ' . $e->getMessage()));

                    return;
                }
            }

            // Create the user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => $validated['password'],
                'is_active' => true,
            ]);

            // Store whatsapp number in user_meta if provided
            if (!empty($validated['whatsapp_number'])) {
                $user->setMeta('whatsapp_number', $validated['whatsapp_number']);
            }

            // Store photo in user_meta if provided
            if ($_photo) {
                $user->setMeta('photo', $_photo);
            }

            // Attach admin role (role ID 1)
            $user->roles()->attach(1);

            // Reset form
            $this->reset(['name', 'email', 'phone', 'whatsapp_number', 'password', 'password_confirmation', 'photo']);

            // Show success message
            $this->notifySuccess(__('Admin created successfully!'));
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to create admin. Please try again: ' . $e->getMessage()));
        }
    }

    public function cancel(): void
    {
        $this->reset(['name', 'email', 'phone', 'whatsapp_number', 'password', 'password_confirmation', 'photo']);
        $this->notifyInfo(__('Admin creation cancelled!'));
    }
}; ?>

<div>
	<x-admin-panel>
		<x-slot name="header">{{ __('Add New Admin') }}</x-slot>
		<x-slot name="desc">
			{{ __('Enter the details below to create a new admin account.') }}
		</x-slot>

		<x-slot name="button">
			<a href="{{ route('admin.admins.index') }}"
				class="bg-black dark:bg-gray-700 text-white hover:bg-gray-800 dark:hover:bg-gray-600 px-3 py-2 rounded-md float-right text-sm font-bold">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4 inline-block">
					<path fill-rule="evenodd"
						d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-4.28 9.22a.75.75 0 0 0 0 1.06l3 3a.75.75 0 1 0 1.06-1.06l-1.72-1.72h5.69a.75.75 0 0 0 0-1.5h-5.69l1.72-1.72a.75.75 0 0 0-1.06-1.06l-3 3Z"
						clip-rule="evenodd" />
				</svg>
				Go Back
			</a>
		</x-slot>

		{{-- Form --}}
		<div class="overflow-hidden shadow-sm ring-1 ring-black/5 dark:ring-white/10 sm:rounded-lg px-4 py-5 mb-5 sm:p-6">
			{{-- Display notifications --}}
			{!! $this->renderNotification() !!}

			<form wire:submit="create">
				<div class="space-y-12">
					<div class="grid grid-cols-1 gap-x-8 gap-y-10 pb-12 md:grid-cols-3">
						<div>
							<h2 class="text-base/7 font-semibold text-gray-900 dark:text-gray-100">Admin Information</h2>
							<p class="mt-1 text-sm/6 text-gray-600 dark:text-gray-300">Enter the admin's information below. All fields marked with <span
									class="text-red-500">*</span> are required.</p>
						</div>

						<div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
							<div class="col-span-full">
								<label for="name" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100 required">Name</label>
								<div class="mt-2">
									<div
										class="flex items-center rounded-md bg-white dark:bg-gray-700 pl-3 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
										<input type="text" id="name" wire:model="name" placeholder="e.g. John Doe"
											class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none sm:text-sm/6" />
									</div>
									<span class="text-red-500 text-sm italic "> {{ $errors->first('name') }}</span>
								</div>
							</div>

							<div class="col-span-full">
								<label for="email" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100 required">Email</label>
								<div class="mt-2">
									<div
										class="flex items-center rounded-md bg-white dark:bg-gray-700 pl-3 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
										<input type="email" id="email" wire:model="email" placeholder="email@example.com"
											class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none sm:text-sm/6" />
									</div>
									<span class="text-red-500 text-sm italic "> {{ $errors->first('email') }}</span>
								</div>
							</div>

							<div class="col-span-full">
								<label for="phone" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">Phone</label>
								<div class="mt-2">
									<div
										class="flex items-center rounded-md bg-white dark:bg-gray-700 pl-3 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
										<input type="text" id="phone" wire:model="phone" placeholder="e.g. +1234567890"
											class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none sm:text-sm/6" />
									</div>
									<span class="text-red-500 text-sm italic "> {{ $errors->first('phone') }}</span>
								</div>
							</div>

							<div class="col-span-full">
								<label for="whatsapp_number" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">WhatsApp Number</label>
								<div class="mt-2">
									<div
										class="flex items-center rounded-md bg-white dark:bg-gray-700 pl-3 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
										<input type="text" id="whatsapp_number" wire:model="whatsapp_number" placeholder="e.g. +1234567890"
											class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none sm:text-sm/6" />
									</div>
									<span class="text-red-500 text-sm italic "> {{ $errors->first('whatsapp_number') }}</span>
								</div>
							</div>

							<div class="col-span-full" x-data="{ showPassword: false }">
								<label for="password" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100 required">Password</label>
								<div class="mt-2">
									<div
										class="flex items-center rounded-md bg-white dark:bg-gray-700 pl-3 pr-1 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
										<input :type="showPassword ? 'text' : 'password'" id="password" wire:model="password"
											placeholder="Enter password"
											class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none sm:text-sm/6" />
										<button type="button" x-on:click="showPassword = !showPassword"
											class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
											<svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none"
												viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
												<path stroke-linecap="round" stroke-linejoin="round"
													d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.5a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 12.544 12.544m0 0L21 21m-3.228-3.228a3 3 0 1 1-4.243-4.243" />
											</svg>
											<svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" fill="none"
												viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
												<path stroke-linecap="round" stroke-linejoin="round"
													d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.964 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178ZM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
											</svg>
										</button>
									</div>
									<span class="text-red-500 text-sm italic "> {{ $errors->first('password') }}</span>
								</div>
								<p class="mt-3 text-sm/6 text-gray-600 dark:text-gray-300">Set a secure password for the admin to login.</p>
							</div>

							<div class="col-span-full">
								<label for="password_confirmation" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100 required">Confirm
									Password</label>
								<div class="mt-2">
									<div
										class="flex items-center rounded-md bg-white dark:bg-gray-700 pl-3 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
										<input type="password" id="password_confirmation" wire:model="password_confirmation"
											placeholder="Confirm password"
											class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none sm:text-sm/6" />
									</div>
									<span class="text-red-500 text-sm italic "> {{ $errors->first('password_confirmation') }}</span>
								</div>
							</div>

							<div class="col-span-full">
								<label for="photo" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">Photo</label>
								<div class="mt-2 flex items-center gap-x-3">
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
										stroke="currentColor" class="size-6">
										<path stroke-linecap="round" stroke-linejoin="round"
											d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
									</svg>
									<div>
										<input type="file" wire:model="photo" accept=".jpg,.jpeg,.png,.webp"
											class="block w-full cursor-pointer px-3 py-2 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-gray-200 dark:file:bg-gray-600 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-gray-900 dark:file:text-gray-100 hover:file:bg-gray-300 dark:hover:file:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
										<span class="text-red-500 text-sm italic "> {{ $errors->first('photo') }} </span>
									</div>
								</div>

								{{-- Preview uploaded image --}}
								@if ($photo)
									<div class="mt-2">
										<img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="h-20 w-20 object-cover rounded-md">
									</div>
								@endif
								<p class="mt-3 text-sm/6 text-gray-600 dark:text-gray-300">Upload an optional photo for the admin (JPG, PNG, or WebP format, max
									2MB).</p>
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
