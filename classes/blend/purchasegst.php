<?php
namespace gst\blend;

class purchasegst extends gst
{
    public function __construct()
    {
        parent::__construct();

        $this->label = 'Purchases';
        $this->filters[] = (object)[
            'field' => 'sort',
            'cmp' => '=',
            'value' => 'purchase',
        ];

        filter_objects($this->fields, 'name', 'is', 'sort')[0]->hide = true;
    }
}
