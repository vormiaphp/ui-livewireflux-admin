<?php

use Livewire\Volt\Component;

new class extends Component
{
    public $id;

    public function mount($id)
    {
        $this->id = $id;
    }

    // Inheritance edit page
    public function render()
    {
        return view('livewire.admin.control.inheritance.edit');
    }
};

