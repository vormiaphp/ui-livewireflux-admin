{{-- Admin sidebar menu items — inject inside Platform flux:sidebar.group, before </flux:sidebar.group> --}}
<flux:sidebar.item icon="globe-alt" :href="route('admin.countries.index')" wire:navigate>
    {{ __('Countries') }}
</flux:sidebar.item>
<flux:sidebar.item icon="building-office-2" :href="route('admin.cities.index')" wire:navigate>
    {{ __('Cities') }}
</flux:sidebar.item>
<flux:sidebar.item icon="calendar-days" :href="route('admin.availabilities.index')" wire:navigate>
    {{ __('Availability') }}
</flux:sidebar.item>
<flux:sidebar.item icon="squares-2x2" :href="route('admin.inheritance.index')" wire:navigate>
    {{ __('Inheritance') }}
</flux:sidebar.item>
<flux:sidebar.item icon="user-circle" :href="route('admin.admins.index')" wire:navigate>
    {{ __('Admins') }}
</flux:sidebar.item>
