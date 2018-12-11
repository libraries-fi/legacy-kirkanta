<?php

namespace Kirkanta\Form;

use Kirkanta\I18n\TranslatableInterface;
use Kirkanta\I18n\TranslatableTrait;
use Kirkanta\Util\RegionalLibraries;

class OrganisationWebLinkGroupForm extends WebLinkGroupForm
{
    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'organisation',
            'type' => 'hidden',
            'options' => [
                'label' => $this->tr('Organisation')
            ]
        ]);
    }
}
