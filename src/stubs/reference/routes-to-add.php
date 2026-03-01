<?php

// Add these routes to your routes/web.php file
// Place them inside: Route::middleware(['auth'])->group(...) or Route::middleware(['auth','verified'])->group(...)

Route::group(['prefix' => 'admin'], function () {
    // Categories
    Route::livewire('categories', 'admin.control.categories.index')->name('admin.categories.index');
    Route::livewire('categories/create', 'admin.control.categories.create')->name('admin.categories.create');
    Route::livewire('categories/edit/{id}', 'admin.control.categories.edit')->name('admin.categories.edit');

    // Inheritance
    Route::livewire('inheritance', 'admin.control.inheritance.index')->name('admin.inheritance.index');
    Route::livewire('inheritance/create', 'admin.control.inheritance.create')->name('admin.inheritance.create');
    Route::livewire('inheritance/edit/{id}', 'admin.control.inheritance.edit')->name('admin.inheritance.edit');

    // Locations - Countries
    Route::livewire('countries', 'admin.control.locations.index')->name('admin.countries.index');
    Route::livewire('countries/create', 'admin.control.locations.create')->name('admin.countries.create');
    Route::livewire('countries/edit/{id}', 'admin.control.locations.edit')->name('admin.countries.edit');

    // Locations - Cities
    Route::livewire('cities', 'admin.control.locations.index')->name('admin.cities.index');
    Route::livewire('cities/create', 'admin.control.locations.create')->name('admin.cities.create');
    Route::livewire('cities/edit/{id}', 'admin.control.locations.edit')->name('admin.cities.edit');

    // Availability taxonomy
    Route::livewire('availabilities', 'admin.control.availability.index')->name('admin.availabilities.index');
    Route::livewire('availabilities/create', 'admin.control.availability.create')->name('admin.availabilities.create');
    Route::livewire('availabilities/edit/{id}', 'admin.control.availability.edit')->name('admin.availabilities.edit');

    // Admins
    Route::livewire('admins', 'admin.admins.index')->name('admin.admins.index');
    Route::livewire('admins/create', 'admin.admins.create')->name('admin.admins.create');
    Route::livewire('admins/edit/{id}', 'admin.admins.edit')->name('admin.admins.edit');
});
