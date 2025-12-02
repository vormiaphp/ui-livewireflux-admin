<?php

use Livewire\Volt\Component;

new class extends Component
{
    public $id;

    public function mount($id)
    {
        $this->id = $id;
    }

    // Admins edit page
    public function render()
    {
        return view('livewire.admin.admins.edit');
    }
};

