<?php

namespace Modules\Core\app\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Select2Livewire extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $model,
        public string $name,
        public string $required,
        public mixed $options,
        public string $id,
        public string $placeholder,
        public ?string $modelBootstrap = null,
        public ?string $onChangeEvent = null,
        public ?string $onChange = null,
        public ?string $property = null,
    ) {}

    /**
     * Get the view/contents that represent the component.
     */
    public function render(): View|string
    {
        return view('core::components.select2livewire');
    }
}
