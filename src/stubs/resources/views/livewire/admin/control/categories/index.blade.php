<?php

use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Log;
use App\Traits\Vrm\Livewire\WithNotifications;

new class extends Component {
    //
    use WithPagination;
    use WithNotifications;

    public $search = '';
    public $perPage = 10;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    #[Computed]
    public function results()
    {
        $query = \App\Models\Vrm\Taxonomy::query();

        // with slugs
        $query->with('slugs');

        // Filter by video group
        $query->where('group', 'video');

        // Apply search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')->orWhere('group', 'like', '%' . $this->search . '%');
            });
        }

        // Order by created_at desc by default
        $query->orderBy('created_at', 'desc');

        return $query->paginate($this->perPage);
    }

    // Activate
    public function activate($id)
    {
        $category = \App\Models\Vrm\Taxonomy::find($id);
        if ($category) {
            $category->is_active = true;
            $category->save();

            $this->notifySuccess(__('Category was activated successfully!'));
        } else {
            $this->notifyError(__('Category not found!'));
        }
    }

    // Deactivate
    public function deactivate($id)
    {
        $category = \App\Models\Vrm\Taxonomy::find($id);
        if ($category) {
            $category->is_active = false;
            $category->save();

            $this->notifySuccess(__('Category was deactivated successfully!'));
        } else {
            $this->notifyError(__('Category not found!'));
        }
    }

    // Delete
    public function delete($id)
    {
        try {
            $category = \App\Models\Vrm\Taxonomy::find($id);
            if ($category) {
                $this->notifySuccess(__('Category was deleted successfully!'));
                $category->delete();
            } else {
                $this->notifyError(__('Category not found!'));
            }
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to delete category:' . $e->getMessage()));
        }
    }
}; ?>

<div>
	{{-- @dd($results) --}}
	<x-admin-panel>
		<x-slot name="header">{{ __('Video Categories') }}</x-slot>
		<x-slot name="desc">
			{{ __('Manage the video categories displayed in the mobile app.') }}
			{{ __('You can create, edit, enable/disable, or delete categories here.') }}
		</x-slot>
		<x-slot name="button">
			<a href="{{ route('admin.categories.create') }}"
				class="bg-blue-500 text-white hover:bg-blue-600 px-3 py-2 rounded-md float-right text-sm font-bold">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4 inline-block">
					<path fill-rule="evenodd"
						d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z"
						clip-rule="evenodd" />
				</svg>
				Add New Category
			</a>
		</x-slot>

		{{-- Search & Filter --}}
		<div class="my-4">
			<div class="bg-white shadow-sm sm:rounded-lg">
				<div class="px-4 py-5 sm:p-6">
					<h3 class="text-base font-semibold text-gray-900">Search & Filter data</h3>
					<form class="sm:flex sm:items-center">
						<div class="w-full sm:max-w-xs">
							<input type="text" wire:model.live.debounce.300ms="search"
								class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
								placeholder="Search categories..." />
						</div>
					</form>
				</div>
			</div>
		</div>

		{{-- Display notifications --}}
		{!! $this->renderNotification() !!}

		{{-- List --}}
		<div class="overflow-hidden shadow-sm ring-1 ring-black/5 sm:rounded-lg mt-2">

			<table class="min-w-full divide-y divide-gray-300">
				<thead class="bg-gray-50">
					<tr>
						<th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">#ID</th>
						<th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">Name</th>
						<th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
						<th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Slug</th>
						<th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
						<th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-3">
							<span class="sr-only">Actions</span>
						</th>
					</tr>
				</thead>
				<tbody class="bg-white">
					@if ($this->results->isNotEmpty())
						@foreach ($this->results as $row)
							<tr class="even:bg-gray-50">
								<td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">{{ $row->id }}</td>
								<td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">{{ $row->name }}</td>
								<td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $row->type }}</td>
								<td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $row->getSlug() }}</td>
								<td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
									@if ($row->is_active)
										<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-sm bg-green-400 text-white">
											Active
										</span>
									@else
										<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-sm bg-red-400 text-white">
											Inactive
										</span>
									@endif
								</td>
								<td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
									<a href="{{ route('admin.categories.edit', $row->id) }}"
										class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
											stroke="currentColor" class="size-4">
											<path stroke-linecap="round" stroke-linejoin="round"
												d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
										</svg>
										Edit
									</a>


									<button type="button" wire:click="activate({{ $row->id }})"
										class="inline-flex items-center gap-x-1.5 rounded-md bg-green-600 px-2.5 py-1 text-sm font-semibold text-white shadow-xs hover:bg-green-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
											<path fill-rule="evenodd"
												d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z"
												clip-rule="evenodd" />
										</svg>
									</button>

									<button type="button" wire:click="deactivate({{ $row->id }})"
										class="cursor-pointer inline-flex items-center gap-x-1.5 rounded-md bg-yellow-400 px-2.5 py-1 text-sm font-semibold text-white shadow-xs hover:bg-yellow-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-600">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
											<path fill-rule="evenodd"
												d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z"
												clip-rule="evenodd" />
										</svg>
									</button>

									<button type="button" wire:click="$js.confirmDelete({{ $row->id }})"
										class="inline-flex items-center gap-x-1.5 rounded-md bg-red-600 px-2.5 py-1 text-xs font-semibold text-white shadow-xs hover:bg-red-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
											stroke="currentColor" class="size-4">
											<path stroke-linecap="round" stroke-linejoin="round"
												d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
										</svg>
										Delete
									</button>
								</td>
							</tr>
						@endforeach
					@else
						<tr class="even:bg-gray-50">
							<td colspan="5"
								class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3 text-center">
								<span class="text-gray-500 text-2xl font-bold">No results found</span>
							</td>
						</tr>
					@endif
				</tbody>
			</table>
		</div>

		{{-- Pagination --}}
		<div class="mt-8">
			@if ($this->results->hasPages())
				<div class="p-2">
					{{ $this->results->links() }}
				</div>
			@endif
		</div>
	</x-admin-panel>

	@script
		<script>
			// Function to show delete confirmation
			$js('confirmDelete', (id) => {
				Swal.fire({
					title: 'Are you sure you want to delete?',
					text: "This category will be removed permanently.",
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#d33',
					cancelButtonColor: '#3085d6',
					confirmButtonText: 'Yes, delete it!',
					cancelButtonText: 'No, cancel!'
				}).then((result) => {
					if (result.isConfirmed) {
						$wire.delete(id);
					}
				});
			});
		</script>
	@endscript
</div>
