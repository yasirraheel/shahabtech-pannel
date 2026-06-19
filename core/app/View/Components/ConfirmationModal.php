<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ConfirmationModal extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */

    public $customButton;
    public $addClass;

    public function __construct($customButton = false, $addClass = null)
    {
        $this->customButton = $customButton;
        $this->addClass = $addClass;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $customButton = $this->customButton;
        $addClass = $this->addClass;
        return view('components.confirmation-modal');
    }
}
