<?php

namespace Modules\Core\App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use Modules\Core\App\Enums\LanguageEnum;

class InputMultiLanguageComponent extends Component
{
    public function __construct(
        public string $name,
        public string $divClass,
        public string $type,
        public string $label,
        public string $id,
        public ?string $required = 'required',
        public ?array $langs = [],
        public ?string $language = null,
        public ?object $item = null,
    ) {
        //
    }

    /**
     * Get the view/contents that represent the component.
     */
    public function render(): View|string
    {
        $this->langs = LanguageEnum::cases();

        return view('core::components.inputmultilanguagecomponent');
    }
}
