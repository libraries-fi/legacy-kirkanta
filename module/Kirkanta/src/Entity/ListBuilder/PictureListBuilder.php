<?php

namespace Kirkanta\Entity\ListBuilder;

class PictureListBuilder extends AbstractListBuilder
{
    public function getTitle()
    {
        return $this->tr('Pictures');
    }

    public function build($entities)
    {
        return $this->table()
            ->setData($entities)
            ->setColumns([
                'filename' => $this->tr('Picture'),
                'name' => $this->tr('Name'),
                'year' => $this->tr('Year'),
                'author' => $this->tr('Author'),
                'default' => '',
            ])
            ->setWidth('year', 80)
            ->setWidth('filename', 120)
            ->setWidth('default', 0)
            ->setSortable('default', false)
            ->transform('default', function() { return ''; })
            ->transform('name', [$this, 'editLink'])
            ->transform('filename', function($filename, $i, $data) {
                $image = $this->plugin('ViewHelper')->get('KirkantaPicture')->tag($filename, 'small');
                $link = $this->url('edit', $data);
                return sprintf('<a href="%s">%s</a>', $link, $image);
            })
            ->before(function($row) {
                if ($row->getData()['default']) {
                    $row->setClass('info active');
                }
            });
    }
}
