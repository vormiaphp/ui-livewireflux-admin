<?php

use Livewire\Volt\Component;

new class extends Component
{
    // Locations index page (used for both countries and cities)
    public function render()
    {
        return view('livewire.admin.control.locations.index');
    }
};

