<?php

// Add these routes to your routes/web.php file
// Place them inside: Route::middleware(['auth'])->group(function () { ... });
// Note: If you have configured your own starterkit, you may need to add: use Livewire\Volt\Volt;

Route::group(['prefix' => 'admin'], function () {
    // Categories
    Volt::route('categories', 'admin.control.categories.index')->name('admin.categories.index');
    Volt::route('categories/create', 'admin.control.categories.create')->name('admin.categories.create');
    Volt::route('categories/edit/{id}', 'admin.control.categories.edit')->name('admin.categories.edit');

    // Inheritance
    Volt::route('inheritance', 'admin.control.inheritance.index')->name('admin.inheritance.index');
    Volt::route('inheritance/create', 'admin.control.inheritance.create')->name('admin.inheritance.create');
    Volt::route('inheritance/edit/{id}', 'admin.control.inheritance.edit')->name('admin.inheritance.edit');

    // Locations - Countries
    Volt::route('countries', 'admin.control.locations.index')->name('admin.countries.index');
    Volt::route('countries/create', 'admin.control.locations.create')->name('admin.countries.create');
    Volt::route('countries/edit/{id}', 'admin.control.locations.edit')->name('admin.countries.edit');

    // Locations - Cities
    Volt::route('cities', 'admin.control.locations.index')->name('admin.cities.index');
    Volt::route('cities/create', 'admin.control.locations.create')->name('admin.cities.create');
    Volt::route('cities/edit/{id}', 'admin.control.locations.edit')->name('admin.cities.edit');

    // Availability taxonomy
    Volt::route('availabilities', 'admin.control.availability.index')->name('admin.availabilities.index');
    Volt::route('availabilities/create', 'admin.control.availability.create')->name('admin.availabilities.create');
    Volt::route('availabilities/edit/{id}', 'admin.control.availability.edit')->name('admin.availabilities.edit');

    // Admins
    Volt::route('admins', 'admin.admins.index')->name('admin.admins.index');
    Volt::route('admins/create', 'admin.admins.create')->name('admin.admins.create');
    Volt::route('admins/edit/{id}', 'admin.admins.edit')->name('admin.admins.edit');
});
