<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AdminPanel extends Component
{
    public $header;
    public $desc;
    public $button;

    /**
     * Create a new component instance.
     */
    public function __construct($header = null, $desc = null, $button = null)
    {
        $this->header = $header;
        $this->desc = $desc;
        $this->button = $button;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.admin-panel');
    }
}
