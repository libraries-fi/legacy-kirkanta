<?php

namespace Kirkanta\Entity\ListBuilder;

class AccessibilityFeatureListBuilder extends AbstractListBuilder
{
    public function getTitle()
    {
        return $this->tr('Accessibility');
    }
    
    public function build($entities)
    {
        return $this->table()
            ->setData($entities)
            ->setColumns([
//                 'id' => $this->tr('ID'),
                'name' => $this->tr('Name'),
                'description' => $this->tr('Description'),
            ])
//             ->setWidth('id', 50)
            ->transform('name', [$this, 'editLink'])
            ->alter('description', function($d) { return mb_substr($d, 0, 50); });
    }
}
