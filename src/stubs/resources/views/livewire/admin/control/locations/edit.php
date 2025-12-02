<?php

use Livewire\Volt\Component;

new class extends Component
{
    public $id;

    public function mount($id)
    {
        $this->id = $id;
    }

    // Locations edit page (used for both countries and cities)
    public function render()
    {
        return view('livewire.admin.control.locations.edit');
    }
};

